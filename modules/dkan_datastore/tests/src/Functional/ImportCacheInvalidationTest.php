<?php

namespace Drupal\Tests\dkan_metastore\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\dkan_common\Traits\QueueRunnerTrait;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use RootedData\RootedJsonData;

/**
 * Test cache invalidation behavior for datastore endpoints.
 *
 * @group dkan
 * @group dkan_datastore
 * @group functional2
 */
class ImportCacheInvalidationTest extends BrowserTestBase {

  use QueueRunnerTrait;

  protected static $modules = [
    'dkan_common',
    'dkan_datastore',
    'dynamic_page_cache',
    'dkan_harvest',
    'dkan_metastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Ensure the proper triggering properties are set for datastore comparison.
    $this->config('dkan_datastore.settings')
      ->set('triggering_properties', ['modified'])
      ->save();

    // Set up a Guzzle client using our service.
    $this->httpClient = $this->container->get('http_client_factory')
      ->fromOptions([
        'base_uri' => $this->baseUrl,
        'http_errors' => FALSE,
        'timeout' => 600,
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
    $metastoreResponse = $this->apiRequest('GET', 'api/1/metastore/schemas/dataset/items/' . $identifier);
    $this->assertEquals(404, $metastoreResponse->getStatusCode(), $metastoreResponse->getBody());

    $datasetRootedJsonData = $this->getData($identifier, '1', ['1.csv']);

    // Post dataset.
    $this->assertEquals(
      $identifier,
      $this->httpVerbHandler('post', $datasetRootedJsonData, json_decode($datasetRootedJsonData))
    );

    // Request datastore; should fail, as the import queue hasn't run yet.
    $response = $this->apiRequest('GET', 'api/1/datastore/query/' . $identifier . '/0');
    $this->assertEquals(400, $response->getStatusCode(), $response->getBody());

    $queues = ['localize_import', 'datastore_import'];

    // Importing the datastore should invalidate the cache.
    $this->runQueues($queues);
    // Re-render the dataset nodes using the render service.
    $this->renderDatasetNodesForCache();

    // Request once, should not return a cached version (it's a novel request).
    $response = $this->apiRequest('GET', 'api/1/datastore/query/' . $identifier . '/0');
    $this->assertEquals(200, $response->getStatusCode(), $response->getBody());
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0] ?? '', $response->getBody());

    // Request again, confirm it's now a hit.
    $response = $this->apiRequest('GET', 'api/1/datastore/query/' . $identifier . '/0');
    $this->assertEquals(200, $response->getStatusCode(), $response->getBody());
    $this->assertEquals('HIT', $response->getHeaders()['X-Drupal-Cache'][0] ?? '', $response->getBody());

    // Now let's patch the dataset and switch the download URL.
    $datasetRootedJsonData = $this->getData($identifier, '1', ['2.csv']);
    $this->assertEquals(
      $identifier,
      $this->httpVerbHandler('put', $datasetRootedJsonData, json_decode($datasetRootedJsonData))
    );

    // We haven't run an import yet, the dataset query should return error.
    $response = $this->apiRequest('GET', 'api/1/datastore/query/' . $identifier . '/0');
    $this->assertEquals(400, $response->getStatusCode(), $response->getBody());

    $this->runQueues($queues);

    // Now we should get a 200 and a cache MISS.
    $response = $this->apiRequest('GET', 'api/1/datastore/query/' . $identifier . '/0');
    $this->assertEquals(200, $response->getStatusCode(), $response->getBody());
    $this->assertEquals('MISS', $response->getHeaders()['X-Drupal-Cache'][0] ?? '', $response->getBody());
  }

  /**
   * Generate dataset metadata, possibly with multiple distributions.
   *
   * @param string $identifier
   *   Dataset identifier.
   * @param string $title
   *   Dataset title.
   * @param array $filenames
   *   Array of resource files URLs for this dataset.
   *
   * @return \RootedData\RootedJsonData
   *   RootedJsonData object containing the dataset metadata.
   */
  private function getData(string $identifier, string $title, array $filenames): RootedJsonData {

    $data = new \stdClass();
    $data->title = $title;
    $data->description = 'Some description.';
    $data->identifier = $identifier;
    $data->accessLevel = 'public';
    $data->modified = '06-04-2020';
    $data->keyword = ['some keyword'];
    $data->distribution = [];

    foreach ($filenames as $key => $filename) {
      $distribution = new \stdClass();
      $distribution->title = 'Distribution #' . $key . ' for ' . $identifier;
      $distribution->downloadURL = $this->getPublicCsvUrl($filename);
      $distribution->mediaType = 'text/csv';

      $data->distribution[] = $distribution;
    }

    $valid_metadata_factory = $this->container->get('dkan.metastore.valid_metadata');
    return $valid_metadata_factory->get(json_encode($data), 'dataset');
  }

  /**
   * Render all the dataset nodes to address cache.
   */
  private function renderDatasetNodesForCache() {
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

  /**
   * Handle HTTP verbs for dataset operations.
   */
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

  /**
   * Copy file from the data folder to public:// and produce a URL.
   *
   * @param string $filename
   *   Filename. Must be in the module's tests/data folder.
   *
   * @return string
   *   Absolute URL to the copied file.
   */
  private function getPublicCsvUrl(string $filename): string {
    $source = dirname(__DIR__, 2) . '/data/' . $filename;
    $destination = 'public://' . $filename;
    $this->container->get('file_system')->copy($source, $destination, TRUE);
    return $this->container->get('file_url_generator')->generateAbsoluteString($destination);
  }

}
