<?php

namespace Drupal\Tests\harvest\Kernel\Transform;

use Drupal\KernelTests\KernelTestBase;
use Drupal\harvest\Transform\ResourceImporter;

/**
 * @covers \Drupal\harvest\Transform\ResourceImporter
 * @coversDefaultClass \Drupal\harvest\Transform\ResourceImporter
 *
 * @group dkan
 * @group harvest
 * @group kernel
 */
class ResourceImporterTest extends KernelTestBase {

  protected static $modules = [
    'common',
  ];

  /**
   * @covers ::updateDistributions
   */
  public function testUpdateDistributions() {
    // Calling with an empty dataset should result in the same empty dataset.
    $dataset = (object) [];
    $importer = new ResourceImporter('harvest_plan_id');
    $this->assertIsObject($result = $importer->run($dataset));
    $this->assertEquals($dataset, $result);

    // Calling with a distribution with no download url should result in the
    // same object being returned.
    $dataset = (object) [
      'distribution' => [
        (object) [
          'title' => 'my title',
        ],
      ],
    ];
    $this->assertIsObject($result = $importer->run($dataset));
    $this->assertEquals($dataset, $result);
  }

  /**
   * @covers ::updateDownloadUrl
   */
  public function testUpdateDownloadUrl() {
    // Mock saveFile so we don't actually have to worry about the file system.
    $importer = $this->getMockBuilder(ResourceImporter::class)
      ->setConstructorArgs(['harvest_plan_id'])
      ->onlyMethods(['saveFile'])
      ->getMock();
    $importer->expects($this->any())
      ->method('saveFile')
      // Adds '_saved' to whatever URL was passed in.
      ->willReturnCallback(function ($url, $dataset_id): string {
        return $url . '_saved';
      });

    // Prepare a dataset with a downloadURL.
    $dataset = (object) [
      'identifier' => 'identifier',
      'distribution' => [
        (object) [
          'downloadURL' => 'my_url',
          'title' => 'my title',
        ],
      ],
    ];
    $this->assertIsObject($result = $importer->run($dataset));
    $this->assertEquals(
      'my_url_saved',
      $result->distribution[0]->downloadURL ?? 'nope'
    );
  }

}
