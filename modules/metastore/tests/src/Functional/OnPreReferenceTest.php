<?php

namespace Drupal\Tests\metastore\Functional;

use Drupal\Tests\common\Traits\CleanUp;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * @group functional
 * @group special_test
 */
class OnPreReferenceTest extends ExistingSiteBase {

  use CleanUp;

  private $downloadUrl = "https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv";

  /**
   *
   */
  private function getData($downloadUrl, $id) {
    return '
    {
      "title": "Test #1",
      "description": "Yep",
      "identifier": "' . $id . '",
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
  public function testTriggerDatastoreUpdate() {
    $this->markTestIncomplete('Needs to clean up its CSV file.');
    /** @var \Drupal\Core\Config\ConfigFactory $config */
    $config_factory = \Drupal::service('config.factory');
    // Ensure the proper triggering properties are set for datastore comparison.
    $datastore_settings = $config_factory->getEditable('datastore.settings');
    $datastore_settings->set('triggering_properties', ['modified']);
    $datastore_settings->save();

    // Test posting a dataset to the metastore.
    $id = uniqid();
    $data = $this->getData($this->downloadUrl, $id);
    /** @var \Drupal\metastore\Service $metastore */
    $metastore = \Drupal::service('dkan.metastore.service');
    $dataset = $metastore->getValidMetadataFactory()->get($data, 'dataset');
    $this->assertEquals($id, $metastore->post('dataset', $dataset));

    $decoded = json_decode($data);
    $decoded->modified = '06-04-2021';
    $edited = json_encode($decoded);

    $dataset = $metastore->getValidMetadataFactory()->get($edited, 'dataset');
    $this->assertEquals($id, $metastore->patch('dataset', $id, $dataset));

    $rev = drupal_static('metastore_resource_mapper_new_revision');
    $this->assertEquals(1, $rev);

    // Clean up after ourselves.
    $this->assertEquals($id, $metastore->delete('dataset', $id));
  }

  public function tearDown(): void {
    parent::tearDown();
    $this->removeHarvests();
    $this->removeAllNodes();
    $this->removeAllMappedFiles();
    $this->removeAllFileFetchingJobs();
    $this->flushQueues();
    $this->removeFiles();
    $this->removeDatastoreTables();
  }

}
