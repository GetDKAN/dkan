<?php

namespace Drupal\Tests\dkan\Functional;

use Drupal\common\DataResource;
use Drupal\Core\Queue\QueueFactory;
use Drupal\metastore\MetastoreService;
use Drupal\metastore_search\Search;
use Drupal\search_api\Entity\Index;
use Drupal\Tests\common\Traits\CleanUp;
use Drupal\Tests\metastore\Unit\MetastoreServiceTest;
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

  /**
   * Test the resource purger when the default moderation state is 'draft'.
   *
   * @runInSeparateProcess
   *
   * @todo Move this test to \Drupal\Tests\dkan\Functional\DatasetBTBTest.
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

  private function changeDatasetsResourceOutputPerspective(string $perspective = DataResource::DEFAULT_SOURCE_PERSPECTIVE) {
    $configFactory = \Drupal::service('config.factory');
    $config = $configFactory->getEditable('metastore.settings');
    $config->set('resource_perspective_display', $perspective);
    $config->save();
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
      $distribution->title = "Distribution #{$key} for {$identifier}";
      $distribution->downloadURL = $this->getDownloadUrl($downloadUrl);
      $distribution->mediaType = 'text/csv';

      $data->distribution[] = $distribution;
    }

    return $this->validMetadataFactory->get(json_encode($data), 'dataset');
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
    /** @var \Drupal\Core\Database\Connection $db */
    $db = \Drupal::service('database');

    $tables = $db->schema()->findTables('datastore_%');
    return count($tables);
  }

  private function checkFiles() {
    /** @var \Drupal\Core\File\FileSystemInterface $fileSystem */
    $fileSystem = \Drupal::service('file_system');

    $dir = DRUPAL_ROOT . '/sites/default/files/resources';
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
