<?php

namespace Drupal\Tests\datastore\Functional\Controller;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\common\Traits\GetDataTrait;
use Drupal\metastore\DataDictionary\DataDictionaryDiscovery;
use RootedData\RootedJsonData;

/**
 * @group dkan
 * @group datastore
 * @group functional
 * @group btb
 */
class QueryDownloadControllerTest extends BrowserTestBase {

  use GetDataTrait;

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
  protected const RESOURCE_FILE = 'data-dict.csv';


  protected static $modules = [
    'datastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

  /**
   * Test application of data dictionary schema to CSV generated for download.
   */
  public function testDownloadWithDataDictionary() {
    // Set per-reference data-dictinary in metastore config.
    $this->config('metastore.settings')
      ->set('data_dictionary_mode', DataDictionaryDiscovery::MODE_REFERENCE)
      ->save();
    $this->assertEquals(
      DataDictionaryDiscovery::MODE_REFERENCE,
      $this->config('metastore.settings')->get('data_dictionary_mode')
    );

    // Dependencies.
    $uuid = $this->container->get('uuid');
    /** @var \Drupal\metastore\ValidMetadataFactory $validMetadataFactory */
    $validMetadataFactory = $this->container->get('dkan.metastore.valid_metadata');
    /** @var \Drupal\metastore\MetastoreService $metastore */
    $metastore = $this->container->get('dkan.metastore.service');
    // Copy resource file to uploads directory.
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = $this->container->get('file_system');
    $upload_path = $file_system->realpath(self::UPLOAD_LOCATION);
    $file_system->prepareDirectory($upload_path, FileSystemInterface::CREATE_DIRECTORY);
    $file_system->copy(self::TEST_DATA_PATH . self::RESOURCE_FILE, $upload_path, FileSystemInterface::EXISTS_REPLACE);
    $resourceUrl = $this->container->get('stream_wrapper_manager')
      ->getViaUri(self::UPLOAD_LOCATION . self::RESOURCE_FILE)
      ->getExternalUrl();

    // Build data-dictionary.
    $dict_id = $uuid->generate();
    $date_format = '%m/%d/%Y';
    $fields = [
      [
        'name' => 'b',
        'title' => 'b',
        'type' => 'date',
        'format' => $date_format,
      ],
    ];
    $data_dict = $validMetadataFactory->get(
      $this->getDataDictionary($fields, [], $dict_id),
      'data-dictionary'
    );
    // Create data-dictionary.
    $this->assertEquals(
      $dict_id,
      $metastore->post('data-dictionary', $data_dict)
    );
    // Publish should return FALSE, because the node was already published.
    $this->assertFalse($metastore->publish('data-dictionary', $dict_id));
    $this->assertEquals(
      $date_format,
      $metastore->get('data-dictionary', $dict_id)->{'$.data.fields[0].format'}
    );

    // Build dataset.
    $dataset_id = $uuid->generate();
    $this->assertInstanceOf(
      RootedJsonData::class,
      $dataset = $validMetadataFactory->get(
        $this->getDataset(
          $dataset_id,
          'Test ' . $dataset_id,
          [$resourceUrl],
          TRUE,
          'dkan://metastore/schemas/data-dictionary/items/' . $dict_id
        ),
        'dataset'
      )
    );
    // Create dataset.
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
    // The dataset references the dictionary.
    $this->assertStringContainsString(
      $dict_id,
      $dataset->{'$["%Ref:distribution"][0].data.describedBy'}
    );
    // Get the distribution ID.
    $distribution_id = $dataset->{'$["%Ref:distribution"][0].identifier'};

    // Dictionary fields are applied to the dataset.
    /** @var \Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer $dictionary_enforcer */
    $dictionary_enforcer = $this->container->get('dkan.datastore.service.resource_processor.dictionary_enforcer');
    $this->assertCount(
      1,
      $dictionary_fields = $dictionary_enforcer->returnDataDictionaryFields($distribution_id)
    );
    $this->assertEquals($date_format, $dictionary_fields[0]['format'] ?? 'not found');

    // Run queue items to perform the import.
    $this->runQueues(['localize_import', 'datastore_import', 'post_import']);

    // Query for the dataset, as a streaming CSV.
    $client = $this->getHttpClient();
    $response = $client->request(
      'GET',
      $this->baseUrl . '/api/1/datastore/query/' . $dataset_id . '/0/download',
      ['query' => ['format' => 'csv']]
    );

    $lines = explode("\n", $response->getBody()->getContents());
    $this->assertEquals('1,02/23/1978,9.07,efghijk,0', $lines[1]);
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
