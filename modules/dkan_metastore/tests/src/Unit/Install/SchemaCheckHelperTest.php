<?php

namespace Drupal\Tests\dkan_metastore\Unit\Install;

use PHPUnit\Framework\TestCase;

/**
 * Covers the pure schema-parse helper in dkan_metastore.install.
 *
 * @group dkan
 * @group dkan_metastore
 * @group unit
 */
class SchemaCheckHelperTest extends TestCase {

  protected function setUp(): void {
    parent::setUp();
    // The install file's helper functions aren't autoloaded; pull them in
    // explicitly. Path: tests/src/Unit/Install -> module root is ../../../..
    require_once __DIR__ . '/../../../../dkan_metastore.install';
  }

  public function testValidObjectSchemaReturnsNull(): void {
    $this->assertNull(_dkan_metastore_validate_schema_json('{"type":"object"}'));
  }

  public function testBooleanSchemaTrueReturnsNull(): void {
    $this->assertNull(_dkan_metastore_validate_schema_json('true'));
  }

  public function testBooleanSchemaFalseReturnsNull(): void {
    $this->assertNull(_dkan_metastore_validate_schema_json('false'));
  }

  public function testMalformedJsonReturnsError(): void {
    $msg = _dkan_metastore_validate_schema_json('{not json');
    $this->assertNotNull($msg);
    $this->assertStringContainsString('not valid JSON', $msg);
  }

  public function testStringSchemaReturnsError(): void {
    $msg = _dkan_metastore_validate_schema_json('"hello"');
    $this->assertNotNull($msg);
    $this->assertStringContainsString('object or boolean', $msg);
  }

  public function testNumberSchemaReturnsError(): void {
    $msg = _dkan_metastore_validate_schema_json('42');
    $this->assertNotNull($msg);
    $this->assertStringContainsString('object or boolean', $msg);
  }

  /**
   * Non-string $schema throws eagerly at the root parse step.
   */
  public function testRootLevelStructuralErrorReturnsError(): void {
    $this->assertNotNull(_dkan_metastore_validate_schema_json('{"$schema": 42}'));
  }

  /**
   * A root-level keyword parse error is caught by strict-parsing the root node.
   */
  public function testDeepStructuralErrorReturnsError(): void {
    $this->assertNotNull(
      _dkan_metastore_validate_schema_json('{"type":"object","properties":"not-an-object"}')
    );
  }

  /**
   * Draft-04 declaration — opis v2 supports only draft-06+.
   */
  public function testDraft04SchemaReturnsError(): void {
    $msg = _dkan_metastore_validate_schema_json(
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
    $this->assertNotNull(_dkan_metastore_validate_schema_json($json));
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
    $this->assertNull(_dkan_metastore_validate_schema_json($json));
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

}
