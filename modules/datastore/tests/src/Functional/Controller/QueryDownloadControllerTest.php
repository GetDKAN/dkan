<?php

namespace Drupal\Tests\datastore\Functional\Controller;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\common\Traits\GetDataTrait;
use Drupal\Tests\common\Traits\QueueRunnerTrait;
use RootedData\RootedJsonData;

/**
 * @coversDefaultClass \Drupal\datastore\Controller\QueryDownloadController
 *
 * @group dkan
 * @group datastore
 * @group functional
 * @group btb
 */
class QueryDownloadControllerTest extends BrowserTestBase {

  use GetDataTrait, QueueRunnerTrait;

  /**
   * Uploaded resource file destination.
   *
   * @var string
   */
  protected const UPLOAD_LOCATION = 'public://uploaded_resources/';

  /**
   * Test data file path.
   *
   * @var string
   */
  protected const TEST_DATA_PATH = __DIR__ . '/../../../data/';

  /**
   * Resource file name.
   *
   * @var string
   */
  protected const RESOURCE_FILE = 'longcolumn.csv';


  protected static $modules = [
    'datastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

  /**
   * Test application of data dictionary schema to CSV generated for download.
   */
  public function testDownloadWithMachineName() {
    // Dependencies.
    $uuid = $this->container->get('uuid');
    /** @var \Drupal\metastore\ValidMetadataFactory $validMetadataFactory */
    $validMetadataFactory = $this->container->get('dkan.metastore.valid_metadata');
    /** @var \Drupal\metastore\MetastoreService $metastoreService */
    $metastoreService = $this->container->get('dkan.metastore.service');
    // Copy resource file to uploads directory.
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = $this->container->get('file_system');
    $upload_path = $file_system->realpath(self::UPLOAD_LOCATION);
    $file_system->prepareDirectory($upload_path, FileSystemInterface::CREATE_DIRECTORY);
    $file_system->copy(self::TEST_DATA_PATH . self::RESOURCE_FILE, $upload_path, FileSystemInterface::EXISTS_REPLACE);
    $resourceUrl = $this->container->get('stream_wrapper_manager')
      ->getViaUri(self::UPLOAD_LOCATION . self::RESOURCE_FILE)
      ->getExternalUrl();

    // Set up dataset.
    $dataset_id = $uuid->generate();
    $this->assertInstanceOf(
      RootedJsonData::class,
      $dataset = $validMetadataFactory->get(
        $this->getDataset(
          $dataset_id,
          'Test ' . $dataset_id,
          [$resourceUrl],
          TRUE
        ),
        'dataset'
      )
    );
    // Create dataset.
    $this->assertEquals(
      $dataset_id,
      $metastoreService->post('dataset', $dataset)
    );
    // Publish should return FALSE, because the node was already published.
    $this->assertFalse($metastoreService->publish('dataset', $dataset_id));

    // Retrieve dataset.
    $this->assertInstanceOf(
      RootedJsonData::class,
      $dataset = $metastoreService->get('dataset', $dataset_id)
    );

    // Run queue items to perform the import.
    $this->runQueues(['localize_import', 'datastore_import', 'post_import']);

    // Explicitly configure for the CSV's headers.
    $this->config('metastore.settings')
      ->set('csv_headers_mode', 'resource_headers')
      ->save();

    // Query for the dataset, as a streaming CSV.
    $client = $this->getHttpClient();
    $response = $client->request(
      'GET',
      $this->baseUrl . '/api/1/datastore/query/' . $dataset_id . '/0/download',
      ['query' => ['format' => 'csv']]
    );

    $lines = explode("\n", $response->getBody()->getContents());
    $this->assertEquals(
      'id,name,extra_long_column_name_with_tons_of_characters_that_will_need_to_be_truncated_in_order_to_work,extra_long_column_name_with_tons_of_characters_that_will_need_to_be_truncated_in_order_to_work2',
      $lines[0]
    );

    // Re-request, but with machine name headers.
    $this->config('metastore.settings')
      ->set('csv_headers_mode', 'machine_names')
      ->save();

    $client = $this->getHttpClient();
    $response = $client->request(
      'GET',
      $this->baseUrl . '/api/1/datastore/query/' . $dataset_id . '/0/download',
      ['query' => ['format' => 'csv']]
    );

    $lines = explode("\n", $response->getBody()->getContents());
    // Truncated headers from the datastore.
    $this->assertEquals(
      'id,name,extra_long_column_name_with_tons_of_characters_that_will_ne_e872,extra_long_column_name_with_tons_of_characters_that_will_ne_5127',
      $lines[0]
    );
  }

}
