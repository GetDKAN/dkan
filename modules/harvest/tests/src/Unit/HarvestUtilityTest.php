<?php

namespace Drupal\Tests\harvest\Unit;

use Drupal\harvest\HarvestUtility;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\harvest\HarvestUtility
 * @coversDefaultClass \Drupal\harvest\HarvestUtility
 *
 * @group dkan
 * @group harvest
 * @group unit
 */
class HarvestUtilityTest extends TestCase {

  public static function providerPlanIdFromTableName() {
    return [
      ['thing', 'harvest_thing_hash'],
      ['', 'harvest__hash'],
      ['', ''],
      ['', 'whatever'],
    ];
  }

  /**
   * @dataProvider providerPlanIdFromTableName
   */
  public function testPlanIdFromTableName(string $expected, string $table_name) {
    $this->assertEquals($expected, HarvestUtility::planIdFromTableName($table_name));
  }

}
