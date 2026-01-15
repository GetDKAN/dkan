<?php

declare(strict_types=1);

namespace Drupal\Tests\dkan_harvest\Functional\Transform;

use Drupal\dkan_harvest\Transform\ResourceImporter;
use Drupal\Tests\BrowserTestBase;

/**
 * @covers \Drupal\dkan_harvest\Transform\ResourceImporter
 * @coversDefaultClass \Drupal\dkan_harvest\Transform\ResourceImporter
 *
 * @group dkan
 * @group harvest
 * @group functional
 * @group btb
 * @group functional1
 *
 * @todo Turn this into a kernel test when we have refactored
 *   DrupalFiles::retrieveFile to allow for vfsStream.
 */
class ResourceImporterTest extends BrowserTestBase {

  protected static $modules = [
    'dkan_datastore',
    'dkan_metastore',
  ];

  protected $defaultTheme = 'stark';

  /**
   * @covers ::saveFile
   */
  public function testSaveFileHappyPath() {
    $sample_content_module_path = $this->container->get('extension.list.module')
      ->getPath('sample_content');

    $importer = new ResourceImporter('harvest_plan_id');

    $this->assertStringContainsString(
      'distribution/dataset/Bike_Lane.csv',
      $importer->saveFile(
        'file://' . $sample_content_module_path . '/files/Bike_Lane.csv',
        'dataset'
      )
    );
  }

}
