<?php

namespace Drupal\Tests\harvest\Unit\Storage;

use Drupal\common\Storage\Query;
use Drupal\harvest\Storage\HarvestHashesEntityDatabaseTable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\harvest\Storage\HarvestHashesEntityDatabaseTable
 * @coversDefaultClass \Drupal\harvest\Storage\HarvestHashesEntityDatabaseTable
 *
 * @group dkan
 * @group harvest
 * @group unit
 */
class HarvestHashesEntityDatabaseTableTest extends TestCase {

  public static function providerNotImplementedMethods() {
    return [
      ['storeMultiple', [[]]],
      ['query', [new Query()]],
      ['setSchema', [[]]],
      ['getSchema', []],
    ];
  }

  /**
   * Gives us coverage on unimplemented methods.
   *
   * @dataProvider providerNotImplementedMethods
   */
  public function testNotImplementedMethods($method, $arguments) {
    $table = $this->getMockBuilder(HarvestHashesEntityDatabaseTable::class)
      ->disableOriginalConstructor()
      ->onlyMethods([])
      ->getMock();

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(HarvestHashesEntityDatabaseTable::class . '::' . $method . ' not yet implemented.');

    $ref_method = new \ReflectionMethod($table, $method);
    $ref_method->invokeArgs($table, $arguments);
  }

  /**
   * @covers ::loadEntity
   */
  public function testLoadEntityBadId() {
    $table = $this->getMockBuilder(HarvestHashesEntityDatabaseTable::class)
      ->disableOriginalConstructor()
      ->getMock();

    $ref_method = new \ReflectionMethod($table, 'loadEntity');
    $ref_method->setAccessible(TRUE);

    // Invoke with empty string.
    $this->assertNull($ref_method->invokeArgs($table, ['']));
  }

}
