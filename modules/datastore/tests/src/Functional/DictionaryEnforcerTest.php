<?php

namespace Drupal\Tests\datastore\Functional;

use Drupal\Core\File\FileSystemInterface;

use Drupal\datastore\Controller\ImportController;
use Drupal\metastore\DataDictionary\DataDictionaryDiscovery;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\common\Traits\CleanUp;
use Drupal\Tests\common\Traits\GetDataTrait;
use Drupal\Tests\metastore\Unit\MetastoreServiceTest;

use RootedData\RootedJsonData;
use Symfony\Component\HttpFoundation\Request;

/**
 * DictionaryEnforcer QueueWorker test.
 *
 * @package Drupal\Tests\datastore\Functional
 * @group datastore
 * @group functional
 * @group btb
 */
class DictionaryEnforcerTest extends BrowserTestBase {

  use GetDataTrait, CleanUp;

  protected $defaultTheme = 'stark';

  protected static $modules = [
    'datastore',
    'node',
  ];

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
  protected const TEST_DATA_PATH = __DIR__ . '/../../data/';

  /**
   * Resource file name.
   *
   * @var string
   */
  protected const RESOURCE_FILE = 'data-dict.csv';

  /**
   * Node data storage.
   *
   * @var \Drupal\metastore\Storage\NodeData
   */
  protected $datasetStorage;

  /**
   * Metastore service.
   *
   * @var \Drupal\metastore\MetastoreService
   */
  protected $metastore;

  /**
   * Uuid service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuid;

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  /**
   * Import controller.
   *
   * @var \Drupal\datastore\Controller\ImportController
   */
  protected $importController;

  /**
   * External URL for the fixture CSV file.
   *
   * @var string
   */
  protected $resourceUrl;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Initialize services.
    $this->metastore = $this->container->get('dkan.metastore.service');
    $this->uuid = $this->container->get('uuid');
    $this->validMetadataFactory = MetastoreServiceTest::getValidMetadataFactory($this);
    $this->importController = ImportController::create(\Drupal::getContainer());
    $this->datasetStorage = $this->container->get('dkan.metastore.storage')
      ->getInstance('dataset');
    // Copy resource file to uploads directory.
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = $this->container->get('file_system');
    $upload_path = $file_system->realpath(self::UPLOAD_LOCATION);
    $file_system->prepareDirectory($upload_path, FileSystemInterface::CREATE_DIRECTORY);
    $file_system->copy(self::TEST_DATA_PATH . self::RESOURCE_FILE, $upload_path, FileSystemInterface::EXISTS_REPLACE);
    // Create resource URL.
    $this->resourceUrl = $this->container->get('stream_wrapper_manager')
      ->getViaUri(self::UPLOAD_LOCATION . self::RESOURCE_FILE)
      ->getExternalUrl();
  }

  /**
   * Test dictionary enforcement.
   */
  public function testDictionaryEnforcement(): void {
    // Build data-dictionary.
    $dict_id = $this->uuid->generate();
    $fields = [
      [
        'name' => 'a',
        'type' => 'integer',
        'format' => 'default',
      ],
      [
        'name' => 'b',
        'title' => 'B',
        'type' => 'date',
        'format' => '%m/%d/%Y',
      ],
      [
        'name' => 'c',
        'title' => 'C',
        'type' => 'number',
      ],
      [
        'name' => 'd',
        'title' => 'D',
        'type' => 'string',
      ],
      [
        'name' => 'e',
        'title' => 'E',
        'type' => 'boolean',
      ],
    ];
    $indexes = [
      [
        'name' => 'index_a',
        'fields' => [
          ['name' => 'a'],
          ['name' => 'd', 'length' => 6],
        ],
        'type' => 'index',
      ],
      [
        'name' => 'fulltext_index_a',
        'fields' => [
          ['name' => 'd', 'length' => 3],
        ],
        'type' => 'fulltext',
      ],
    ];
    $data_dict = $this->validMetadataFactory->get($this->getDataDictionary($fields, $indexes, $dict_id), 'data-dictionary');
    // Create data-dictionary.
    $this->assertEquals(
      $dict_id,
      $this->metastore->post('data-dictionary', $data_dict)
    );
    // Publish should return FALSE, because the node was already published.
    $this->assertFalse($this->metastore->publish('data-dictionary', $dict_id));

    // Set global data-dictinary in metastore config.
    $metastore_config = $this->config('metastore.settings');
    $metastore_config->set('data_dictionary_mode', DataDictionaryDiscovery::MODE_SITEWIDE)
      ->set('data_dictionary_sitewide', $dict_id)
      ->save();

    // Build dataset.
    $dataset_id = $this->uuid->generate();
    $this->assertInstanceOf(
      RootedJsonData::class,
      $dataset = $this->validMetadataFactory->get(
        $this->getDataset($dataset_id, 'Test ' . $dataset_id, [$this->resourceUrl], TRUE),
        'dataset'
      )
    );
    // Create dataset.
    $this->assertEquals($dataset_id, $this->metastore->post('dataset', $dataset));
    // Publish should return FALSE, because the node was already published.
    $this->assertFalse($this->metastore->publish('dataset', $dataset_id));

    // Run queue items to perform the import.
    $this->runQueues(['localize_import', 'datastore_import', 'post_import']);

    // Retrieve dataset distribution ID.
    $this->assertInstanceOf(
      RootedJsonData::class,
      $dataset = $this->metastore->get('dataset', $dataset_id)
    );
    $this->assertNotEmpty(
      $dist_id = $dataset->{'$["%Ref:distribution"][0].identifier'} ?? NULL
    );
    // Retrieve schema for dataset resource.
    $response = $this->importController->summary(
      $dist_id,
      Request::create('http://blah/api')
    );
    $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    $result = json_decode($response->getContent(), TRUE);

    // Validate schema.
    $this->assertEquals([
      'numOfColumns' => 6,
      'columns' => [
        'record_number' => [
          'type' => 'serial',
          'length' => 10,
          'unsigned' => TRUE,
          'not null' => TRUE,
          'mysql_type' => 'int',
        ],
        'a' => [
          'type' => 'int',
          'length' => 11,
          'mysql_type' => 'int',
        ],
        'b' => [
          'type' => 'varchar',
          'mysql_type' => 'date',
          'description' => 'B',
        ],
        'c' => [
          'type' => 'numeric',
          'length' => 3,
          'mysql_type' => 'decimal',
          'description' => 'C',
        ],
        'd' => [
          'type' => 'text',
          'mysql_type' => 'text',
          'description' => 'D',
        ],
        'e' => [
          'type' => 'int',
          'mysql_type' => 'tinyint',
          'description' => 'E',
          'length' => 1,
          'size' => 'tiny',
        ],
      ],
      'indexes' => [
        'index_a' => [
          'a',
          'd',
        ],
      ],
      'fulltextIndexes' => [
        'fulltext_index_a' => [
          'd',
        ],
      ],
      'numOfRows' => 3,
    ], $result);
  }

  /**
   * Process queues in a predictable order.
   */
  private function runQueues(array $relevantQueues = []) {
    /** @var \Drupal\Core\Queue\QueueWorkerManager $queueWorkerManager */
    $queueWorkerManager = \Drupal::service('plugin.manager.queue_worker');
    /** @var \Drupal\Core\Queue\QueueFactory $queueFactory */
    $queueFactory = $this->container->get('queue');
    foreach ($relevantQueues as $queueName) {
      $worker = $queueWorkerManager->createInstance($queueName);
      $queue = $queueFactory->get($queueName);
      while ($item = $queue->claimItem()) {
        $worker->processItem($item->data);
        $queue->deleteItem($item);
      }
    }
  }

}
