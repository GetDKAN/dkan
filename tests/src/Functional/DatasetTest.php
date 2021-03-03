<?php

namespace Drupal\Tests\dkan\Functional;

use Drupal\Core\Queue\QueueFactory;
use Drupal\datastore\Plugin\QueueWorker\Import;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\harvest\Load\Dataset;
use Drupal\harvest\Service as Harvester;
use Drupal\metastore\Exception\UnmodifiedObjectException;
use Drupal\metastore\Service as Metastore;
use Drupal\metastore_search\Search;
use Drupal\node\NodeStorage;
use Drupal\search_api\Entity\Index;
use Drupal\Tests\common\Traits\CleanUp;
use Drupal\Tests\common\Traits\GetDataTrait;
use Drupal\Tests\common\Traits\ServiceCheckTrait;
use Harvest\ETL\Extract\DataJson;
use weitzman\DrupalTestTraits\ExistingSiteBase;

class DatasetTest extends ExistingSiteBase {
  use ServiceCheckTrait;
  use CleanUp;
  use GetDataTrait;

  public function setUp() {
    parent::setUp();
    $this->removeHarvests();
    $this->removeAllNodes();
    $this->removeAllMappedFiles();
    $this->removeAllFileFetchingJobs();
    $this->flushQueues();
    $this->removeFiles();
    $this->removeDatastoreTables();
  }

  public function test() {

    // Test posting a dataset to the metastore.
    $dataset = $this->getDataset(123, 'Test #1', ['district_centerpoints_small.csv']);
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
    $dataset = $this->getDataset(123, 'Test #1', ['district_centerpoints_small.csv']);
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
    $this->assertNotEquals($localUrlDataset->distribution[0]->downloadURL,
      $this->getDownloadUrl('district_centerpoints_small.csv'));
  }

  /**
   * Test the resource purger when the default moderation state is 'published'.
   */
  public function test3() {

    // Post then update a dataset with multiple, changing resources.
    $this->storeDatasetRunQueues(111, '1.1', ['1.csv', '2.csv']);
    $this->storeDatasetRunQueues(111, '1.2', ['2.csv', '4.csv'], 'put');

    // Verify only the 2 most recent resources remain.
    $this->assertEquals(['2.csv', '4.csv'], $this->checkFiles());
    $this->assertEquals(2, $this->countTables());
  }

  /**
   * Test the resource purger when the default moderation state is 'draft'.
   */
  public function test4() {
    /** @var \Drupal\Core\Config\ConfigFactory $config */
    $config = \Drupal::service('config.factory');
    $defaultModerationState = $config->getEditable('workflows.workflow.dkan_publishing');
    $defaultModerationState->set('type_settings.default_moderation_state', 'draft');
    $defaultModerationState->save();

    // Post, update and publish a dataset with multiple, changing resources.
    $this->storeDatasetRunQueues(111, '1.1', ['1.csv', '2.csv']);
    $this->storeDatasetRunQueues(111, '1.2', ['3.csv', '1.csv'], 'put');
    $this->getMetastore()->publish('dataset', 111);
    $this->storeDatasetRunQueues(111, '1.3', ['1.csv', '5.csv'], 'put');

    // Verify dataset information.
    /** @var \Drupal\common\DatasetInfo $datasetInfo */
    $datasetInfo = \Drupal::service('dkan.common.dataset_info');
    $info = $datasetInfo->gather('111');
    $this->assertEquals('1.csv', substr($info['latest_revision']['distributions'][0]['file_path'], -5));
    $this->assertEquals('5.csv', substr($info['latest_revision']['distributions'][1]['file_path'], -5));
    $this->assertEquals('3.csv', substr($info['published_revision']['distributions'][0]['file_path'], -5));
    $this->assertEquals('1.csv', substr($info['published_revision']['distributions'][1]['file_path'], -5));

    // Verify that only the resources associated with the published and the
    // latest revision.
    $this->assertEquals(['1.csv', '3.csv', '5.csv'], $this->checkFiles());
    $this->assertEquals(3, $this->countTables());

    // Add more datasets, only publishing some.
    $this->storeDatasetRunQueues(222, '2.1', []);
    $this->storeDatasetRunQueues(333, '3.1', []);
    $this->getMetastore()->publish('dataset', 222);
    // Reindex.
    $index = Index::load('dkan');
    $index->clear();
    $index->indexItems();
    // Verify search results contain the '1.2' version of 111, 222 but not 333.
    $searchResults = $this->getMetastoreSearch()->search();
    $this->assertEquals(2, $searchResults->total);
    $this->assertArrayHasKey('dkan_dataset/111', $searchResults->results);
    $this->assertEquals('1.2', $searchResults->results['dkan_dataset/111']->title);
    $this->assertArrayHasKey('dkan_dataset/222', $searchResults->results);
    $this->assertArrayNotHasKey('dkan_dataset/333', $searchResults->results);

    $defaultModerationState->set('type_settings.default_moderation_state', 'published');
    $defaultModerationState->save();
  }

  /**
   * Test removal of datasets by a subsequent harvest.
   */
  public function test5() {

    $plan = $this->getPlan('test5', 'catalog-step-1.json');
    $harvester = $this->getHarvester();
    $harvester->registerHarvest($plan);

    // First harvest.
    $harvester->runHarvest('test5');

    // Ensure different harvest run identifiers, since based on timestamp.
    sleep(1);

    // Second harvest, re-register with different catalog to simulate change.
    $plan->extract->uri = 'file://' . __DIR__ . '/../../files/catalog-step-2.json';
    $harvester->registerHarvest($plan);
    $result = $harvester->runHarvest('test5');

    // Test unchanged, updated and new datasets.
    $expected = [
      '1' => 'UNCHANGED',
      '2' => 'UPDATED',
      '4' => 'NEW',
    ];
    $this->assertEquals($expected, $result['status']['load']);

    $this->assertEquals('published', $this->getModerationState('1'));
    $this->assertEquals('published' , $this->getModerationState('2'));
    $this->assertEquals('orphaned' , $this->getModerationState('3'));
    $this->assertEquals('published' , $this->getModerationState('4'));
  }

  /**
   * Generate a harvest plan object.
   */
  private function getPlan(string $identifier, string $testFilename) : \stdClass {
    return (object) [
      'identifier' => $identifier,
      'extract' => (object) [
        'type' => DataJson::class,
        'uri' => 'file://' . __DIR__ . '/../../files/' . $testFilename,
      ],
      'transforms' => [],
      'load' => (object) [
        'type' => Dataset::class,
      ],
    ];
  }

  /**
   * Get a dataset's moderation state.
   */
  private function getModerationState(string $uuid) : string {
    $nodeStorage = $this->getNodeStorage();
    $datasets = $nodeStorage->loadByProperties(['uuid' => $uuid]);
    if (FALSE !== ($dataset = reset($datasets))) {
      return $dataset->get('moderation_state')->getString();
    }
    return '';
  }

  /**
   * Store or update a dataset,run datastore_import and resource_purger queues.
   */
  private function storeDatasetRunQueues(string $identifier, string $title, array $filenames, string $method = 'post') {
    $datasetJson = $this->getDataset($identifier, $title, $filenames);
    $this->httpVerbHandler($method, $datasetJson, json_decode($datasetJson));

    // Simulate a cron on queues relevant to this scenario.
    $this->runQueues(['datastore_import', 'resource_purger']);
  }

  /**
   * Process queues in a predictible order.
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

  private function countTables() {
    /* @var $db \Drupal\Core\Database\Connection */
    $db = \Drupal::service('database');

    $tables = $db->schema()->findTables("datastore_%");
    return count($tables);
  }

  private function checkFiles() {
    /** @var \Drupal\Core\File\FileSystemInterface $fileSystem */
    $fileSystem = \Drupal::service('file_system');

    $dir = DRUPAL_ROOT . "/sites/default/files/resources";
    // Nothing to check if the resource folder does not exist.
    if (!is_dir($dir)) {
      return [];
    }
    $filesObjects = $fileSystem->scanDirectory($dir, "/.*\.csv$/i", ['recurse' => TRUE]);
    $filenames = array_values(array_map(function ($obj) {
      return str_replace($this->FILENAME_PREFIX, '', $obj->filename);
    }, $filesObjects));
    sort($filenames);
    return $filenames;
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
    $queue = $this->getQueueService()->get('datastore_import');
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

    $identifier = $this->httpVerbHandler($method, $datasetJson, $dataset);
    $this->assertEquals($dataset->identifier, $identifier);

    $datasetWithReferences = json_decode($this->getMetastore()->get('dataset', $identifier));
    $fileData = $datasetWithReferences->{"%Ref:distribution"}[0]
      ->data->{"%Ref:downloadURL"}[0]
      ->data;

    $this->assertEquals($downloadUrl, $fileData->filePath);

    return $fileData;
  }

  private function httpVerbHandler(string $method, string $json, $dataset) {

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

  private function getQueueService() : QueueFactory {
    return \Drupal::service('queue');
  }

  private function getHarvester() : Harvester {
    return \Drupal::service('dkan.harvest.service');
  }

  private function getNodeStorage(): NodeStorage {
    return \Drupal::service('entity_type.manager')->getStorage('node');
  }

  /**
   * @return \Drupal\metastore_search\Search
   */
  private function getMetastoreSearch() : Search {
    return \Drupal::service('metastore_search.service');
  }

}
