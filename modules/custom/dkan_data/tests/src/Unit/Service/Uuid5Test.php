<?php

declare(strict_types = 1);

namespace Drupal\dkan_data\Tests\Unit;

use Drupal\dkan_data\Service\Uuid5;
use PHPUnit\Framework\TestCase;

/**
 * Tests Drupal\dkan_data\Service\Uuid5.
 *
 * @coversDefaultClass \Drupal\dkan_data\Service\Uuid5
 * @package Drupal\Tests\dkan_data\Unit\Service
 * @group dkan_data
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
    $actual = Uuid5::generate($schema_id, $value);
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

}
