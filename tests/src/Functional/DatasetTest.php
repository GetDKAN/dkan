<?php

namespace Drupal\Tests\dkan\Functional;

use Drupal\common\DataResource;
use Drupal\Core\Queue\QueueFactory;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\harvest\Load\Dataset;
use Drupal\harvest\HarvestService;
use Drupal\metastore\MetastoreService;
use Drupal\metastore_search\Search;
use Drupal\node\NodeStorage;
use Drupal\search_api\Entity\Index;
use Drupal\Tests\common\Traits\CleanUp;
use Drupal\Tests\metastore\Unit\MetastoreServiceTest;
use Harvest\ETL\Extract\DataJson;
use RootedData\RootedJsonData;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Class DatasetTest
 *
 * @package Drupal\Tests\dkan\Functional
 * @group dkan
 */
class DatasetTest extends ExistingSiteBase {
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
    $this->setDefaultModerationState();
    $this->changeDatasetsResourceOutputPerspective();
    $this->validMetadataFactory = MetastoreServiceTest::getValidMetadataFactory($this);
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
    $this->setDefaultModerationState();
    $this->changeDatasetsResourceOutputPerspective();
  }

  public function testChangingDatasetResourcePerspectiveOnOutput() {
    $this->datastoreImportAndQuery();

    drupal_flush_all_caches();

    $this->changeDatasetsResourceOutputPerspective(ResourceLocalizer::LOCAL_URL_PERSPECTIVE);

    $metadata = $this->getMetastore()->get('dataset', 123);
    $dataset = json_decode($metadata);

    $this->assertNotEquals(
      $dataset->distribution[0]->downloadURL,
      $this->getDownloadUrl('district_centerpoints_small.csv')
    );
  }

  /**
   * Test the resource purger when the default moderation state is 'published'.
   */
  public function testResourcePurgePublished() {
    $id_1 = uniqid(__FUNCTION__ . '1');

    // Post then update a dataset with multiple, changing resources.
    $this->storeDatasetRunQueues($id_1, '1.1', ['1.csv', '2.csv']);
    $this->storeDatasetRunQueues($id_1, '1.2', ['2.csv', '4.csv'], 'put');

    // Verify only the 2 most recent resources remain.
    $this->assertEquals(['2.csv', '4.csv'], $this->checkFiles());
    $this->assertEquals(2, $this->countTables());
  }

  /**
   * Test the resource purger when the default moderation state is 'draft'.
   *
   * @runInSeparateProcess
   */
  public function testResourcePurgeDraft() {
    $id_1 = uniqid(__FUNCTION__ . '1');
    $id_2 = uniqid(__FUNCTION__ . '2');
    $id_3 = uniqid(__FUNCTION__ . '3');

    $this->setDefaultModerationState('draft');

    // Post, update and publish a dataset with multiple, changing resources.
    $this->storeDatasetRunQueues($id_1, '1.1', ['1.csv', '2.csv']);
    $this->storeDatasetRunQueues($id_1, '1.2', ['3.csv', '1.csv'], 'put');
    $this->getMetastore()->publish('dataset', $id_1);
    $this->storeDatasetRunQueues($id_1, '1.3', ['1.csv', '5.csv'], 'put');

    /** @var \Drupal\common\DatasetInfo $datasetInfo */
    $datasetInfo = \Drupal::service('dkan.common.dataset_info');
    $info = $datasetInfo->gather($id_1);
    $this->assertStringEndsWith('1.csv', $info['latest_revision']['distributions'][0]['file_path']);
    $this->assertStringEndsWith('5.csv', $info['latest_revision']['distributions'][1]['file_path']);
    $this->assertStringEndsWith('3.csv', $info['published_revision']['distributions'][0]['file_path']);
    $this->assertStringEndsWith('1.csv', $info['published_revision']['distributions'][1]['file_path']);

    // Verify that only the resources associated with the published and the
    // latest revision.
    $this->assertEquals(['1.csv', '3.csv', '5.csv'], $this->checkFiles());
    $this->assertEquals(3, $this->countTables());

    // Add more datasets, only publishing some.
    $this->storeDatasetRunQueues($id_2, '2.1', []);
    $this->storeDatasetRunQueues($id_3, '3.1', []);
    $this->getMetastore()->publish('dataset', $id_2);
    // Reindex.
    $index = Index::load('dkan');
    $index->clear();
    $index->indexItems();
    // Verify search results contain the '1.2' version of $id_1, $id_2 but not $id_3.
    $searchResults = $this->getMetastoreSearch()->search();
    $this->assertEquals(2, $searchResults->total);
    $this->assertArrayHasKey('dkan_dataset/' . $id_1, $searchResults->results);
    $this->assertEquals('1.2', $searchResults->results['dkan_dataset/' . $id_1]->title);
    $this->assertArrayHasKey('dkan_dataset/' . $id_2, $searchResults->results);
    $this->assertArrayNotHasKey('dkan_dataset/' . $id_3, $searchResults->results);
  }


  /**
   * Test archiving of datasets after a harvest
   */
  public function testHarvestArchive() {

    $plan = $this->getPlan('testHarvestArchive', 'catalog-step-1.json');
    $harvester = $this->getHarvester();
    $harvester->registerHarvest($plan);

    // First harvest.
    $harvester->runHarvest('testHarvestArchive');

    // Ensure different harvest run identifiers, since based on timestamp.
    sleep(1);

    // Confirm we have some published datasets.
    $this->assertEquals('published', $this->getModerationState('1'));
    $this->assertEquals('published', $this->getModerationState('2'));

    // Run archive command, confirm datasets are archived.
    $harvester->archive('testHarvestArchive');
    $this->assertEquals('archived', $this->getModerationState('1'));
    $this->assertEquals('archived', $this->getModerationState('2'));
  }

  /**
   * Test removal of datasets by a subsequent harvest.
   */
  public function testHarvestOrphan() {

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
    $this->assertEquals('published', $this->getModerationState('2'));
    $this->assertEquals('orphaned', $this->getModerationState('3'));
    $this->assertEquals('published', $this->getModerationState('4'));
  }

  /**
   * Test resource removal on distribution deleting.
   */
  public function testDeleteDistribution() {
    $id_1 = uniqid(__FUNCTION__ . '1');

    // Post a dataset with a single distribution.
    $this->storeDatasetRunQueues($id_1, '1.1', ['1.csv']);

    // Get distribution id.
    $dataset = $this->getMetastore()->get('dataset', $id_1);
    $datasetMetadata = $dataset->{'$'};
    $distributionId = $datasetMetadata["%Ref:distribution"][0]["identifier"];

    // Load distribution node.
    $distributionNode = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['uuid' => $distributionId]);
    $distributionNode = reset($distributionNode);

    // Delete distribution node.
    $distributionNode->delete();
    $this->runQueues(['orphan_resource_remover']);

    // Verify that the resources are deleted.
    $this->assertEquals([], $this->checkFiles());
    $this->assertEquals(0, $this->countTables());
  }

  /**
   * Test local resource removal on datastore import.
   */
  public function testDatastoreImportDeleteLocalResource() {
    $id_1 = uniqid(__FUNCTION__ . '1');
    $id_2 = uniqid(__FUNCTION__ . '2');

    // Get the original config value.
    $datastoreSettings = \Drupal::service('config.factory')->getEditable('datastore.settings');
    $deleteLocalResourceOriginal = $datastoreSettings->get('delete_local_resource');

    // delete_local_resource is on.
    $datastoreSettings->set('delete_local_resource', 1)->save();

    // Post dataset 1 and run the 'datastore_import' queue.
    $this->storeDatasetRunQueues($id_1, '1', ['1.csv']);

    // Get local resource folder name.
    $dataset = $this->getMetastore()->get('dataset', $id_1);
    $datasetMetadata = $dataset->{'$'};
    $resourceId = explode('__', $datasetMetadata["%Ref:distribution"][0]["data"]["%Ref:downloadURL"][0]["identifier"]);
    $refUuid = $resourceId[0] . '_' . $resourceId[1];

    // Assert the local resource folder doesn't exist.
    $this->assertDirectoryExists('public://resources/');
    $this->assertDirectoryDoesNotExist('public://resources/' . $refUuid);

    // delete_local_resource is off.
    $datastoreSettings->set('delete_local_resource', 0)->save();

    // Post dataset 2 and run the 'datastore_import' queue.
    $this->storeDatasetRunQueues($id_2, '2', ['2.csv']);

    // Get local resource folder name.
    $dataset = $this->getMetastore()->get('dataset', $id_2);
    $datasetMetadata = $dataset->{'$'};
    $resourceId = explode('__', $datasetMetadata["%Ref:distribution"][0]["data"]["%Ref:downloadURL"][0]["identifier"]);
    $refUuid = $resourceId[0] . '_' . $resourceId[1];

    // Assert the local resource folder exists.
    $this->assertDirectoryExists('public://resources/' . $refUuid);

    // Restore the original config value.
    $datastoreSettings->set('delete_local_resource', $deleteLocalResourceOriginal)->save();
  }

  private function datasetPostAndRetrieve(): object {
    $datasetRootedJsonData = $this->getData(123, 'Test #1', ['district_centerpoints_small.csv']);
    $dataset = json_decode($datasetRootedJsonData);

    $uuid = $this->getMetastore()->post('dataset', $datasetRootedJsonData);

    $this->assertEquals(
      $dataset->identifier,
      $uuid
    );

    $datasetRootedJsonData = $this->getMetastore()->get('dataset', $uuid);
    $this->assertIsString("$datasetRootedJsonData");

    $retrievedDataset = json_decode($datasetRootedJsonData);

    $this->assertEquals(
      $retrievedDataset->identifier,
      $uuid
    );

    return $retrievedDataset;
  }

  private function datastoreImportAndQuery() {
    $dataset = $this->datasetPostAndRetrieve();
    $resource = $this->getResourceFromDataset($dataset);

    $this->runQueues(['datastore_import']);

    $queryString = "[SELECT * FROM {$this->getResourceDatastoreTable($resource)}][WHERE lon = \"61.33\"][ORDER BY lat DESC][LIMIT 1 OFFSET 0];";
    $this->queryResource($resource, $queryString);

    /**/
  }

  private function changeDatasetsResourceOutputPerspective(string $perspective = DataResource::DEFAULT_SOURCE_PERSPECTIVE) {
    $display = &drupal_static('metastore_resource_mapper_display');
    $display = $perspective;
  }

  private function getResourceDatastoreTable(object $resource) {
    return "{$resource->identifier}__{$resource->version}";
  }

  private function getResourceFromDataset(object $dataset) {
    $this->assertTrue(isset($dataset->{"%Ref:distribution"}));
    $this->assertTrue(isset($dataset->{"%Ref:distribution"}[0]));
    $this->assertTrue(isset($dataset->{"%Ref:distribution"}[0]->data));
    $this->assertTrue(isset($dataset->{"%Ref:distribution"}[0]->data->{"%Ref:downloadURL"}));
    $this->assertTrue(isset($dataset->{"%Ref:distribution"}[0]->data->{"%Ref:downloadURL"}[0]));
    $this->assertTrue(isset($dataset->{"%Ref:distribution"}[0]->data->{"%Ref:downloadURL"}[0]->data));

    return $dataset->{"%Ref:distribution"}[0]->data->{"%Ref:downloadURL"}[0]->data;
  }

  private function getDownloadUrl(string $filename) {
    return self::S3_PREFIX . '/' . $filename;
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
   * @return \RootedData\RootedJsonData
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
    $data->publisher = (object) [
      'name' => 'Test Publisher',
    ];
    $data->contactPoint = (object) [
      'fn' => 'Test Name',
      'hasEmail' => 'test@example.com',
    ];

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
    $datasetRootedJsonData = $this->getData($identifier, $title, $filenames);
    $this->httpVerbHandler($method, $datasetRootedJsonData, json_decode($datasetRootedJsonData));

    // Simulate a cron on queues relevant to this scenario.
    $this->runQueues(['datastore_import', 'resource_purger']);
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

  private function countTables() {
    /** @var $db \Drupal\Core\Database\Connection */
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
      return str_replace(self::FILENAME_PREFIX, '', $obj->filename);
    }, $filesObjects));
    sort($filenames);
    return $filenames;
  }

  private function queryResource(object $resource, string $queryString) {
    /** @var $sqlEndpoint \Drupal\datastore\SqlEndpoint\DatastoreSqlEndpointService */
    $sqlEndpoint = \Drupal::service('dkan.datastore.sql_endpoint.service');
    $results = $sqlEndpoint->runQuery($queryString);
    $this->assertGreaterThan(0, count($results));
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

  private function setDefaultModerationState($state = 'published') {
    /** @var \Drupal\Core\Config\ConfigFactory $config */
    $config = \Drupal::service('config.factory');
    $defaultModerationState = $config->getEditable('workflows.workflow.dkan_publishing');
    $defaultModerationState->set('type_settings.default_moderation_state', $state);
    $defaultModerationState->save();
  }

  private function getQueueService() : QueueFactory {
    return \Drupal::service('queue');
  }

  private function getHarvester() : HarvestService {
    return \Drupal::service('dkan.harvest.service');
  }

  private function getNodeStorage(): NodeStorage {
    return \Drupal::service('entity_type.manager')->getStorage('node');
  }

  /**
   * @return \Drupal\metastore_search\Search
   */
  private function getMetastoreSearch() : Search {
    return \Drupal::service('dkan.metastore_search.service');
  }

  /**
   * @return \Drupal\metastore\MetastoreService
   */
  private function getMetastore(): MetastoreService {
    return \Drupal::service('dkan.metastore.service');
  }

}
