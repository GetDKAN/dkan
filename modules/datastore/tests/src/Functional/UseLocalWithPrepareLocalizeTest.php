<?php

namespace Drupal\Tests\datastore\Functional;

use Drupal\common\DataResource;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\Tests\BrowserTestBase;
use RootedData\RootedJsonData;

/**
 * Test adding and localizing a datastore.
 *
 * @group datastore
 * @group btb
 * @group functional
 */
class UseLocalWithPrepareLocalizeTest extends BrowserTestBase {

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
    $this->markTestIncomplete('very imcomplete');
    $identifier = 'id_1';

    /** @var \Drupal\metastore\MetastoreService $metastore_service */
    $metastore_service = $this->container->get('dkan.metastore.service');

    $rooted = $this->getData($identifier, '1.1', [self::SOURCE_URL]);
    $dataset_identifier = $metastore_service->post('dataset', $rooted);

//    $this->assertEquals('foo', print_r($rooted, TRUE));

//    $source_resource = new DataResource(
//      self::SOURCE_URL,
//      'text/csv',
//      DataResource::DEFAULT_SOURCE_PERSPECTIVE
//    );
    // Add our source data resource to the mapper.
    /** @var \Drupal\metastore\ResourceMapper $resource_mapper */
    $resource_mapper = $this->container->get('dkan.metastore.resource_mapper');
    $source_resource = $resource_mapper->get($identifier);
    $this->assertInstanceOf(DataResource::class, $source_resource);

    // Set always_use_existing_local_perspective to true.
    $this->config('common.settings')
      ->set('always_use_existing_local_perspective', TRUE)
      ->save();
    $this->assertTrue(
      $this->config('common.settings')->get('always_use_existing_local_perspective')
    );

    // Run prepare-localized, emulating the Drush command.
    $identifier = $source_resource->getIdentifier();
    /** @var \Drupal\datastore\Service\ResourceLocalizer $resource_localizer */
    $resource_localizer = $this->container->get('dkan.datastore.service.resource_localizer');
    $info = $resource_localizer->prepareLocalized($identifier);
    $this->assertArrayHasKey('file', $info);

    // 'Pre-download' the file. We'll just write an arbitrary file here.
    $preexisting_file_contents = '1234,5678';
    file_put_contents($info['file'], $preexisting_file_contents);
    $this->assertStringEqualsFile($info['file'], $preexisting_file_contents);

    // Run localize_import queue item.
    /** @var \Drupal\Core\Queue\QueueWorkerManager $queue_worker_manager */
    $queue_worker_manager = \Drupal::service('plugin.manager.queue_worker');
    $worker = $queue_worker_manager->createInstance('localize_import');
    $worker->processItem([
      'identifier' => $source_resource->getIdentifier(),
      'version' => $source_resource->getVersion(),
    ]);

    // Assert that the file wasn't changed.
    $this->assertStringEqualsFile($info['file'], $preexisting_file_contents);

    // Get the resource again.
    $localized_resource = $resource_mapper->get(
      $source_resource->getIdentifier(),
      ResourceLocalizer::LOCAL_FILE_PERSPECTIVE,
      $source_resource->getVersion()
    );
    // Is the final file the same as the derived one from prepare-localize?
    /** @var \Drupal\Core\File\FileSystem $file_system */
    $file_system = $this->container->get('file_system');
    $localize_file = $file_system->realpath($localized_resource->getFilePath());
    $this->assertEquals($info['file'], $localize_file);
    $this->assertStringEqualsFile($localize_file, $preexisting_file_contents);
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
