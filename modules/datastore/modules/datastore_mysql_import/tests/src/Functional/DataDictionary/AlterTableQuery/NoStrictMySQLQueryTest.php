<?php

namespace Drupal\Tests\datastore_mysql_import\Functional\DataDictionary\AlterTableQuery;

use Drupal\metastore\DataDictionary\DataDictionaryDiscovery;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\common\Traits\GetDataTrait;
use Drupal\Tests\common\Traits\QueueRunnerTrait;
use RootedData\RootedJsonData;

class NoStrictMySQLQueryTest extends BrowserTestBase {

  use GetDataTrait, QueueRunnerTrait;

  protected static $modules = [
    'datastore_mysql_import',
    'node',
  ];

  protected $defaultTheme = 'stark';

  public function testPostImport() {
    // Dependencies.
    $resourceFile = 'data-dict.csv';
    $uuid = $this->container->get('uuid');
    /** @var \Drupal\metastore\ValidMetadataFactory $validMetadataFactory */
    $validMetadataFactory = $this->container->get('dkan.metastore.valid_metadata');
    /** @var \Drupal\metastore\MetastoreService $metastore */
    $metastore = $this->container->get('dkan.metastore.service');
    $resourceUrl = $this->setUpResourceFile($resourceFile);

    // Set per-reference data-dictinary in metastore config.
    $this->config('metastore.settings')
      ->set('data_dictionary_mode', DataDictionaryDiscovery::MODE_REFERENCE)
      ->save();
    $this->assertEquals(
      DataDictionaryDiscovery::MODE_REFERENCE,
      $this->config('metastore.settings')->get('data_dictionary_mode')
    );

    // Create a data dictionary for wide_table.csv. (Columns are numeric.)
    // Build data-dictionary.
    $dict_id = $uuid->generate();
    $date_format = '%m/%d/%Y';
    $fields = [
      [
//        'name' => 'b',
//        'title' => 'b_title',
//        'type' => 'date',
//        'format' => $date_format,
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

    // Import wide_table.csv
    // Run post-import process on dataset.
    // Verify that the process was successful.
    // Verify that our DD schema was applied.











//
//    // Build dataset.
//    $dataset_id = $uuid->generate();
//    $this->assertInstanceOf(
//      RootedJsonData::class,
//      $dataset = $validMetadataFactory->get(
//        $this->getDataset(
//          $dataset_id,
//          'Test ' . $dataset_id,
//          [$resourceUrl],
//          TRUE,
//          'dkan://metastore/schemas/data-dictionary/items/' . $dict_id
//        ),
//        'dataset'
//      )
//    );
//    // Create dataset.
//    $this->assertEquals(
//      $dataset_id,
//      $metastore->post('dataset', $dataset)
//    );
//    // Publish should return FALSE, because the node was already published.
//    $this->assertFalse($metastore->publish('dataset', $dataset_id));
//
//    // Retrieve dataset.
//    $this->assertInstanceOf(
//      RootedJsonData::class,
//      $dataset = $metastore->get('dataset', $dataset_id)
//    );
//    // The dataset references the dictionary. DescribedBy will contain the https
//    // URL-style reference.
//    $this->assertStringContainsString(
//      $dict_id,
//      $dataset->{'$["%Ref:distribution"][0].data.describedBy'}
//    );
//    // Get the distribution ID.
//    $distribution_id = $dataset->{'$["%Ref:distribution"][0].identifier'};
//
//    // Dictionary fields are applied to the dataset.
//    /** @var \Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer $dictionary_enforcer */
//    $dictionary_enforcer = $this->container->get('dkan.datastore.service.resource_processor.dictionary_enforcer');
//    $this->assertCount(
//      1,
//      $dictionary_fields = $dictionary_enforcer->returnDataDictionaryFields($distribution_id)
//    );
//    $this->assertEquals($date_format, $dictionary_fields[0]['format'] ?? 'not found');
//
//    // Set the dictionary CSV header mode before the import.
//    $this->config('metastore.settings')
//      ->set('csv_headers_mode', 'dictionary_titles')
//      ->save();
//
//    // Run queue items to perform the import.
//    $this->runQueues(['localize_import', 'datastore_import', 'post_import']);
//
//    // Query for the dataset, as a streaming CSV.
//    $client = $this->getHttpClient();
//    $response = $client->request(
//      'GET',
//      $this->baseUrl . '/api/1/datastore/query/' . $dataset_id . '/0/download',
//      ['query' => ['format' => 'csv']]
//    );
//
//    $lines = explode("\n", $response->getBody()->getContents());
//    // Header should be using the dictionary title.
//    $this->assertEquals('a,b_title,c,d,e', $lines[0]);
//
//    // Set the machine name CSV header mode.
//    $this->config('metastore.settings')
//      ->set('csv_headers_mode', 'machine_names')
//      ->save();
//
//    // Make another request.
//    $response = $client->request(
//      'GET',
//      $this->baseUrl . '/api/1/datastore/query/' . $dataset_id . '/0/download',
//      ['query' => ['format' => 'csv']]
//    );
//    $lines = explode("\n", $response->getBody()->getContents());
//    // Header should be using the machine name title.
//    $this->assertEquals('a,b,c,d,e', $lines[0]);
//    // Date value should use the dictionary format.
//    $this->assertEquals('1,02/23/1978,9.07,efghijk,0', $lines[1]);
  }

}
