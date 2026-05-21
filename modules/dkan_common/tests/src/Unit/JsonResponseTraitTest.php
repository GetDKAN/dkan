<?php

namespace Drupal\Tests\dkan_common\Unit;

use Drupal\Tests\dkan_common\Unit\Mocks\ClassUsingJsonResponseTrait;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use RootedData\Exception\ValidationException;

/**
 * @coversDefaultClass \Drupal\dkan_common\JsonResponseTrait
 *
 * @group dkan
 * @group dkan_common
 * @group unit
 */
class JsonResponseTraitTest extends TestCase {

  /**
   * @covers ::getExceptionData
   *
   * Locks down the historical v1/m1x0n response shape:
   *   { "keyword": <string>, "pointer": <string>, "message": <string> }
   *
   * The v2 migration preserves this shape (the values are sourced from
   * opis v2's API instead of m1x0n, and message wording may differ
   * slightly, but the keys and types match) so downstream consumers
   * parsing validation errors keep working unchanged.
   */
  public function testGetExceptionDataReturnsV1CompatibleShape(): void {
    $schema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'title' => (object) ['type' => 'string', 'minLength' => 1],
      ],
      'required' => ['title'],
    ];
    $invalid = (object) ['title' => ''];
    $result = (new Validator())->validate($invalid, $schema);
    $this->assertTrue($result->hasError());

    $exception = new ValidationException('invalid', $result);
    $data = (new ClassUsingJsonResponseTrait())->callGetExceptionData($exception);

    $this->assertIsArray($data);
    $this->assertSame(['keyword', 'pointer', 'message'], array_keys($data));
    $this->assertIsString($data['keyword']);
    $this->assertNotEmpty($data['keyword']);
    $this->assertIsString($data['pointer']);
    $this->assertIsString($data['message']);
    $this->assertNotEmpty($data['message']);
    // Pointer should reference the failing field.
    $this->assertStringContainsString('title', $data['pointer']);
    // Keyword should name the failing constraint.
    $this->assertSame('minLength', $data['keyword']);
  }

  /**
   * @covers ::getExceptionData
   */
  public function testGetExceptionDataReturnsFalseForNonValidationException(): void {
    $this->assertFalse(
      (new ClassUsingJsonResponseTrait())->callGetExceptionData(new \RuntimeException('nope'))
    );
  }

  /**
   * @covers ::getExceptionData
   *
   * Edge case: ValidationException whose result has no error (unusual but
   * possible if callers misuse the exception). Should return FALSE rather
   * than crashing.
   */
  public function testGetExceptionDataReturnsFalseWhenResultHasNoError(): void {
    $schema = (object) ['type' => 'object'];
    $valid = (object) [];
    $result = (new Validator())->validate($valid, $schema);
    $this->assertFalse($result->hasError());

    $exception = new ValidationException('no-error', $result);
    $this->assertFalse(
      (new ClassUsingJsonResponseTrait())->callGetExceptionData($exception)
    );
  }

}
