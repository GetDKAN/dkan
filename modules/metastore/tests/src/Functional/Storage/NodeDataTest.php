<?php

namespace Drupal\Tests\metastore\Functional\Storage;

use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Service as Metastore;
use Drupal\metastore\Service;
use Drupal\Tests\common\Traits\CleanUp;
use Drupal\Tests\metastore\Unit\ServiceTest;
use RootedData\RootedJsonData;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * NodeData Functional Tests.
 *
 * @package Drupal\Tests\dkan\Functional
 * @group dkan
 * @group _functional
 */
class NodeDataTest extends ExistingSiteBase {
  use CleanUp;

  private const S3_PREFIX = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit';

  public function setUp(): void {
    parent::setUp();
    $this->setDefaultModerationState("draft");
    $this->validMetadataFactory = ServiceTest::getValidMetadataFactory($this);
  }

  protected function tearDown(): void {
    parent::tearDown();
    $this->removeHarvests();
    $this->removeAllNodes();
    $this->removeAllMappedFiles();
    $this->removeAllFileFetchingJobs();
    $this->flushQueues();
    $this->removeFiles();
    $this->removeDatastoreTables();
  }

  /**
   * Test resource removal on distribution deleting.
   */
  public function testStorageRetrieveMethods() {
//    $this->markTestIncomplete('Needs to clean up its CSV file.');

    // Post a dataset with a single distribution.
    $this->datasetPostTwoAndUnpublishOne();
    $datasetStorage = $this->getStorage('dataset');

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
    $datasetStorage->retrieve('abc');

  }

  /**
   * Test resource removal on distribution deleting.
   */
  public function testBadPublish() {
//    $this->markTestIncomplete('Needs to clean up its CSV file.');
    $this->datasetPostTwoAndUnpublishOne();
    $datasetStorage = $this->getStorage('dataset');

    $result = $datasetStorage->publish("123");
    $this->assertFalse($result);

    $this->expectException(MissingObjectException::class);
    $datasetStorage->publish("abc");
  }

  /**
   * Test resource removal on distribution deleting.
   */
  public function testRetrieveByHash() {
//    $this->markTestIncomplete('Needs to clean up its CSV file.');
    $this->datasetPostTwoAndUnpublishOne();
    $keywordStorage = $this->getStorage('keyword');

    $keyword = 'some keyword';
    $hash = Service::metadataHash($keyword);
    $keywordId = $keywordStorage->retrieveByHash($hash, 'keyword');
    $keywordMetadata = json_decode($keywordStorage->retrieve($keywordId));
    $this->assertEquals($keyword, $keywordMetadata->data);
  }

  private function datasetPostTwoAndUnpublishOne() {
    /** @var Metastore $metastore */
    $metastore = \Drupal::service('dkan.metastore.service');
    $datasetRootedJsonData = $this->getData("123", 'Test Published', []);
    $dataset = json_decode($datasetRootedJsonData);

    $uuid_123 = $metastore->post('dataset', $datasetRootedJsonData);
    $metastore->publish('dataset', $uuid_123);

    $this->assertEquals(
      $dataset->identifier,
      $uuid_123
    );

    $datasetRootedJsonData = $metastore->get('dataset', $uuid_123);
    $this->assertIsString("$datasetRootedJsonData");

    $retrievedDataset = json_decode($datasetRootedJsonData);

    $this->assertEquals(
      $retrievedDataset->identifier,
      $uuid_123
    );

    $datasetRootedJsonData = $this->getData("456", 'Test Unpublished', []);
    $uuid_456 = $metastore->post('dataset', $datasetRootedJsonData);
    $this->assertNotEquals($uuid_123, $uuid_456);
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

  private function getStorage($schemaId) {
    return \Drupal::service('dkan.metastore.storage')->getInstance($schemaId);
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
