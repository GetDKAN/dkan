<?php

namespace Drupal\Tests\datastore\Functional\Controller;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\common\Traits\GetDataTrait;
use Drupal\Tests\metastore\Unit\MetastoreServiceTest;
use Drupal\metastore\DataDictionary\DataDictionaryDiscovery;
use RootedData\RootedJsonData;
use Symfony\Component\HttpFoundation\Request;

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
  protected const TEST_DATA_PATH = __DIR__ . '/../../data/';

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
    // Dependencies.
    $uuid = $this->container->get('uuid');
    $validMetadataFactory = MetastoreServiceTest::getValidMetadataFactory($this);
    $metastore = $this->container->get('dkan.metastore.service');
    $resourceUrl = $this->container->get('stream_wrapper_manager')
      ->getViaUri(self::UPLOAD_LOCATION . self::RESOURCE_FILE)
      ->getExternalUrl();

    // Set per-reference data-dictinary in metastore config.
    $this->config('metastore.settings')
      ->set('data_dictionary_mode', DataDictionaryDiscovery::MODE_REFERENCE)
      ->save();

    // Build data-dictionary.
    $dict_id = $uuid->generate();
    $fields = [
      [
        'name' => 'the_date',
        'title' => 'The Date',
        'type' => 'date',
        'format' => '%m/%d/%Y',
      ],
    ];
    $data_dict = $validMetadataFactory->get($this->getDataDictionary($fields, [], $dict_id), 'data-dictionary');
    // Create data-dictionary.
    $this->assertEquals(
      $dict_id,
      $metastore->post('data-dictionary', $data_dict)
    );
    // Publish should return FALSE, because the node was already published.
    $this->assertFalse($metastore->publish('data-dictionary', $dict_id));

    // Build dataset.
    $dataset_id = $uuid->generate();
    $this->assertInstanceOf(
      RootedJsonData::class,
      $dataset = $validMetadataFactory->get(
        $this->getDataset($dataset_id, 'Test ' . $dataset_id, [$resourceUrl], TRUE),
        'dataset'
      )
    );
    // Create dataset.
    $this->assertEquals($dataset_id, $metastore->post('dataset', $dataset));
    // Publish should return FALSE, because the node was already published.
    $this->assertFalse($metastore->publish('dataset', $dataset_id));

    // Run queue items to perform the import.
    $this->runQueues(['localize_import', 'datastore_import', 'post_import']);

    // Retrieve dataset distribution ID.
    $this->assertInstanceOf(
      RootedJsonData::class,
      $dataset = $metastore->get('dataset', $dataset_id)
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

  // Create a data dictionary.



  // Create a dataset, linked to the data dictionary.

  // Ask the endpoint for a download.

  // Check the format of the date column.

}
