<?php

namespace Drupal\Tests\dkan_common\Unit;

use Drupal\dkan_common\JsonResponseTrait;
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
   * Verifies the v2 ErrorFormatter::format() shape — array keyed by JSON
   * pointer, values are arrays of message strings. This is the BC-impacting
   * shape change downstream consumers parsing validation error responses
   * need to be aware of.
   */
  public function testGetExceptionDataReturnsPointerKeyedShape(): void {
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
    $this->assertNotEmpty($data);
    foreach ($data as $pointer => $messages) {
      $this->assertIsString($pointer, 'response keys are JSON pointers');
      $this->assertIsArray($messages, 'response values are arrays of strings');
      $this->assertNotEmpty($messages);
      foreach ($messages as $message) {
        $this->assertIsString($message);
        $this->assertNotEmpty($message);
      }
    }
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

/**
 * Stub exposing the protected trait method for tests.
 *
 * Mirrors the pattern in CacheableResponseTraitTest.
 */
class ClassUsingJsonResponseTrait {
  use JsonResponseTrait;

  public function callGetExceptionData(\Exception $e) {
    return $this->getExceptionData($e);
  }

}
