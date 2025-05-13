<?php

namespace Drupal\Tests\datastore\Functional;

use Drupal\common\DataResource;
use Drupal\common\FileFetcher\FileFetcherRemoteUseExisting;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\Tests\BrowserTestBase;
use FileFetcher\FileFetcher;
use FileFetcher\Processor\Remote;
use Procrastinator\Result;
use RootedData\RootedJsonData;

/**
 * Test dataset import when using existing localized files.
 *
 * @group datastore
 * @group btb
 * @group functional
 * @group functional2
 */
class ImportLocalCopyOfRemoteFileTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'datastore',
    'metastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

  protected const SOURCE_URL = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';

  public function test() {
    // Explicitly turn off always_use_existing_local_perspective for now.
    $this->config('common.settings')
      ->set('always_use_existing_local_perspective', FALSE)
      ->save();
    $this->assertFalse(
      $this->config('common.settings')->get('always_use_existing_local_perspective')
    );

    $identifier = uniqid();

    // Get mapping entity storage.
    /** @var \Drupal\Core\Entity\EntityStorageInterface $mapping_entity_storage */
    $mapping_entity_storage = $this->container
      ->get('entity_type.manager')
      ->getStorage('resource_mapping');

    // There should be no mapper records.
    $this->assertEquals(0, $this->getEntityCount($mapping_entity_storage));

    // Post our dataset.
    /** @var \Drupal\metastore\MetastoreService $metastore_service */
    $metastore_service = $this->container->get('dkan.metastore.service');
    $this->assertEquals(
      $identifier,
      $metastore_service->post(
        'dataset',
        $this->getData($identifier, 'Test Title', [self::SOURCE_URL])
      )
    );

    // 1 mapping after posting the datastore.
    $this->assertEquals(1, $this->getEntityCount($mapping_entity_storage));

    // Get our resource info from the dataset info service.
    /** @var \Drupal\common\DatasetInfo $dataset_info_service */
    $dataset_info_service = $this->container->get('dkan.common.dataset_info');
    $info = $dataset_info_service->gather($identifier);

    // Having gotten the info, there should still only be 1 record.
    $this->assertEquals(1, $this->getEntityCount($mapping_entity_storage));

    // Let's interrogate it. It should be a source mapping.
    $all_mapping_ids = $this->getAllEntityIds($mapping_entity_storage);
    /** @var \Drupal\Core\Entity\EntityInterface $mapping */
    $mapping = $mapping_entity_storage->load(reset($all_mapping_ids));
    $this->assertEquals(
      DataResource::DEFAULT_SOURCE_PERSPECTIVE,
      $mapping->get('perspective')->getString()
    );

    // Now let's ask the mapper instead of its storage.
    /** @var \Drupal\metastore\ResourceMapper $resource_mapper */
    $resource_mapper = $this->container->get('dkan.metastore.resource_mapper');
    $this->assertNotNull(
      $resource_id = $info['latest_revision']['distributions'][0]['resource_id'] ?? NULL
    );
    $this->assertInstanceOf(
      DataResource::class,
      $source_resource = $resource_mapper->get($resource_id)
    );
    // Getting the source resource should not change the record count.
    $this->assertEquals(1, $this->getEntityCount($mapping_entity_storage));
    // No local file perspective yet.
    $this->assertNull($resource_mapper->get(
      $resource_id,
      ResourceLocalizer::LOCAL_FILE_PERSPECTIVE
    ));

    // Interrogate the file fetcher. We want to test which processor class it
    // uses.
    /** @var \Drupal\datastore\Service\ResourceLocalizer $resource_localizer */
    $resource_localizer = $this->container->get('dkan.datastore.service.resource_localizer');
    $file_fetcher = $resource_localizer->getFileFetcher($source_resource);
    $ref_get_processor = new \ReflectionMethod($file_fetcher, 'getProcessor');
    $ref_get_processor->setAccessible(TRUE);
    // Should be Remote.
    $this->assertInstanceOf(
      Remote::class,
      $ref_get_processor->invoke($file_fetcher)
    );
    // Result should be 'waiting,' which is the default value if there is no
    // actual result object.
    $some_info = $dataset_info_service->gather($identifier);
    $this->assertEquals(
      Result::WAITING,
      $some_info['latest_revision']['distributions'][0]['fetcher_status'] ?? NULL
    );

    // Turn on always_use_existing_local_perspective.
    $this->config('common.settings')
      ->set('always_use_existing_local_perspective', TRUE)
      ->save();
    $this->assertTrue(
      $this->config('common.settings')->get('always_use_existing_local_perspective')
    );

    // We should get our FileFetcherRemoteUseExisting when we get another
    // file fetcher.
    $file_fetcher = $resource_localizer->getFileFetcher($source_resource);
    $this->assertInstanceOf(FileFetcher::class, $file_fetcher);
    $this->assertInstanceOf(
      FileFetcherRemoteUseExisting::class,
      $ref_get_processor->invoke($file_fetcher)
    );

    // Turn off always_use_existing_local_perspective and get the file fetcher
    // again. It should be Remote again.
    $this->config('common.settings')
      ->set('always_use_existing_local_perspective', FALSE)
      ->save();
    $file_fetcher = $resource_localizer->getFileFetcher($source_resource);
    $this->assertInstanceOf(
      Remote::class,
      $ref_get_processor->invoke($file_fetcher)
    );

    // Perform the localization.
    $this->assertInstanceOf(
      Result::class,
      $result = $resource_localizer->localizeTask(
        $source_resource->getIdentifier(), $source_resource->getVersion()
      )
    );
    $this->assertEquals(
      Result::DONE, $result->getStatus()
    );

    // Now there should be three mappings.
    $this->assertEquals(3, $this->getEntityCount($mapping_entity_storage));
    // Get the info again after localization.
    $localized_info = $dataset_info_service->gather($identifier);
    $this->assertNotEquals($localized_info, $info);
    $this->assertEquals(
      Result::DONE,
      $localized_info['latest_revision']['distributions'][0]['fetcher_status'] ?? NULL
    );
  }

  /**
   * Count the number of entities present for the given storage.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage object.
   *
   * @return int
   *   The count of arrays present in the storage.
   */
  protected function getEntityCount(EntityStorageInterface $storage) {
    return $storage->getQuery()
      ->count()
      ->accessCheck(FALSE)
      ->execute();
  }

  /**
   * Get all the IDs available for the given entity storage.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage object.
   *
   * @return array
   *   Array of entity IDs for all the entities in the storage.
   */
  protected function getAllEntityIds(EntityStorageInterface $storage) {
    return $storage->getQuery()
      ->accessCheck(FALSE)
      ->execute();
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
   *
   * @see \Drupal\Tests\dkan\Functional\DatasetBTBTest::getData()
   */
  private function getData(string $identifier, string $title, array $downloadUrls): RootedJsonData {
    $data = new \stdClass();
    $data->title = $title;
    $data->description = 'This & that description. <a onauxclick=prompt(document.domain)>Right click me</a>.';
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
      $distribution->downloadURL = $downloadUrl;
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
    // so we can have a useful test fail message.
    /** @var \Drupal\metastore\ValidMetadataFactory $valid_metadata_factory */
    $valid_metadata_factory = $this->container->get('dkan.metastore.valid_metadata');
    return $valid_metadata_factory->get(json_encode($data), 'dataset');
  }

}
