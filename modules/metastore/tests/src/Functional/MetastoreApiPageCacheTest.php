<?php

namespace Drupal\Tests\metastore\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\common\Traits\QueueRunnerTrait;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use RootedData\RootedJsonData;

/**
 * Metastore service API caching.
 *
 * @group dkan
 * @group metastore
 * @group functional
 * @group btb
 * @group functional2
 */
class MetastoreApiPageCacheTest extends BrowserTestBase {

  use QueueRunnerTrait;

  protected static $modules = [
    'common',
    'datastore',
    'dynamic_page_cache',
    'harvest',
    'metastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  private const S3_PREFIX = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit';

  public function setUp(): void {
    parent::setUp();

    // Ensure the proper triggering properties are set for datastore comparison.
    $this->config('datastore.settings')
      ->set('triggering_properties', ['modified'])
      ->save();

    // Set up a Guzzle client using our service.
    $this->httpClient = $this->container->get('http_client_factory')
      ->fromOptions([
        'base_uri' => $this->baseUrl,
        'http_errors' => FALSE,
      ]);
  }

  /**
   * Make an API request, using method, path, and query.
   *
   * @param string $method
   *   HTTP method.
   * @param string $path
   *   Request path.
   * @param array $query
   *   Request query as an array. Example: '?foo' would be ['foo' => TRUE].
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response object from Guzzle.
   */
  protected function apiRequest(string $method, string $path, array $query = []): ResponseInterface {
    return $this->httpClient->request(
      $method,
      $this->buildUrl($path),
      [RequestOptions::QUERY => $query]
    );
  }

  /**
   * Test dataset page caching.
   */
  public function testDatasetApiPageCache() {
    $identifier = '111';

    // Before we've done anything, GET should yield a 404.
    $response = $this->apiRequest('GET', 'api/1/metastore/schemas/dataset/items/' . $identifier);
    $this->assertEquals(404, $response->getStatusCode(), $response->getBody());

    $datasetRootedJsonData = $this->getData($identifier, '1', ['1.csv']);

    // Post dataset.
    $this->assertEquals(
      $identifier,
      $this->httpVerbHandler('post', $datasetRootedJsonData, json_decode($datasetRootedJsonData))
    );

    $queues = [
      'localize_import',
      'datastore_import',
      'resource_purger',
      'orphan_reference_processor',
      'orphan_resource_remover',
    ];

    // Request once, should not return cached version.
    $response = $this->apiRequest('GET', 'api/1/metastore/schemas/dataset/items/' . $identifier);
    $this->assertEquals(200, $response->getStatusCode(), $response->getBody());
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0] ?? '', $response->getBody());
    $response = $this->apiRequest('GET', 'api/1/metastore/schemas/dataset/items/' . $identifier . '/docs');
    $this->assertEquals(200, $response->getStatusCode(), $response->getBody());
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0] ?? '', $response->getBody());

    // Request again, should return cached version.
    $response = $this->apiRequest('GET', 'api/1/metastore/schemas/dataset/items/' . $identifier);
    $this->assertEquals(200, $response->getStatusCode(), $response->getBody());
    $this->assertEquals('HIT', $response->getHeaders()['X-Drupal-Cache'][0]);
    $response = $this->apiRequest('GET', 'api/1/metastore/schemas/dataset/items/' . $identifier . '/docs');
    $this->assertEquals(200, $response->getStatusCode(), $response->getBody());
    $this->assertEquals('HIT', $response->getHeaders()['X-Drupal-Cache'][0]);

    // Importing the datastore should invalidate the cache.
    $this->runQueues($queues);
    // Re-render the dataset nodes using the render service.
    $this->renderDatasetNodesForCache();

    // Cache is a miss because we performed an import.
    $response = $this->apiRequest('GET', 'api/1/metastore/schemas/dataset/items/' . $identifier);
    $this->assertEquals(200, $response->getStatusCode(), $response->getBody());
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0], $response->getBody());

    // Get the variants of the import endpoint
    $response = $this->apiRequest('GET', 'api/1/metastore/schemas/dataset/items/' . $identifier, ['show-reference-ids' => TRUE]);
    $dataset = json_decode($response->getBody()->getContents());
    $distributionId = $dataset->distribution[0]->identifier ?? '';
    $resourceId = $dataset->distribution[0]->data->{'%Ref:downloadURL'}[0]->identifier ?? '';
    $response = $this->apiRequest('GET', 'api/1/datastore/imports/' . $distributionId);
    $this->assertEquals(200, $response->getStatusCode(), $response->getBody());
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0]);
    $response = $this->apiRequest('GET', 'api/1/datastore/imports/' . $resourceId);
    $this->assertEquals(200, $response->getStatusCode(), $response->getBody());
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0]);

    $response = $this->apiRequest('GET', 'api/1/datastore/query/' . $identifier . '/0');
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0]);

    // Request again, should return cached version.
    $response = $this->apiRequest('GET', 'api/1/metastore/schemas/dataset/items/' . $identifier);
    $this->assertEquals('HIT', $response->getHeaders()['X-Drupal-Cache'][0]);
    $response = $this->apiRequest('GET', 'api/1/datastore/query/' . $identifier . '/0');
    $this->assertEquals('HIT', $response->getHeaders()['X-Drupal-Cache'][0]);
    $response = $this->apiRequest('GET', 'api/1/datastore/imports/' . $distributionId);
    $this->assertEquals('HIT', $response->getHeaders()['X-Drupal-Cache'][0]);
    $response = $this->apiRequest('GET', 'api/1/datastore/imports/' . $resourceId);
    $this->assertEquals('HIT', $response->getHeaders()['X-Drupal-Cache'][0]);

    // Editing the dataset should invalidate the cache.
    $datasetRootedJsonData->{'$.description'} = 'Add a description.';
    $datasetRootedJsonData->{'$.modified'} = '2021-05-07';
    $this->httpVerbHandler('put', $datasetRootedJsonData, json_decode($datasetRootedJsonData));

    // Importing the datastore should invalidate the cache.
    $this->runQueues($queues);
    // Re-render the dataset nodes using the render service.
    $this->renderDatasetNodesForCache();

    $response = $this->apiRequest('GET', 'api/1/metastore/schemas/dataset/items/' . $identifier);
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0]);
    $response = $this->apiRequest('GET', 'api/1/metastore/schemas/dataset/items/' . $identifier . '/docs');
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0]);
    $response = $this->apiRequest('GET', 'api/1/datastore/query/' . $identifier . '/0');
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0], $response->getBody()->getContents());

    // The import endpoints shouldn't be there at all anymore.
    $response = $this->apiRequest('GET', 'api/1/datastore/imports/' . $distributionId);
    $this->assertEquals(404, $response->getStatusCode());
    $response = $this->apiRequest('GET', 'api/1/datastore/imports/' . $resourceId);
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

  private function renderDatasetNodesForCache() {
    // Render all the dataset nodes to address cache.
    $renderer = $this->container->get('renderer');
    $entityTypeManager = $this->container->get('entity_type.manager');
    $database_service = $this->container->get('database');

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
