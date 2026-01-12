<?php

declare(strict_types=1);

namespace Drupal\Tests\Unit\harvest;

use Drupal\harvest\Util;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\harvest\Util
 * @coversDefaultClass \Drupal\harvest\Util
 *
 * @group harvest
 */
class UtilTest extends TestCase {

  public function test(): void {
    $fake_dataset = 'Not an object';
    $this->expectExceptionMessage('The dataset ' . json_encode($fake_dataset) . ' is not an object.');
    Util::getDatasetId($fake_dataset);
  }

  public static function provideJsonSame(): array {
    return [
      'same_shallow_values' => [
        (object) ['key_a' => 'value', 'key_b' => 'value'],
        (object) ['key_b' => 'value', 'key_a' => 'value'],
      ],
      'same_deep_values' => [
              ['key_a' => 'value', 'key_b' => ['deep_1' => 'value1', 'deep_2' => 'value2']],
              ['key_b' => ['deep_2' => 'value2', 'deep_1' => 'value1'], 'key_a' => 'value'],
      ],
    ];
  }

  /**
   * @covers ::generateHash
   * @dataProvider provideJsonSame
   */
  public function testJsonHashSame($first, $second): void {
    $this->assertEquals(
          Util::generateHash($first),
          Util::generateHash($second)
      );
  }

  public static function provideArrays(): array {
    return [
      'fully_flipped' => [
              [
                'a' => [
                  'A' => '2',
                  'B' => '2',
                ],
                'z' => [
                  'Y' => '1',
                  'Z' => '1',
                ],
              ],
              [
                'z' => [
                  'Z' => '1',
                  'Y' => '1',
                ],
                'a' => [
                  'B' => '2',
                  'A' => '2',
                ],
              ],
      ],
    ];
  }

  /**
   * @covers ::recursiveKeySort
   * @dataProvider provideArrays
   */
  public function testRecursiveKSort(array $expected_array, array $array): void {
    Util::recursiveKeySort($array);
    $this->assertSame($expected_array, $array);
  }

}
