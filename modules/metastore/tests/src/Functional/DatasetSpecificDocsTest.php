<?php

namespace Drupal\Tests\metastore\Functional;

use Drupal\Tests\common\Traits\CleanUp;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 *
 */
class DatasetSpecificDocsTest extends ExistingSiteBase {
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

    // Test posting a dataset to the metastore.
    $dataset = $this->getData($this->downloadUrl);

    /** @var \Drupal\metastore\MetastoreService $metastore */
    $metastore = \Drupal::service('dkan.metastore.service');
    $dataset = $metastore->getValidMetadataFactory()->get($dataset, 'dataset');
    $metastore->post('dataset', $dataset);

    $docService = \Drupal::service('dkan.metastore.dataset_api_docs');
    $spec = $docService->getDatasetSpecific('123');
    $this->assertTrue(is_array($spec));
    $this->assertEquals("123", $spec['components']['parameters']['datasetUuid']['example']);
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
