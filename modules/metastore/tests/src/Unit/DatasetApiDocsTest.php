<?php

namespace Drupal\Tests\metastore\Unit;

use Drupal\Core\Site\Settings;
use Drupal\common\DkanApiDocsGenerator;
use Drupal\common\Plugin\DkanApiDocsBase;
use Drupal\common\Plugin\DkanApiDocsPluginManager;
use Drupal\metastore\DatasetApiDocs;
use Drupal\metastore\SchemaRetriever;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\ValidMetadataFactory;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use RootedData\RootedJsonData;

/**
 * Unit tests for DatasetApiDocs.
 */
class DatasetApiDocsTest extends TestCase {

  /**
   *
   */
  public function testBuildDatasetApiDocsWithDkanApiBase() {

    $dkanApiPath = '/foo/bar';

    $settings = new Settings(['dkan_api_base' => $dkanApiPath]);

    $generator = new DkanApiDocsGenerator(
      $this->getManagerChain()->getMock(),
      $settings
    );

    $service = $this->getServiceChain()->getMock();

    $datasetDoc = new DatasetApiDocs($generator, $service, $settings);

    $spec = $datasetDoc->getDatasetSpecific('123');

    $this->assertTrue(is_array($spec['paths']));

    $paths = array_keys($spec['paths']);
    $expected_paths = [
      $dkanApiPath . '/api/1/metastore/schemas/dataset/items/123',
      $dkanApiPath . '/api/1/datastore/query/123/{index}',
      $dkanApiPath . '/api/1/datastore/query/{distributionId}',
      $dkanApiPath . '/api/1/datastore/sql'
    ];
    foreach ($expected_paths as $path) {
      $this->assertContains($path, $paths);
    }
  }

  /**
   *
   */
  private function getSpec(): array {
    return [
      'openapi' => '3.0.2',
      'info' => [
        'title' => '',
        'version' => '',
      ],
      'components' => [
        'parameters' => [
          'datasetUuid' => [
            'name' => 'identifier',
            'in' => 'path',
            'required' => true,
            'schema' => ['type' => 'string'],
          ],
          "datastoreDatasetUuid" => [
            'name' => 'datasetId',
            'in' => 'path',
            'required' => true,
            'schema' => ['type' => 'string'],
          ],
          'datastoreDistributionUuid' => [
            'name' => 'distributionId',
            'in' => 'path',
            'required' => true,
            'schema' => ['type' => 'string'],
          ],
          'datastoreDistributionIndex' => [
            'name' => 'index',
            'in' => 'path',
            'required' => true,
            'schema' => ['type' => 'string'],
          ]
        ],
        'responses' => [
          '200JsonOrCsvQueryOk' => ['description' => '']
        ],
        'schemas' => [
          'dataset' => [
            'title' => '',
            'type' => 'object'
          ]
        ]
      ],
      'paths' => [
        '/api/1/metastore/schemas/dataset/items/{identifier}' => [
          'get' => [
            'operationId' => 'dataset-get-item',
            'parameters' => [0 => ['$ref' => '#/components/parameters/datasetUuid']],
            'responses' => ['200' => ['$ref' => '#/components/responses/200JsonOrCsvQueryOk']]
          ]
        ],
        '/api/1/datastore/query/{datasetId}/{index}' => [
          'post' => [
            'operationId' => 'datastore-datasetindex-query-post',
            'parameters' => [
              0 => ['$ref' => '#/components/parameters/datasetUuid'],
              1 => ['$ref' => '#/components/parameters/datastoreDistributionIndex']
            ],
            'responses' => ['200' => ['$ref' => '#/components/responses/200JsonOrCsvQueryOk']]
          ],
          'get' => [
            'operationId' => 'datastore-datasetindex-query-get',
            'parameters' => [
              0 => ['$ref' => '#/components/parameters/datasetUuid'],
              1 => ['$ref' => '#/components/parameters/datastoreDistributionIndex']
            ],
            'responses' => ['200' => ['$ref' => '#/components/responses/200JsonOrCsvQueryOk']]
          ]
        ],
        '/api/1/datastore/query/{distributionId}' => [
          'get' => [
            'operationId' => 'datastore-resource-query-get',
            'parameters' => [0 => ['$ref' => '#/components/parameters/datastoreDistributionUuid']],
            'responses' => ['200' => ['$ref' => '#/components/responses/200JsonOrCsvQueryOk']]
          ]
        ],
        '/api/1/datastore/sql' => ['get' => [
          'operationId' => 'datastore-sql',
          'responses' => ['200' => ['$ref' => '#/components/responses/200JsonOrCsvQueryOk']]
        ]]
      ]
    ];
  }

  private function getManagerChain() {
    $definitions = [
      'metastore_api_docs' => ['id' => 'metastore_api_docs']
    ];

    $spec = $this->getSpec();

    return (new Chain($this))
      ->add(DkanApiDocsPluginManager::class, 'getDefinitions', $definitions)
        ->addd('createInstance', DkanApiDocsBase::class)
      ->add(DkanApiDocsBase::class, 'spec', $spec);
  }

  private function getServiceChain() {
    $dataset = '
    {
      "title": "Test #1",
      "description": "Yep",
      "identifier": "123",
      "accessLevel": "public",
      "modified": "06-04-2020",
      "keyword": ["hello"]
    }';

    return (new Chain($this))
      ->add(MetastoreService::class, 'swapReferences', new RootedJsonData($dataset))
        ->addd('get', new RootedJsonData($dataset))
      ->add(SchemaRetriever::class)
      ->add(DataFactory::class, 'swapReferences', new RootedJsonData($dataset))
      ->add(ValidMetadataFactory::class, 'get', $dataset);
  }

}
