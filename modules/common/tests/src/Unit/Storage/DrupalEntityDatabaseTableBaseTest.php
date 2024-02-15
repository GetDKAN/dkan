<?php

namespace Drupal\Tests\common\Unit\Storage;

use Drupal\common\Storage\DrupalEntityDatabaseTableBase;
use Drupal\common\Storage\Query;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\common\Storage\DrupalEntityDatabaseTableBase
 * @coversDefaultClass \Drupal\common\Storage\DrupalEntityDatabaseTableBase
 *
 * @group dkan
 * @group common
 * @group unit
 */
class DrupalEntityDatabaseTableBaseTest extends TestCase {

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
    $table = $this->getMockBuilder(DrupalEntityDatabaseTableBase::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(DrupalEntityDatabaseTableBase::class . '::' . $method . ' not yet implemented.');

    $ref_method = new \ReflectionMethod($table, $method);
    $ref_method->invokeArgs($table, $arguments);
  }

}
