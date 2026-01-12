<?php

declare(strict_types=1);

namespace Drupal\Tests\dkan_harvest\Unit;

use Drupal\dkan_harvest\HarvestUtility;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\dkan_harvest\HarvestUtility
 * @coversDefaultClass \Drupal\dkan_harvest\HarvestUtility
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
