<?php

namespace Drupal\Tests\metastore\Functional\Storage;

use Drupal\common\Resource;
use Drupal\Core\Queue\QueueFactory;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\harvest\Load\Dataset;
use Drupal\harvest\Service as Harvester;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Service as Metastore;
use Drupal\metastore_search\Search;
use Drupal\node\NodeStorage;
use Drupal\search_api\Entity\Index;
use Drupal\Tests\common\Traits\CleanUp;
use Drupal\Tests\metastore\Unit\ServiceTest;
use Harvest\ETL\Extract\DataJson;
use RootedData\RootedJsonData;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * NodeData Functional Tests.
 *
 * @package Drupal\Tests\dkan\Functional
 * @group dkan
 */
class NodeDataDatasetTest extends ExistingSiteBase {
  use CleanUp;

  private const S3_PREFIX = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit';

  public function setUp(): void {
    parent::setUp();
    $this->removeHarvests();
    $this->removeAllNodes();
    $this->removeAllMappedFiles();
    $this->removeAllFileFetchingJobs();
    $this->flushQueues();
    $this->removeFiles();
    $this->removeDatastoreTables();
    $this->setDefaultModerationState("draft");

    $this->validMetadataFactory = ServiceTest::getValidMetadataFactory($this);
  }

  /**
   * Test resource removal on distribution deleting.
   */
  public function testStorageRetrieveMethods() {

    // Post a dataset with a single distribution.
    $this->datasetPostTwoAndUnpublishOne();
    $datasetStorage = $this->getMetastore()->getStorage('dataset');

    $this->assertEquals(1, $datasetStorage->count());
    $this->assertEquals(2, $datasetStorage->count(TRUE));

    $allPublished = $datasetStorage->retrieveAll();
    $datasetData = json_decode($allPublished[0]);
    $this->assertEquals("123", $datasetData->identifier);
    $this->assertEquals(1, count($allPublished));

    $all = $datasetStorage->retrieveAll(NULL, NULL, TRUE);
    $this->assertEquals(2, count($all));

    $rangeUnpublished = $datasetStorage->retrieveAll(0, 1, TRUE);
    $this->assertEquals(1, count($rangeUnpublished));

    $allIds = $datasetStorage->retrieveIds(NULL, NULL, TRUE);
    $this->assertEquals(2, count($allIds));
    $this->assertEquals("456", $allIds[1]);

    $this->expectException(MissingObjectException::class);
    $datasetStorage->retrievePublished('abc');

  }

    /**
   * Test resource removal on distribution deleting.
   */
  public function testBadPublish() {
    $this->datasetPostTwoAndUnpublishOne();
    $datasetStorage = $this->getMetastore()->getStorage('dataset');

    $result = $datasetStorage->publish("123");
    $this->assertFalse($result);

    $this->expectException(MissingObjectException::class);
    $datasetStorage->publish("abc");
  }

  private function datasetPostTwoAndUnpublishOne() {
    $datasetRootedJsonData = $this->getData("123", 'Test Published', []);
    $dataset = json_decode($datasetRootedJsonData);

    $uuid = $this->getMetastore()->post('dataset', $datasetRootedJsonData);
    $this->getMetastore()->publish('dataset', $uuid);

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

    $datasetRootedJsonData = $this->getData("456", 'Test Unpublished', []);
    $this->getMetastore()->post('dataset', $datasetRootedJsonData);
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

    foreach ($downloadUrls as $key => $downloadUrl) {
      $distribution = new \stdClass();
      $distribution->title = "Distribution #{$key} for {$identifier}";
      $distribution->downloadURL = $this->getDownloadUrl($downloadUrl);
      $distribution->mediaType = "text/csv";

      $data->distribution[] = $distribution;
    }

    return $this->validMetadataFactory->get(json_encode($data), 'dataset');
  }

  private function getMetastore(): Metastore {
    return \Drupal::service('dkan.metastore.service');
  }

  private function setDefaultModerationState($state = 'published') {
    /** @var \Drupal\Core\Config\ConfigFactory $config */
    $config = \Drupal::service('config.factory');
    $defaultModerationState = $config->getEditable('workflows.workflow.dkan_publishing');
    $defaultModerationState->set('type_settings.default_moderation_state', $state);
    $defaultModerationState->save();
  }

  private function getDownloadUrl(string $filename) {
    return self::S3_PREFIX . '/' . $filename;
  }

}
