<?php

namespace Drupal\Tests\dkan\Functional;

use Drupal\common\DataResource;
use Drupal\Core\Queue\QueueFactory;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\harvest\Load\Dataset;
use Drupal\harvest\HarvestService;
use Drupal\metastore\MetastoreService;
use Drupal\node\NodeStorage;
use Drupal\Tests\BrowserTestBase;
use Harvest\ETL\Extract\DataJson;
use RootedData\RootedJsonData;

/**
 * Class DatasetTest
 *
 * @group dkan
 * @group functional
 */
class DatasetBTBTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'datastore',
    'field',
    'harvest',
    'metastore',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'starterkit_theme';

  private const S3_PREFIX = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit';
  private const FILENAME_PREFIX = 'dkan_default_content_files_s3_amazonaws_com_phpunit_';

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
   * Test cleanup of orphaned draft distributions.
   */
  public function testOrphanDraftDistributionCleanup() {
    // Get the original configuration settings.
    $config = $this->container->get('config.factory');
    $datastoreSettings = $config->getEditable('datastore.settings');
    $defaultModerationState = $config->getEditable('workflows.workflow.dkan_publishing');

    // Set delete local resource files = false.
    $datastoreSettings->set('delete_local_resource', 0);

    // Set modified as a triggering property.
    $datastoreSettings->set('triggering_properties', ['modified'])->save();

    // Set default moderation state = draft.
    $defaultModerationState->set('type_settings.default_moderation_state', 'draft')->save();

    // Post dataset 1 and run the 'datastore_import' queue.
    $id_1 = uniqid(__FUNCTION__ . '1');
    $this->storeDatasetRunQueues($id_1, '1', ['1.csv']);

    // Get the dataset info.
    $metadata =  \Drupal::service('dkan.common.dataset_info')->gather($id_1);
    $distributionTable = $metadata['latest_revision']['distributions'][0]['table_name'];

    // Confirm distribution table exists.
    $databaseSchema = $this->container->get('database')->schema();
    $distributionTableExists = $databaseSchema->tableExists($distributionTable);
    $this->assertTrue($distributionTableExists, $distributionTable . ' exists.');

    // Get the associated distribution's resource directory
    $resourceId = $metadata['latest_revision']['distributions'][0]['resource_id'];
    $resourceVersion = $metadata['latest_revision']['distributions'][0]['resource_version'];
    $resourceDirectory = $resourceId . '_' . $resourceVersion;

    // Confirm distribution local directory exists.
    $this->assertDirectoryExists('public://resources/' . $resourceDirectory);

    // Update the modified date for the dataset.
    $this->getMetastore()->patch('dataset', $id_1, json_encode(['modified' => '06-05-2020']));

    // Simulate datastore_import and cleanup queues post update.
    $this->runQueues(['datastore_import', 'orphan_reference_processor']);

    // Confirm original distribution table removed.
    $distributionTableExists = $databaseSchema->tableExists($distributionTable);
    $this->assertFalse($distributionTableExists, $distributionTable . ' removed.');

    // Confirm original distribution local directory removed.
    $this->assertDirectoryDoesNotExist('public://resources/' . $resourceDirectory);
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
    $distributionId = $datasetMetadata['%Ref:distribution'][0]['identifier'];

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

    // Get the datastore configuration.
    $datastoreSettings = $this->container->get('config.factory')->getEditable('datastore.settings');

    // delete_local_resource is on.
    $datastoreSettings->set('delete_local_resource', 1)->save();

    // Post dataset 1 and run the 'datastore_import' queue.
    $this->storeDatasetRunQueues($id_1, '1', ['1.csv']);

    // Get local resource folder name.
    $dataset = $this->getMetastore()->get('dataset', $id_1);
    $datasetMetadata = $dataset->{'$'};
    $resourceId = explode('__', $datasetMetadata['%Ref:distribution'][0]['data']['%Ref:downloadURL'][0]['identifier']);
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
    $resourceId = explode('__', $datasetMetadata['%Ref:distribution'][0]['data']['%Ref:downloadURL'][0]['identifier']);
    $refUuid = $resourceId[0] . '_' . $resourceId[1];

    // Assert the local resource folder exists.
    $this->assertDirectoryExists('public://resources/' . $refUuid);
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
    $configFactory = $this->container->get('config.factory');
    $config = $configFactory->getEditable('metastore.settings');
    $config->set('resource_perspective_display', $perspective);
    $config->save();
  }

  private function getResourceDatastoreTable(object $resource) {
    return "{$resource->identifier}__{$resource->version}";
  }

  private function getResourceFromDataset(object $dataset) {
    $this->assertTrue(isset($dataset->{'%Ref:distribution'}));
    $this->assertTrue(isset($dataset->{'%Ref:distribution'}[0]));
    $this->assertTrue(isset($dataset->{'%Ref:distribution'}[0]->data));
    $this->assertTrue(isset($dataset->{'%Ref:distribution'}[0]->data->{'%Ref:downloadURL'}));
    $this->assertTrue(isset($dataset->{'%Ref:distribution'}[0]->data->{'%Ref:downloadURL'}[0]));
    $this->assertTrue(isset($dataset->{'%Ref:distribution'}[0]->data->{'%Ref:downloadURL'}[0]->data));

    return $dataset->{'%Ref:distribution'}[0]->data->{'%Ref:downloadURL'}[0]->data;
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
    /** @var \Drupal\metastore\ValidMetadataFactory $valid_metadata_factory */
    $valid_metadata_factory = $this->container->get('dkan.metastore.valid_metadata');

    $data = new \stdClass();
    $data->title = $title;
    $data->description = 'Some description.';
    $data->identifier = $identifier;
    $data->accessLevel = 'public';
    $data->modified = '06-04-2020';
    $data->keyword = ['some keyword'];
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
      $distribution->title = 'Distribution #' . $key . ' for ' . $identifier;
      $distribution->downloadURL = $this->getDownloadUrl($downloadUrl);
      $distribution->format = 'csv';
      $distribution->mediaType = 'text/csv';

      $data->distribution[] = $distribution;
    }
    $this->assertGreaterThan(
      0,
      count($data->distribution),
      'JSON Schema requires one or more distributions.'
    );
    // @todo: Figure out how to assert against $factory->getResult()->getError()
    //   so we can have a useful test fail message.
    return $valid_metadata_factory->get(json_encode($data), 'dataset');
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
    $queueWorkerManager = $this->container->get('plugin.manager.queue_worker');
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
    /** @var \Drupal\Core\Database\Connection $db */
    $db = $this->container->get('database');

    $tables = $db->schema()->findTables('datastore_%');
    return count($tables);
  }

  private function checkFiles() {
    /** @var \Drupal\Core\File\FileSystemInterface $fileSystem */
    $fileSystem = $this->container->get('file_system');

    $dir = $fileSystem->dirname('public://resources');
    // Nothing to check if the resource folder does not exist.
    if (!is_dir($dir)) {
      return [];
    }
    $filesObjects = $fileSystem->scanDirectory($dir, '/.*\.csv$/i', ['recurse' => TRUE]);
    $filenames = array_values(array_map(function ($obj) {
      return str_replace(self::FILENAME_PREFIX, '', $obj->filename);
    }, $filesObjects));
    sort($filenames);
    return $filenames;
  }

  private function queryResource(object $resource, string $queryString) {
    /** @var \Drupal\datastore\SqlEndpoint\DatastoreSqlEndpointService $sqlEndpoint */
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
   * @return \Drupal\metastore\MetastoreService
   */
  private function getMetastore(): MetastoreService {
    return \Drupal::service('dkan.metastore.service');
  }

}
