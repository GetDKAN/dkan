<?php

namespace Drupal\Tests\metastore\Functional;

use Drupal\Core\Queue\QueueFactory;
use Drupal\metastore\Service as Metastore;
use Drupal\Tests\common\Traits\CleanUp;
use Drupal\Tests\metastore\Unit\ServiceTest;
use GuzzleHttp\Client;
use RootedData\RootedJsonData;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Class DatasetTest
 *
 * @package Drupal\Tests\dkan\Functional
 * @group dkan
 */
class MetastoreApiPageCacheTest extends ExistingSiteBase {
  use CleanUp;

  private const S3_PREFIX = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit';
  private const FILENAME_PREFIX = 'dkan_default_content_files_s3_amazonaws_com_phpunit_';

  private $validMetadataFactory;

  public function setUp(): void {
    parent::setUp();
    $this->removeHarvests();
    $this->removeAllNodes();
    $this->removeAllMappedFiles();
    $this->removeAllFileFetchingJobs();
    $this->flushQueues();
    $this->removeFiles();
    $this->removeDatastoreTables();

    $this->validMetadataFactory = ServiceTest::getValidMetadataFactory($this);
  }

  /**
   * Test dataset page caching
   */
  public function testDatasetApiPageCache() {
    // Post dataset.
    $datasetRootedJsonData = $this->getData(111, '1', ['1.csv']);
    $this->httpVerbHandler('post', $datasetRootedJsonData, json_decode($datasetRootedJsonData));

    $client = new Client([
      'base_uri' => \Drupal::request()->getSchemeAndHttpHost(),
      'timeout'  => 2.0,
      'http_errors' => FALSE,
    ]);

    // Request once, should not return cached version.
    $response = $client->head('api/1/metastore/schemas/dataset/items/111');
    $this->assertEquals("MISS", $response->getHeaders()['X-Drupal-Cache'][0]);

    // Request again, should return cached version.
    $response = $client->head('api/1/metastore/schemas/dataset/items/111');
    $this->assertEquals("HIT", $response->getHeaders()['X-Drupal-Cache'][0]);

    // Importing the datastore should invalidate the cache.
    $this->runQueues(['datastore_import', 'resource_purger']);
    $response = $client->head('api/1/metastore/schemas/dataset/items/111');
    $this->assertEquals("MISS", $response->getHeaders()['X-Drupal-Cache'][0]);

    // Request again, should return cached version.
    $response = $client->head('api/1/metastore/schemas/dataset/items/111');
    $this->assertEquals("HIT", $response->getHeaders()['X-Drupal-Cache'][0]);

    // Editing the dataset should invalidate the cache.
    $datasetRootedJsonData->{'$.description'} = "Add a description.";
    $this->httpVerbHandler('put', $datasetRootedJsonData, json_decode($datasetRootedJsonData));
    $response = $client->head('api/1/metastore/schemas/dataset/items/111');
    $this->assertEquals("MISS", $response->getHeaders()['X-Drupal-Cache'][0]);
  }

  /**
   * Generate dataset metadata, possibly with multiple distributions.
   *
   * @param string $identifier
   *   Dataset identifier.
   * @param string $title
   *   Dataset title.
   * @param array $downloadUrls
   *   Array of resource files URLs for this dataset.
   *
   * @return string|false
   *   Json encoded string of this dataset's metadata, or FALSE if error.
   */
  private function getData(string $identifier, string $title, array $downloadUrls): RootedJsonData {

    $data = new \stdClass();
    $data->title = $title;
    $data->description = "Some description.";
    $data->identifier = $identifier;
    $data->accessLevel = "public";
    $data->modified = "06-04-2020";
    $data->keyword = ["some keyword"];
    $data->distribution = [];

    foreach ($downloadUrls as $key => $downloadUrl) {
      $distribution = new \stdClass();
      $distribution->title = "Distribution #{$key} for {$identifier}";
      $distribution->downloadURL = $this->getDownloadUrl($downloadUrl);
      $distribution->mediaType = "text/csv";

      $data->distribution[] = $distribution;
    }

    return $this->validMetadataFactory->get(json_encode($data), 'dataset');
  }

  /**
   * Process queues in a predictable order.
   */
  private function runQueues(array $relevantQueues = []) {
    /** @var \Drupal\Core\Queue\QueueWorkerManager $queueWorkerManager */
    $queueWorkerManager = \Drupal::service('plugin.manager.queue_worker');
    foreach ($relevantQueues as $queueName) {
      $worker = $queueWorkerManager->createInstance($queueName);
      $queue = $this->getQueueService()->get($queueName);
      while ($item = $queue->claimItem()) {
        $worker->processItem($item->data);
        $queue->deleteItem($item);
      }
    }
  }

  private function httpVerbHandler(string $method, RootedJsonData $json, $dataset) {

    if ($method == 'post') {
      $identifier = $this->getMetastore()->post('dataset', $json);
    }
    // PUT for now, refactor later if more verbs are needed.
    else {
      $id = $dataset->identifier;
      $info = $this->getMetastore()->put('dataset', $id, $json);
      $identifier = $info['identifier'];
    }

    return $identifier;
  }

  private function getMetastore(): Metastore {
    return \Drupal::service('dkan.metastore.service');
  }

  private function getDownloadUrl(string $filename) {
    return self::S3_PREFIX . '/' . $filename;
  }

  private function getQueueService() : QueueFactory {
    return \Drupal::service('queue');
  }

}
