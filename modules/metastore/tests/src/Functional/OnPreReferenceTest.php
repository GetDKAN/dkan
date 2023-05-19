<?php

namespace Drupal\Tests\metastore\Functional;

use Drupal\Tests\common\Traits\CleanUp;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Run tests in separate processes, since they rely on the value of
 *   drupal_static().
 * @runTestsInSeparateProcesses
 */
class OnPreReferenceTest extends ExistingSiteBase {
  use CleanUp;

  private $downloadUrl = "https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv";

  /**
   *
   */
  private function getData($downloadUrl) {
    return '
    {
      "title": "Test #1",
      "description": "Yep",
      "identifier": "123",
      "accessLevel": "public",
      "modified": "06-04-2020",
      "keyword": ["hello"],
        "distribution": [
          {
            "title": "blah",
            "downloadURL": "' . $downloadUrl . '",
            "mediaType": "text/csv"
          }
        ]
    }';
  }

  /**
   *
   */
  public function test() {
    /** @var \Drupal\Core\Config\ConfigFactory $config */
    $config_factory = \Drupal::service('config.factory');
    // Ensure the proper triggering properties are set for datastore comparison.
    $datastore_settings = $config_factory->getEditable('datastore.settings');
    $datastore_settings->set('triggering_properties', ['modified']);
    $datastore_settings->save();

    // Test posting a dataset to the metastore.
    $data = $this->getData($this->downloadUrl);
    /** @var \Drupal\metastore\MetastoreService $metastore */
    $metastore = \Drupal::service('dkan.metastore.service');
    $dataset = $metastore->getValidMetadataFactory()->get($data, 'dataset');
    $metastore->post('dataset', $dataset);

    $decoded = json_decode($data);
    $decoded->modified = '06-04-2021';
    $edited = json_encode($decoded);

    $dataset = $metastore->getValidMetadataFactory()->get($edited, 'dataset');
    $metastore->patch('dataset', '123', $dataset);

    $rev = drupal_static('metastore_resource_mapper_new_revision');
    $this->assertEquals(1, $rev);
  }

  /**
   *
   */
  public function tearDown(): void {
    parent::tearDown();
    $this->removeAllNodes();
    $this->removeAllMappedFiles();
    $this->removeAllFileFetchingJobs();
    $this->flushQueues();
    $this->removeFiles();
    $this->removeDatastoreTables();
  }
}
