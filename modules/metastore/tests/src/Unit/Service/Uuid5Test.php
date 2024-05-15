<?php

declare(strict_types = 1);

namespace Drupal\Tests\metastore\Unit\Service;

use Drupal\metastore\Service\Uuid5;
use PHPUnit\Framework\TestCase;

/**
 * Tests Drupal\metastore\Service\Uuid5.
 *
 * @coversDefaultClass \Drupal\metastore\Service\Uuid5
 * @package Drupal\Tests\metastore\Unit\Service
 * @group metastore
 */
class Uuid5Test extends TestCase {

  /**
   * Test generate.
   *
   * @param string $schema_id
   *   The schema id of this value.
   * @param mixed $value
   *   The value for which we generate a uuid for.
   * @param string $expected
   *   Expected value.
   *
   * @dataProvider generateProvider
   */
  public function testGenerate(string $schema_id, $value, $expected) {
    // Assert.
    $actual = (new Uuid5())->generate($schema_id, $value);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Provide data for testGenerate.
   *
   * @return array
   *   Schema id, value and expected.
   */
  public function generateProvider() {
    return [
      'string' => [
        'foo',
        'bar',
        'fd088d96-7c6b-5adf-8581-cbb18a5dad67',
      ],
      'non-string' => [
        'foo',
        (object) ['bar' => 'baz'],
        'e9de513e-b4d7-5b05-902b-f90ee7a5db52',
      ],
    ];
  }

  /**
   *
   */
  public function testIsValid() {
    $uuid5 = new Uuid5();

    $valid = $uuid5->isValid('96f0603e-5da9-43e7-bc94-38eab002f9b3');
    $this->assertTrue($valid);

    $notValid = $uuid5->isValid('foo-bar');
    $this->assertFalse($notValid);
  }

}
