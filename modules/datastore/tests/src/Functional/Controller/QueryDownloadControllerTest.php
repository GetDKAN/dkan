<?php

namespace Drupal\Tests\datastore\Functional\Controller;

use Drupal\Core\File\FileSystemInterface;
use Drupal\metastore\DataDictionary\DataDictionaryDiscovery;
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
 * @group functional3
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

  protected static $modules = [
    'datastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

  /**
   * Test application of data dictionary schema to CSV generated for download.
   */
  public function testDownloadWithMachineName() {
    $resourceFile = 'longcolumn.csv';
    // Dependencies.
    $uuid = $this->container->get('uuid');
    /** @var \Drupal\metastore\ValidMetadataFactory $validMetadataFactory */
    $validMetadataFactory = $this->container->get('dkan.metastore.valid_metadata');
    /** @var \Drupal\metastore\MetastoreService $metastoreService */
    $metastoreService = $this->container->get('dkan.metastore.service');
    $resourceUrl = $this->setUpResourceFile($resourceFile);

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

    // Test for valid field names in json output
    // Query for the dataset, as a streaming JSON.
    $client = $this->getHttpClient();
    $response = $client->request(
      'GET',
      $this->baseUrl . '/api/1/datastore/query/' . $dataset_id . '/0/download',
      ['query' => ['format' => 'json']]
    );

    $json_content = json_decode($response->getBody()->getContents())->results[0];
    $titles = array_keys(get_object_vars($json_content));
    $this->assertEquals('id,name,extra_long_column_name_with_tons_of_characters_that_will_ne_e872,extra_long_column_name_with_tons_of_characters_that_will_ne_5127',
      implode(',',$titles));
  }

  /**
   * Test application of data dictionary schema to CSV generated for download.
   */
  public function testDownloadWithDataDictionary() {
    // Set per-reference data-dictionary in metastore config.
    $this->config('metastore.settings')
      ->set('data_dictionary_mode', DataDictionaryDiscovery::MODE_REFERENCE)
      ->save();
    $this->assertEquals(
      DataDictionaryDiscovery::MODE_REFERENCE,
      $this->config('metastore.settings')->get('data_dictionary_mode')
    );

    $resourceFile = 'data-dict.csv';

    // Dependencies.
    $uuid = $this->container->get('uuid');
    /** @var \Drupal\metastore\ValidMetadataFactory $validMetadataFactory */
    $validMetadataFactory = $this->container->get('dkan.metastore.valid_metadata');
    /** @var \Drupal\metastore\MetastoreService $metastore */
    $metastore = $this->container->get('dkan.metastore.service');
    $resourceUrl = $this->setUpResourceFile($resourceFile);

    // Build data-dictionary.
    $dict_id = $uuid->generate();
    $date_format = '%m/%d/%Y';
    $fields = [
      [
        'name' => 'b',
        'title' => 'b_title',
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
    // Assert the date format is stored correctly.
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
    // The dataset references the dictionary. DescribedBy will contain the https
    // URL-style reference.
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

    // Set the dictionary CSV header mode before the import.
    $this->config('metastore.settings')
      ->set('csv_headers_mode', 'dictionary_titles')
      ->save();

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
    // Header should be using the dictionary title.
    $this->assertEquals('a,b_title,c,d,e', $lines[0]);

    // Set the machine name CSV header mode.
    $this->config('metastore.settings')
      ->set('csv_headers_mode', 'machine_names')
      ->save();

    // Make another request.
    $response = $client->request(
      'GET',
      $this->baseUrl . '/api/1/datastore/query/' . $dataset_id . '/0/download',
      ['query' => ['format' => 'csv']]
    );
    $lines = explode("\n", $response->getBody()->getContents());
    // Header should be using the machine name title.
    $this->assertEquals('a,b,c,d,e', $lines[0]);
    // Date value should use the dictionary format.
    $this->assertEquals('1,02/23/1978,9.07,efghijk,0', $lines[1]);
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
