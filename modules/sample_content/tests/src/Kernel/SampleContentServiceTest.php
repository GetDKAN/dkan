<?php

namespace Drupal\Tests\sample_content\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * @covers \Drupal\sample_content\SampleContentService
 * @coversDefaultClass \Drupal\sample_content\SampleContentService
 *
 * @group dkan
 * @group sample_content
 * @group kernel
 */
class SampleContentServiceTest extends KernelTestBase {

  protected static $modules = [
    'sample_content',
    'harvest',
    'metastore',
    'common',
  ];

  protected function setUp() : void {
    parent::setUp();
    $this->installEntitySchema('harvest_plan');
  }

  /**
   * @covers ::createDatasetJsonFileFromTemplate
   * @covers ::getHarvestPlan
   * @covers ::registerSampleContentHarvest
   */
  public function testSampleContentService() {
    /** @var \Drupal\sample_content\SampleContentService $sample_content_service */
    $sample_content_service = $this->container->get('dkan.sample_content.service');

    $this->assertFileExists(
      $sample_content_service->createDatasetJsonFileFromTemplate()
    );

    $this->assertEquals(
      'sample_content',
      ($sample_content_service->getHarvestPlan())->identifier
    );

    $harvest_plan_id = 'sample_harvest_plan';
    $this->assertEquals(
      $harvest_plan_id,
      $sample_content_service->registerSampleContentHarvest($harvest_plan_id)
    );
  }

}
