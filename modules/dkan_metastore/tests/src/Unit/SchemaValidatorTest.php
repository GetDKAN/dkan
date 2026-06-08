<?php

namespace Drupal\Tests\dkan_metastore\Unit;

use Drupal\dkan_metastore\SchemaRetriever;
use Drupal\dkan_metastore\SchemaValidator;
use PHPUnit\Framework\TestCase;

/**
 * Covers SchemaValidator schema-parse behavior.
 *
 * @group dkan
 * @group dkan_metastore
 * @group unit
 */
class SchemaValidatorTest extends TestCase {

  protected SchemaValidator $validator;

  protected function setUp(): void {
    parent::setUp();

    $datasetSchemaPath = realpath(__DIR__ . '/../docs/dataset.json');
    $this->assertNotFalse($datasetSchemaPath, 'Could not resolve dataset schema path.');
    $datasetSchema = file_get_contents($datasetSchemaPath);
    $this->assertNotFalse($datasetSchema, 'Could not read dataset schema JSON.');

    $retriever = $this->getMockBuilder(SchemaRetriever::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getAllIds', 'retrieve'])
      ->getMock();

    $retriever->method('getAllIds')->willReturn(['dataset']);
    $retriever->method('retrieve')->willReturnCallback(function (string $id) use ($datasetSchema): string {
      if ($id !== 'dataset') {
        throw new \Exception("Schema {$id} not found.");
      }
      return $datasetSchema;
    });

    $this->validator = new SchemaValidator($retriever);
  }

  public function testValidObjectSchemaReturnsNull(): void {
    $this->assertNull($this->validator->validateSchemaJson('{"type":"object"}'));
  }

  public function testSchemaKeywordSubSchemas(): void {
    $schema = [
      'type' => 'object',
      'properties' => [
        'foo' => [
          'type' => 'object',
          'properties' => [
            'bar' => ['type' => 'string'],
          ],
          'additionalProperties' => [
            'type' => 'object',
            'properties' => [
              'bar' => [
                'type' => 'integer',
              ],
            ],
          ],
        ],
      ],
    ];
    $this->assertNull($this->validator->validateSchemaJson(json_encode($schema)));

    // A structural error in a sub-schema must be reported.
    $schema['properties']['foo']['additionalProperties']['properties']['bar']['type'] = 42;
    $msg = $this->validator->validateSchemaJson(json_encode($schema));
    $this->assertStringContainsString('type can only be a string or an array of string', $msg);
  }

  public function testNestedItemsSchema(): void {
    $schema = [
      'type' => 'object',
      'properties' => [
        'foo' => [
          'type' => 'array',
          'items' => [
            [
              'type' => 'object',
              'properties' => [
                'bar' => ['type' => 'string'],
              ],
            ],
            [
              'type' => 'object',
              'properties' => [
                'bar' => ['type' => 'integer'],
              ],
            ],
          ],
        ],
      ],
    ];
    // Items can no longer be an array of schemas; prefixItems must be used.
    $msg = $this->validator->validateSchemaJson(json_encode($schema));
    $this->assertStringContainsString('items must contain a valid json schema', $msg);
  }

  public function testSlotsSchemaValidation(): void {
    $schema = [
      'type' => 'object',
      '$slots' => [
        'foo' => [
          'type' => 'object',
          'properties' => [
            'bar' => ['type' => 'string'],
          ],
        ],
      ],
    ];
    $this->assertNull($this->validator->validateSchemaJson(json_encode($schema)));

    // A structural error in a $slots sub-schema must be reported.
    $schema['$slots']['foo']['properties']['bar']['type'] = 42;
    $msg = $this->validator->validateSchemaJson(json_encode($schema));
    $this->assertStringContainsString('type can only be a string or an array of string', $msg);
  }

  public function testBooleanSchemaTrueReturnsNull(): void {
    $this->assertNull($this->validator->validateSchemaJson('true'));
  }

  public function testBooleanSchemaFalseReturnsNull(): void {
    $this->assertNull($this->validator->validateSchemaJson('false'));
  }

  public function testMalformedJsonReturnsError(): void {
    $msg = $this->validator->validateSchemaJson('{not json');
    $this->assertNotNull($msg);
    $this->assertStringContainsString('not valid JSON', $msg);
  }

  public function testStringSchemaReturnsError(): void {
    $msg = $this->validator->validateSchemaJson('"hello"');
    $this->assertNotNull($msg);
    $this->assertStringContainsString('object or boolean', $msg);
  }

  public function testNumberSchemaReturnsError(): void {
    $msg = $this->validator->validateSchemaJson('42');
    $this->assertNotNull($msg);
    $this->assertStringContainsString('object or boolean', $msg);
  }

  /**
   * Non-string $schema throws eagerly at the root parse step.
   */
  public function testRootLevelStructuralErrorReturnsError(): void {
    $this->assertNotNull($this->validator->validateSchemaJson('{"$schema": 42}'));
  }

  /**
   * A root-level keyword parse error is caught by strict-parsing the root node.
   */
  public function testDeepStructuralErrorReturnsError(): void {
    $this->assertNotNull(
      $this->validator->validateSchemaJson('{"type":"object","properties":"not-an-object"}')
    );
  }

  /**
   * Draft-04 declaration — opis v2 supports only draft-06+.
   */
  public function testDraft04SchemaReturnsError(): void {
    $msg = $this->validator->validateSchemaJson(
      '{"$schema": "http://json-schema.org/draft-04/schema#", "type": "object"}'
    );
    $this->assertNotNull($msg);
  }

  /**
   * Draft-04 or structural errors buried in sub-schemas must be reported.
   *
   * @dataProvider buriedErrorProvider
   */
  public function testBuriedErrorReturnsError(string $json): void {
    $this->assertNotNull($this->validator->validateSchemaJson($json));
  }

  public static function buriedErrorProvider(): array {
    $d04 = '{"$schema":"http://json-schema.org/draft-04/schema#"}';
    $d7 = 'http://json-schema.org/draft-07/schema#';
    return [
      'properties' => ['{"type":"object","properties":{"foo":' . $d04 . '}}'],
      '$defs' => ['{"$defs":{"thing":' . $d04 . '}}'],
      'items (object form)' => ['{"items":' . $d04 . '}'],
      'dependentSchemas' => ['{"dependentSchemas":{"a":' . $d04 . '}}'],
      'dependencies (object value)' => ['{"dependencies":{"a":' . $d04 . '}}'],
      'structural error in properties' => ['{"properties":{"foo":{"type":"array","items":42}}}'],
      // Short-circuited branches validate(NULL) would not have parsed fully.
      'allOf[1] behind null-failing allOf[0]' => [
        '{"$schema":"' . $d7 . '","allOf":[{"type":"string"},' . $d04 . ']}',
      ],
      'non-matching anyOf branch' => [
        '{"$schema":"' . $d7 . '","anyOf":[{"type":"null"},' . $d04 . ']}',
      ],
      'unselected else branch' => [
        '{"$schema":"' . $d7 . '","if":{"type":"null"},"then":{},"else":' . $d04 . '}',
      ],
    ];
  }

  /**
   * Genuine schema positions only — non-schema containers must not be flagged.
   *
   * @dataProvider noFalsePositiveProvider
   */
  public function testNoFalsePositive(string $json): void {
    $this->assertNull($this->validator->validateSchemaJson($json));
  }

  public static function noFalsePositiveProvider(): array {
    return [
      // The properties map contains a key named "format"; it must not be read
      // as the format keyword.
      'property named format' => ['{"type":"object","properties":{"format":{"type":"string"}}}'],
      // Dependencies array values are property-name lists, not schemas.
      'dependencies array form' => ['{"dependencies":{"a":["b","c"]}}'],
      'nested clean properties' => ['{"type":"object","properties":{"a":{"type":"string"},"b":{"type":"integer"}}}'],
    ];
  }

  public function testOldsDatasetSchemaThrowsDraft04Error(): void {
    $this->assertEquals('Unsupported draft-04', $this->validator->validateSchemaId('dataset'));
  }

  public function testNonexistentSchemaIdReturnsError(): void {
    $this->assertStringContainsString('Could not retrieve schema', $this->validator->validateSchemaId('nonexistent'));
  }

  public function testCheckAllSchemas(): void {
    $problems = $this->validator->checkAllSchemas();
    $this->assertIsArray($problems);
    $this->assertArrayHasKey('dataset', $problems);
    $this->assertStringContainsString('Unsupported draft-04', $problems['dataset']);
  }

}
