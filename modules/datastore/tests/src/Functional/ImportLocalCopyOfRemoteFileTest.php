<?php

namespace Drupal\Tests\datastore\Functional;

use Drupal\common\DataResource;
use Drupal\common\FileFetcher\FileFetcherRemoteUseExisting;
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
    $this->markTestIncomplete('convert mapping store counts to use entity api');
    // Explicitly turn off always_use_existing_local_perspective for now.
    $this->config('common.settings')
      ->set('always_use_existing_local_perspective', FALSE)
      ->save();
    $this->assertFalse(
      $this->config('common.settings')->get('always_use_existing_local_perspective')
    );

    $identifier = uniqid();

    // There should be no mapper records.
    /** @var \Drupal\metastore\ResourceMapper $resource_mapper */
    $resource_mapper = $this->container->get('dkan.metastore.resource_mapper');
    $mapping_store = $resource_mapper->getStore();
    $this->assertEquals(0, $mapping_store->count());

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
    $this->assertEquals(1, $mapping_store->count());

    // Get our resource info from the dataset info service.
    /** @var \Drupal\common\DatasetInfo $dataset_info_service */
    $dataset_info_service = $this->container->get('dkan.common.dataset_info');
    $info = $dataset_info_service->gather($identifier);

    // Having gotten the info, there should still only be 1 record.
    $this->assertEquals(1, $mapping_store->count());

    // Let's interrogate it. It should be a source mapping.
    $all_mappings = $mapping_store->retrieveAll();
    $mapping = $mapping_store->retrieve(reset($all_mappings));
    $this->assertEquals(
      DataResource::DEFAULT_SOURCE_PERSPECTIVE,
      $mapping->perspective
    );

    // Now let's ask the mapper instead of its storage.
    $this->assertNotNull(
      $resource_id = $info['latest_revision']['distributions'][0]['resource_id'] ?? NULL
    );
    $this->assertInstanceOf(
      DataResource::class,
      $source_resource = $resource_mapper->get($resource_id)
    );
    // Getting the source resource should not change the record count.
    $this->assertEquals(1, $mapping_store->count());
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
    /** @var \Drupal\datastore\DatastoreService $datastore_service */
    $datastore_service = $this->container->get('dkan.datastore.service');
    // In order to perform the localization without importing, we have to call
    // DatastoreService::getResource(), which is private.
    $ref_get_resource = new \ReflectionMethod($datastore_service, 'getResource');
    $ref_get_resource->setAccessible(TRUE);
    $results = $ref_get_resource->invokeArgs($datastore_service, [
      $source_resource->getIdentifier(),
      $source_resource->getVersion(),
    ]);
    // First result should be a resource, second result should be result objects
    // keyed by the localizer label.
    $this->assertInstanceOf(DataResource::class, $results[0]);
    $this->assertEquals(
      Result::DONE,
      $results[1]['ResourceLocalizer']->getStatus()
    );

    // Now there should be three mappings.
    $this->assertEquals(3, $mapping_store->count());
    // Get the info again after localization.
    $localized_info = $dataset_info_service->gather($identifier);
    $this->assertNotEquals($localized_info, $info);
    $this->assertEquals(
      Result::DONE,
      $localized_info['latest_revision']['distributions'][0]['fetcher_status'] ?? NULL
    );
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
