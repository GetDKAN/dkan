<?php

namespace Drupal\Tests\metastore\Functional;

use Drupal\Tests\BrowserTestBase;
use GuzzleHttp\Client;
use RootedData\RootedJsonData;

/**
 * Metastore service API caching.
 *
 * @group dkan
 * @group metastore
 * @group functional
 * @group btb
 */
class MetastoreApiPageCacheBTBTest extends BrowserTestBase {

  protected static $modules = [
    'common',
    'datastore',
    'harvest',
    'metastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

  private const S3_PREFIX = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit';
  private const FILENAME_PREFIX = 'dkan_default_content_files_s3_amazonaws_com_phpunit_';

  public function setUp(): void {
    parent::setUp();

    \drupal_flush_all_caches();
    \Drupal::cache('default')->invalidateAll();
    \Drupal::cache('default')->deleteAll();
    \Drupal::cache('page')->invalidateAll();
    \Drupal::cache('page')->deleteAll();

    // Ensure the proper triggering properties are set for datastore comparison.
    $this->config('datastore.settings')
      ->set('triggering_properties', ['modified'])
      ->save();
  }

  protected function tearDown() : void {
    \drupal_flush_all_caches();
    parent::tearDown();
  }

  /**
   * Test dataset page caching.
   */
  public function testDatasetApiPageCache() {
    $this->markTestIncomplete('legitimately not complete.');
    // Post dataset.
    $datasetRootedJsonData = $this->getData(111, '1', ['1.csv']);
    $this->assertNotEmpty(
      $this->httpVerbHandler('post', $datasetRootedJsonData, json_decode($datasetRootedJsonData))
    );

    $client = new Client([
      'base_uri' => \Drupal::request()->getSchemeAndHttpHost(),
      'timeout'  => 10,
      'http_errors' => FALSE,
      'connect_timeout' => 10,
    ]);

    $queues = [
//      'localize_import',
      'datastore_import',
      'resource_purger',
      'orphan_reference_processor',
      'orphan_resource_remover',
    ];

    // Request once, should not return cached version.
    $response = $client->request('GET', 'api/1/metastore/schemas/dataset/items/111');
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0] ?? '', print_r($response->getHeaders(), TRUE));
    $response = $client->request('GET', 'api/1/metastore/schemas/dataset/items/111/docs');
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0] ?? '', $response->getBody());

    // Request again, should return cached version.
    $response = $client->request('GET', 'api/1/metastore/schemas/dataset/items/111');
    $this->assertEquals('HIT', $response->getHeaders()['X-Drupal-Cache'][0]);
    $response = $client->request('GET', 'api/1/metastore/schemas/dataset/items/111/docs');
    $this->assertEquals('HIT', $response->getHeaders()['X-Drupal-Cache'][0]);

    // Importing the datastore should invalidate the cache. Run twice so that
    // localize_import can trigger the datastore_import queue.
    $this->runQueues(['localize']);
    $this->runQueues($queues);

    $response = $client->request('GET', 'api/1/metastore/schemas/dataset/items/111');
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0], $response->getBody());

    // Get the variants of the import endpoint
    $response = $client->request('GET', 'api/1/metastore/schemas/dataset/items/111?show-reference-ids');
    $dataset = json_decode($response->getBody()->getContents());
    $distributionId = $dataset->distribution[0]->identifier;
    $resourceId = $dataset->distribution[0]->data->{'%Ref:downloadURL'}[0]->identifier;
    $response = $client->request('GET', 'api/1/datastore/imports/' . $distributionId);
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0]);
    $response = $client->request('GET', 'api/1/datastore/imports/' . $resourceId);
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0]);

    $response = $client->request('GET', 'api/1/datastore/query/111/0');
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0]);

    // Request again, should return cached version.
    $response = $client->request('GET', 'api/1/metastore/schemas/dataset/items/111');
    $this->assertEquals('HIT', $response->getHeaders()['X-Drupal-Cache'][0]);
    $response = $client->request('GET', 'api/1/datastore/query/111/0');
    $this->assertEquals('HIT', $response->getHeaders()['X-Drupal-Cache'][0]);
    $response = $client->request('GET', 'api/1/datastore/imports/' . $distributionId);
    $this->assertEquals('HIT', $response->getHeaders()['X-Drupal-Cache'][0]);
    $response = $client->request('GET', 'api/1/datastore/imports/' . $resourceId);
    $this->assertEquals('HIT', $response->getHeaders()['X-Drupal-Cache'][0]);

    // Editing the dataset should invalidate the cache.
    $datasetRootedJsonData->{'$.description'} = 'Add a description.';
    $datasetRootedJsonData->{'$.modified'} = '2021-05-07';
    $this->httpVerbHandler('put', $datasetRootedJsonData, json_decode($datasetRootedJsonData));

    // Importing the datastore should invalidate the cache.
    $this->runQueues($queues);

    $response = $client->request('GET', 'api/1/metastore/schemas/dataset/items/111');
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0]);
    $response = $client->request('GET', 'api/1/metastore/schemas/dataset/items/111/docs');
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0]);
    $response = $client->request('GET', 'api/1/datastore/query/111/0');
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0]);

    // The import endpoints shouldn't be there at all anymore.
    $response = $client->request('GET', 'api/1/datastore/imports/' . $distributionId);
    $this->assertEquals(404, $response->getStatusCode());
    $response = $client->request('GET', 'api/1/datastore/imports/' . $resourceId);
    $this->assertEquals(404, $response->getStatusCode());
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
   * @return string|false
   *   Json encoded string of this dataset's metadata, or FALSE if error.
   */
  private function getData(string $identifier, string $title, array $downloadUrls): RootedJsonData {

    $data = new \stdClass();
    $data->title = $title;
    $data->description = 'Some description.';
    $data->identifier = $identifier;
    $data->accessLevel = 'public';
    $data->modified = '06-04-2020';
    $data->keyword = ['some keyword'];
    $data->distribution = [];

    foreach ($downloadUrls as $key => $downloadUrl) {
      $distribution = new \stdClass();
      $distribution->title = 'Distribution #' . $key . ' for ' . $identifier;
      $distribution->downloadURL = self::S3_PREFIX . '/' . $downloadUrl;
      $distribution->mediaType = 'text/csv';

      $data->distribution[] = $distribution;
    }

    $valid_metadata_factory = $this->container->get('dkan.metastore.valid_metadata');
    return $valid_metadata_factory->get(json_encode($data), 'dataset');
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

  private function renderDatasetNodesForCache() {
    // Render all the dataset nodes to address cache.
    $renderer = \Drupal::service('renderer');
    $entityTypeManager = \Drupal::service('entity_type.manager');

    $database_service = \Drupal::service('database');
    $query = $database_service->select('node', 'n');
    $query->addField('n', 'nid');
    $nids = $query->execute()->fetchCol();

    $node_storage = $entityTypeManager->getStorage('node');
    $node_render = $entityTypeManager->getViewBuilder('node');
    foreach ($node_storage->loadMultiple($nids) as $node) {
      $build = $node_render->view($node);
      $renderer->renderPlain($build);
    }
  }

  private function httpVerbHandler(string $method, RootedJsonData $json, $dataset) {
    $metastore_service = $this->container->get('dkan.metastore.service');

    if ($method == 'post') {
      $identifier = $metastore_service->post('dataset', $json);
    }
    // PUT for now, refactor later if more verbs are needed.
    else {
      $id = $dataset->identifier;
      $info = $metastore_service->put('dataset', $id, $json);
      $identifier = $info['identifier'];
    }

    return $identifier;
  }

}
