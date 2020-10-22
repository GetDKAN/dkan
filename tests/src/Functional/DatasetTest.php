<?php

namespace Drupal\Tests\dkan\Functional;

use Drupal\datastore\Plugin\QueueWorker\Import;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\metastore\Exception\UnmodifiedObjectException;
use Drupal\metastore\Service;
use Drupal\Tests\common\Traits\CleanUp;
use Drupal\Tests\common\Traits\ServiceCheckTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

class DatasetTest extends ExistingSiteBase {
  use ServiceCheckTrait;
  use CleanUp;

  private $downloadUrl = "https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv";

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

  public function test() {

    // Test posting a dataset to the metastore.
    $dataset = $this->getData($this->downloadUrl);
    $data1 = $this->checkDatasetIn($dataset);

    // Test that nothing changes on put.
    try {
      $this->checkDatasetIn($dataset, 'put');
      $this->assertTrue(FALSE);
    }
    catch(UnmodifiedObjectException $e) {
      $this->assertTrue(TRUE);
    }

    // Test a new file/resource revision is created.
    $rev = &drupal_static('metastore_resource_mapper_new_revision');
    $rev = 1;
    $object = json_decode($dataset);
    $object->modified = "06-05-2020";
    $dataset = json_encode($object);
    $data3 = $this->checkDatasetIn($dataset, 'put');
    $this->assertNotEquals($data1, $data3);
  }

  public function test2() {

    // Test posting a dataset to the metastore.
    $dataset = $this->getData($this->downloadUrl);
    $data1 = $this->checkDatasetIn($dataset);

    // Process datastore operations. This will include downloading the remote
    // CSV file and registering a local url and file with the resource mapper.
    $this->datastoreProcesses($data1);

    // Check that the imported file can be queried with the SQL Endpoint.
    $this->queryResource($data1);

    drupal_flush_all_caches();

    // Test that local url is displayed.
    $display = &drupal_static('metastore_resource_mapper_display');
    $display = ResourceLocalizer::LOCAL_URL_PERSPECTIVE;
    $localUrlDataset = json_decode($this->getMetastore()->get('dataset', json_decode($dataset)->identifier));
    $this->assertNotEqual($localUrlDataset->distribution[0]->downloadURL,
    $this->downloadUrl);
  }

  private function queryResource($fileData) {
    /* @var $sqlEndpoint \Drupal\datastore\SqlEndpoint\Service */
    $sqlEndpoint = \Drupal::service('dkan.datastore.sql_endpoint.service');

    $table = "{$fileData->identifier}__{$fileData->version}";
    $queryString = "[SELECT * FROM {$table}][WHERE lon = \"61.33\"][ORDER BY lat DESC][LIMIT 1 OFFSET 0];";

    $results = $sqlEndpoint->runQuery($queryString);
    $this->assertGreaterThan(0, count($results));
  }

  private function datastoreProcesses($fileData) {
    /* @var $queueFactory \Drupal\Core\Queue\QueueFactory */
    $queueFactory = \Drupal::service('queue');
    $queue = $queueFactory->get('datastore_import');
    $this->assertEquals(1, $queue->numberOfItems());

    /* @var $datastore \Drupal\datastore\Service */
    $datastore = \Drupal::service('dkan.datastore.service');

    $queueWorker = Import::create(\Drupal::getContainer(), [], 'blah', 'blah');
    $queueWorker->processItem((object) ['data' => [
      'identifier' => $fileData->identifier,
      'version' => $fileData->version,
    ]]);

    $result = $datastore->list();
    $this->assertEquals(1, count($result));
  }

  private function checkDatasetIn($datasetJson, $method = 'post', $downloadUrl = null) {
    $dataset = json_decode($datasetJson);

    if (!isset($downloadUrl)) {
      $downloadUrl = $dataset->distribution[0]->downloadURL;
    }

    if ($method == 'post') {
      $identifier = $this->getMetastore()->post('dataset', $datasetJson);
    }
    else {
      $id = $dataset->identifier;
      $info = $this->getMetastore()->put('dataset', $id, $datasetJson);
      $identifier = $info['identifier'];
    }

    $this->assertEquals($dataset->identifier, $identifier);

    $datasetWithReferences = json_decode($this->getMetastore()->get('dataset', $identifier));
    $fileData = $datasetWithReferences->{"%Ref:distribution"}[0]
      ->data->{"%Ref:downloadURL"}[0]
      ->data;

    $this->assertEquals($downloadUrl, $fileData->filePath);

    return $fileData;
  }

  private function getMetastore(): Service {
    return \Drupal::service('dkan.metastore.service');
  }

  public function tearDown() {
    parent::tearDown();
    $this->removeAllNodes();
    $this->removeAllMappedFiles();
    $this->removeAllFileFetchingJobs();
    $this->flushQueues();
    $this->removeFiles();
    $this->removeDatastoreTables();
  }

}
