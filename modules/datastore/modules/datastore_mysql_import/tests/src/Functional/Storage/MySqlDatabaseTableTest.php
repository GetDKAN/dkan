<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore_mysql_import\Functional\Storage;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\common\Traits\GetDataTrait;
use Drupal\Tests\common\Traits\QueueRunnerTrait;
use Drupal\datastore\Controller\ImportController;
use RootedData\RootedJsonData;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group dkan
 * @group datastore_mysql_import
 * @group functional
 */
class MySqlDatabaseTableTest extends BrowserTestBase {

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

  protected static $modules = [
    'datastore_mysql_import',
    'node',
  ];

  protected $defaultTheme = 'stark';

  public function testImport() {
    // Dependencies.
    $resourceFile = 'wide_table.csv';
    $uuid = $this->container->get('uuid');
    /** @var \Drupal\metastore\ValidMetadataFactory $validMetadataFactory */
    $validMetadataFactory = $this->container->get('dkan.metastore.valid_metadata');
    /** @var \Drupal\metastore\MetastoreService $metastore */
    $metastore = $this->container->get('dkan.metastore.service');
    $resourceUrl = $this->setUpResourceFile($resourceFile);
    $importController = ImportController::create(\Drupal::getContainer());

    // Create the data.
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
    // Create dataset node.
    $this->assertEquals(
      $dataset_id,
      $metastore->post('dataset', $dataset)
    );
    // Publish should return FALSE, because the node was already published.
    $this->assertFalse($metastore->publish('dataset', $dataset_id));

    // Retrieve dataset.
    $this->assertInstanceOf(
      RootedJsonData::class,
      $dataset = $metastore->get('dataset', $dataset_id)
    );
    // Get the distribution ID.
    $this->assertNotEmpty(
      $distribution_id = $dataset->{'$["%Ref:distribution"][0].identifier'} ?? NULL
    );

    // Run queue items to perform the import.
    $this->runQueues(['localize_import', 'datastore_import', 'post_import']);

    // Retrieve schema for dataset resource.
    $response = $importController->summary(
      $distribution_id,
      Request::create('https://example.com')
    );
    $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    $result = json_decode($response->getContent(), TRUE);

    // 252 + record_number.
    $this->assertCount(
      253,
      $columns = $result['columns']
    );
    // Column names will be truncated.
    $this->assertArrayNotHasKey('column_heading_that_grossly_exceeds_the_sixty_four_character_limit_1', $columns);
    $this->assertArrayNotHasKey('column_heading_that_grossly_exceeds_the_sixty_four_character_limit_252', $columns);
    // Since we should always have the same hash in the name, we can assert the
    // array key here.
    $this->assertEquals(
      'column_heading_that_grossly_exceeds_the_sixty_four_character_limit_252',
      $columns['column_heading_that_grossly_exceeds_the_sixty_four_characte_7431']['description'] ?? NULL
    );
    $this->assertEquals(
      'text',
      $columns['column_heading_that_grossly_exceeds_the_sixty_four_characte_7431']['type'] ?? NULL
    );
  }

  /**
   * Move a data test file to the public:// directory.
   *
   * @param string $resourceFile
   *   The file name only of the data resource file.
   *
   * @return string
   *   The resource URL of the moved resource.
   *
   * @todo Turn this into a trait or add it to a base class.
   */
  protected function setUpResourceFile(string $resourceFile) : string {
    // Copy resource file to uploads directory.
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = $this->container->get('file_system');
    $upload_path = $file_system->realpath(self::UPLOAD_LOCATION);
    $file_system->prepareDirectory($upload_path, FileSystemInterface::CREATE_DIRECTORY);
    $file_system->copy(self::TEST_DATA_PATH . $resourceFile, $upload_path, FileSystemInterface::EXISTS_REPLACE);
    return $this->container->get('stream_wrapper_manager')
      ->getViaUri(self::UPLOAD_LOCATION . $resourceFile)
      ->getExternalUrl();
  }

}
