<?php

namespace Drupal\Tests\common\Unit;

use Drupal\common\DatasetInfo;
use PHPUnit\Framework\TestCase;

/**
 * Tests DatasetInfo class.
 *
 * Most methods are covered in the corresponding Kernel tests.
 *
 * @coversDefaultClass \Drupal\common\DatasetInfo
 *
 * @group common
 * @group dkan-core
 */
class DatasetInfoTest extends TestCase {

  /**
   * @covers ::getDistributionUuid
   */
  public function testGetDistributionUuid() {
    // Only latest revision present.
    $info['latest_revision']['distributions'][0]['distribution_uuid'] = '123';
    $datasetInfo = $this->getGatherMock($info);

    // Check that when only have latest revision, that distribution_uuid returned.
    $result = $datasetInfo->getDistributionUuid('dataset1');
    $this->assertEquals('123', $result);

    // Add a published revision
    $info['published_revision']['distributions'][0]['distribution_uuid'] = '456';
    $datasetInfo = $this->getGatherMock($info);

    // Check that when published revision present, that distribution_uuid returned.
    $result = $datasetInfo->getDistributionUuid('dataset1');
    $this->assertEquals('456', $result);
  }

  private function getGatherMock($info = []) {
    $datasetInfo = $this->getMockBuilder(DatasetInfo::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['gather'])
      ->getMock();
    $datasetInfo->method('gather')
      ->willReturn($info);

    return $datasetInfo;
  }

}
