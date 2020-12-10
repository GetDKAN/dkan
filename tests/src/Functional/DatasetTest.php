<?php

namespace Drupal\Tests\dkan\Functional;

use Drupal\Core\Queue\QueueFactory;
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

  private const S3_PREFIX = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit';
  private const FILENAME_PREFIX = 'dkan_default_content_files_s3_amazonaws_com_phpunit_';

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
   * @return string|false
   *   Json encoded string of this dataset's metadata, or FALSE if error.
   */
  private function getData(string $identifier, string $title, array $downloadUrls) {

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

    return json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
  }

  public function setUp() {
    parent::setUp();
    $this->removeAllNodes();
    $this->removeAllMappedFiles();
    $this->removeAllFileFetchingJobs();
    $this->flushQueues();
    $this->removeFiles();
    $this->removeDatastoreTables();
  }

  public function test() {

    // Test posting a dataset to the metastore.
    $dataset = $this->getData(123, 'Test #1', ['district_centerpoints_small.csv']);
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
    $dataset = $this->getData(123, 'Test #1', ['district_centerpoints_small.csv']);
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

    // Test the resource purger by posting a dataset, then updating its file.
    $this->storeDatasetRunQueues(111, '1.1', ['1.csv']);
    $this->storeDatasetRunQueues(111, '1.2', ['2.csv'], 'put');

    // Verify only 2.csv remains in the resources folder, and 1 datastore table.
    $this->assertEquals(['2.csv'], $this->checkFiles());
    $this->assertEquals(1, $this->countTables());
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

    // Test the resource purger by posting, updating and publishing.
    $this->storeDatasetRunQueues(111, '1.1', ['1.csv']);
    $this->storeDatasetRunQueues(111, '1.2', ['2.csv'], 'put');
    $this->getMetastore()->publish('dataset', 111);
    $this->storeDatasetRunQueues(111, '1.3', ['3.csv'], 'put');

    // Verify that only the resources associated with the published and the
    // latest revision.
    $this->assertEquals(['2.csv', '3.csv'], $this->checkFiles());
    $this->assertEquals(2, $this->countTables());

    $defaultModerationState->set('type_settings.default_moderation_state', 'published');
    $defaultModerationState->save();
  }

  /**
   * Store or update a dataset,run datastore_import and resource_purger queues.
   */
  private function storeDatasetRunQueues(string $identifier, string $title, array $filenames, string $method = 'post') {
    $datasetJson = $this->getData($identifier, $title, $filenames);
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
      return str_replace(self::FILENAME_PREFIX, '', $obj->filename);
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

  private function getMetastore(): Service {
    return \Drupal::service('dkan.metastore.service');
  }

  private function getQueueService() : QueueFactory {
    return \Drupal::service('queue');
  }

}
