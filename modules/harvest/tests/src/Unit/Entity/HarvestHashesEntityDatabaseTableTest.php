<?php

namespace Drupal\Tests\harvest\Unit\Entity;

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

  public function providerNotImplementedMethods() {
    return [
      ['storeMultiple', [[]]],
      ['query', [$this->createStub(Query::class)]],
      ['setSchema', [[]]],
      ['getSchema', []],
    ];
  }

  /**
   * @dataProvider providerNotImplementedMethods
   */
  public function testNotImplementedMethods($method, $arguments) {
    $table = $this->getMockBuilder(HarvestHashesEntityDatabaseTable::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(HarvestHashesEntityDatabaseTable::class . '::' . $method . ' not yet implemented.');

    $ref_method = new \ReflectionMethod($table, $method);
    $ref_method->invokeArgs($table, $arguments);
  }

}
