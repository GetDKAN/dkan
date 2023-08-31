<?php

namespace Drupal\Tests\datastore\Functional;

use Drupal\Component\Uuid\Php;
use Drupal\Core\File\FileSystemInterface;
use Drupal\datastore\Controller\ImportController;
use Drupal\metastore\DataDictionary\DataDictionaryDiscovery;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\Storage\NodeData;
use Drupal\metastore\ValidMetadataFactory;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\common\Traits\GetDataTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * DictionaryEnforcer QueueWorker test.
 *
 * @package Drupal\Tests\datastore\Functional
 * @group datastore
 */
class DictionaryEnforcerTest extends BrowserTestBase {

  use GetDataTrait;

  protected static $modules = [
    'datastore',
    'metastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

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
   * Cron service.
   *
   * @var \Drupal\Core\Cron
   */
  protected $cron;

  /**
   * Node data storage.
   *
   * @var \Drupal\metastore\Storage\NodeData
   */
  protected NodeData $datasetStorage;

  /**
   * Metastore service.
   *
   * @var \Drupal\metastore\MetastoreService
   */
  protected MetastoreService $metastore;

  /**
   * Uuid service.
   *
   * @var \Drupal\Component\Uuid\Php
   */
  protected Php $uuid;

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory
   */
  protected ValidMetadataFactory $validMetadataFactory;

  /**
   * Import controller.
   *
   * @var \Drupal\datastore\Controller\ImportController
   */
  protected ImportController $webServiceApi;

  /**
   * External URL for the fixture CSV file.
   *
   * @var string
   */
  protected string $resourceUrl;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Initialize services.
    $this->cron = $this->container->get('cron');
    $this->metastore = $this->container->get('dkan.metastore.service');
    $this->uuid = $this->container->get('uuid');
    $this->validMetadataFactory = $this->container->get('dkan.metastore.valid_metadata');
    $this->webServiceApi = ImportController::create($this->container);
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
    $this->metastore->post('data-dictionary', $data_dict);
    $this->metastore->publish('data-dictionary', $dict_id);

    // Set global data-dictionary in metastore config.
    $this->config('metastore.settings')
      ->set('data_dictionary_mode', DataDictionaryDiscovery::MODE_SITEWIDE)
      ->set('data_dictionary_sitewide', $dict_id)
      ->save();

    // Build dataset.
    $dataset_id = $this->uuid->generate();
    $dataset = $this->validMetadataFactory->get($this->getDataset($dataset_id, 'Test ' . $dataset_id, [$this->resourceUrl], TRUE), 'dataset');
    // Create dataset.
    $this->metastore->post('dataset', $dataset);
    $this->metastore->publish('dataset', $dataset_id);

    // Run cron to import dataset into datastore.
    $this->cron->run();
    // Run cron to apply data-dictionary.
    $this->cron->run();

    // Retrieve dataset distribution ID.
    $dataset = $this->metastore->get('dataset', $dataset_id);
    $dist_id = $dataset->{'$["%Ref:distribution"][0].identifier'};
    // Build mock request.
    $request = Request::create('http://blah/api');
    // Retrieve schema for dataset resource.
    $response = $this->webServiceApi->summary($dist_id, $request);
    $result = json_decode($response->getContent(), TRUE);

    // Clean up after ourselves, before performing the assertion.
    $this->metastore->delete('dataset', $dataset_id);

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

}
