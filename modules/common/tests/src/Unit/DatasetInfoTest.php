<?php

namespace Drupal\Tests\common\Unit;

use Drupal\common\DatasetInfo;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\Service\Info\ImportInfo;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Storage\Data;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\NodeData;
use Drupal\node\Entity\Node;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\common\DatasetInfo
 *
 * @group common
 * @group dkan-core
 */
class DatasetInfoTest extends TestCase {

  public function testGather() {
    // Set up some dependencies.
    $field_value = $this->getMockBuilder(FieldItemListInterface::class)
      ->onlyMethods(['getString'])
      ->getMockForAbstractClass();
    // This method must return 'draft' to make sure we set
    // 'published_revision' in the result.
    $field_value->method('getString')
      ->willReturn('draft');

    $latest = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();
    $latest->method('get')
      ->willReturn($field_value);

    $published_content = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();

    $storage = $this->getMockBuilder(Data::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getEntityLatestRevision', 'getEntityPublishedRevision'])
      ->getMockForAbstractClass();
    $storage->method('getEntityLatestRevision')
      ->willReturn($latest);
    // This method must return a non-empty object to make sure we set
    // 'published_revision' in the result.
    $storage->method('getEntityPublishedRevision')
      ->willReturn($published_content);

    $data_factory = $this->getMockBuilder(DataFactory::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getInstance'])
      ->getMock();
    $data_factory->method('getInstance')
      ->willReturn($storage);

    // Make a mock DatasetInfo. We mock this so we can mock the
    // getRevisionInfo() method.
    $dataset_info = $this->getMockBuilder(DatasetInfo::class)
      ->onlyMethods(['getRevisionInfo'])
      ->getMock();
    $dataset_info->method('getRevisionInfo')
      ->willReturn([
        'uuid' => 'fake revision info',
      ]);

    // Set the storage.
    $dataset_info->setStorage($data_factory);

    // Invoke the method. If it has a 'notice' key, then we failed to mock
    // properly.
    $this->assertArrayNotHasKey(
      'notice', $result = $dataset_info->gather('uuid')
    );
    // Check that we have both a latest and published revision. Unset the keys
    // as they go by.
    foreach (['latest_revision', 'published_revision'] as $expected_key) {
      $this->assertArrayHasKey($expected_key, $result);
      unset($result[$expected_key]);
    }
    // Since we unset the keys we expected, there shouldn't be any left.
    $this->assertCount(0, $result);
  }

  /**
   * @covers ::getStorage
   */
  public function testGetStorage() {
    // Make some dependencies.
    // Initially, we'll make the datastore service return the table object.
    $datastore_service = $this->getMockBuilder(DatastoreService::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getStorage'])
      ->getMockForAbstractClass();
    $datastore_service->method('getStorage')
      ->willReturn(
        $this->getMockBuilder(DatabaseTable::class)
          ->disableOriginalConstructor()
          ->getMock()
      );

    // Make a mock DatasetInfo. Stub getRevisionInfo() to return NULL so it
    // doesn't need dependencies.
    $dataset_info = $this->getMockBuilder(DatasetInfo::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getRevisionInfo'])
      ->getMock();

    // Set our datastore service trap.
    $dataset_info->setDatastore($datastore_service);

    // The getStorage() method is protected, so unprotect it.
    $ref_get_storage = new \ReflectionMethod($dataset_info, 'getStorage');
    $ref_get_storage->setAccessible(TRUE);

    // We get back the database table object.
    $this->assertIsObject(
      $ref_get_storage->invokeArgs($dataset_info, ['identifier', 'version'])
    );

    // This time, throw an exception instead of returning a database object.
    $datastore_service->method('getStorage')
      ->will($this->throwException(new \Exception('boom.')));

    // The getStorage() method converts our exception into a NULL.
    $this->assertNull(
      $ref_get_storage->invokeArgs($dataset_info, ['identifier', 'version'])
    );
  }

  /**
   * @covers ::getDistributionsInfo
   */
  public function testGetDistributionsInfoNotFound() {
    // Mock a DatasetInfo object. We do this to bypass the constructor.
    $dataset_info = $this->getMockBuilder(DatasetInfo::class)
      ->disableOriginalConstructor()
      ->getMock();

    // The getDistributionsInfo() method is protected, so unprotect it.
    $ref_get_distributions_info = new \ReflectionMethod($dataset_info, 'getDistributionsInfo');
    $ref_get_distributions_info->setAccessible(TRUE);

    // Invoke with an empty object to get the 'Not found' result.
    $this->assertEquals(
      ['Not found'],
      $ref_get_distributions_info->invokeArgs($dataset_info, [new \stdClass()])
    );
  }

  /**
   *
   */
  public function testMetastoreNotEnabled() {
    $datasetInfo = DatasetInfo::create($this->getCommonChain()->getMock());

    $expected = [
      'notice' => 'The DKAN Metastore module is not enabled.',
    ];
    $result = $datasetInfo->gather('foo');

    $this->assertEquals($expected, $result);
  }

  /**
   *
   */
  public function testUuidNotFound() {
    $mockStorage = (new Chain($this))
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      ->add(NodeData::class, 'getEntityLatestRevision', NULL);
    $mockDatastore = (new Chain($this))
      ->add(DatastoreService::class);
    $mockResourceMapper = (new Chain($this))
      ->add(ResourceMapper::class);

    $datasetInfo = DatasetInfo::create($this->getCommonChain()->getMock());
    $datasetInfo->setStorage($mockStorage->getMock());
    $datasetInfo->setDatastore($mockDatastore->getMock());
    $datasetInfo->setResourceMapper($mockResourceMapper->getMock());

    $expected = [
      'notice' => 'Not found',
    ];
    $result = $datasetInfo->gather('foo');

    $this->assertEquals($expected, $result);
  }

  /**
   * @covers ::getResourcesInfo
   */
  public function testGetResourcesInfo() {
    // Mock some dependencies.
    $import_info = $this->getMockBuilder(ImportInfo::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getItem'])
      ->getMock();
    $import_info->expects($this->once())
      ->method('getItem')
      ->willReturn((object) [
        'fileFetcherStatus' => 'status',
        'fileFetcherPercentDone' => 99,
        'importerPercentDone' => 99,
        'importerStatus' => 'importer_status',
        'importerError' => 'importer_error',
      ]);

    $resource_mapper = $this->getMockBuilder(ResourceMapper::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();
    // Return falsy value for any call. getResourcesInfo() will account for
    // NULL values.
    $resource_mapper->expects($this->exactly(2))
      ->method('get')
      ->willReturn(NULL);

    // Mock the DatasetInfo object. We do this so we can mock the getStorage()
    // method to return NULL.
    $dataset_info = $this->getMockBuilder(DatasetInfo::class)
      ->onlyMethods(['getStorage'])
      ->disableOriginalConstructor()
      ->getMock();
    $dataset_info->method('getStorage')
      ->willReturn(NULL);

    // Set the properties with our mocked dependencies.
    $ref_import_info = new \ReflectionProperty($dataset_info, 'importInfo');
    $ref_import_info->setAccessible(TRUE);
    $ref_import_info->setValue($dataset_info, $import_info);
    $ref_resource_mapper = new \ReflectionProperty($dataset_info, 'resourceMapper');
    $ref_resource_mapper->setAccessible(TRUE);
    $ref_resource_mapper->setValue($dataset_info, $resource_mapper);

    // Set getResourcesInfo() to be public, so we can invoke it.
    $ref_get_resources_info = new \ReflectionMethod($dataset_info, 'getResourcesInfo');
    $ref_get_resources_info->setAccessible(TRUE);

    // Call the method under test with just enough data to ensure a result.
    $this->assertArrayNotHasKey('notice',
      $result = $ref_get_resources_info->invokeArgs($dataset_info, [
        (object) [
          'identifier' => 'distribution identifier',
          'data' => (object) [
            '%Ref:downloadURL' => [
              (object) [
                'data' => (object) [
                  'identifier' => 'my identifier',
                  'version' => 'my version',
                ],
              ],
            ],
          ],
        ],
      ])
    );

    // Assert that our result has all the things we expect, and no more. We
    // don't care so much about the values.
    $expected_keys = [
      'distribution_uuid',
      'resource_id',
      'resource_version',
      'fetcher_status',
      'fetcher_percent_done',
      'file_path',
      'source_path',
      'importer_percent_done',
      'importer_status',
      'importer_error',
      'table_name',
    ];
    foreach ($expected_keys as $key) {
      $this->assertArrayHasKey($key, $result);
      // Unset each key as we pass it by.
      unset($result[$key]);
    }
    // After unsetting all the expected keys, there shouldn't be any left.
    $this->assertCount(0, $result);
  }

  public function testDistributionNoResource() {
    $metadata = [
      'title' => 'no resources',
      'identifier' => 'dataset-id',
      'modified' => '2020-06-11T00:30:45+00:00',
      '%modified' => '2021-07-12',
      'distribution' =>
        [
          [
            '@type' => 'dcat:Distribution',
            'title' => 'bo resources distribution',
          ],
        ],
      '%Ref:distribution' =>
        [
          [
            'identifier' => 'distribution-id',
            'data' =>
              [
                '@type' => 'dcat:Distribution',
                'title' => 'no resources distribution',
              ],
          ],
        ],
    ];

    $mockFieldJsonMetadata = (new Chain($this))
      ->add(FieldItemListInterface::class, 'getString', json_encode($metadata));
    $mockModerationState = (new Chain($this))
      ->add(FieldItemListInterface::class, 'getString', 'published');

    $nodeGetOptions = (new Options())
      ->add('field_json_metadata', $mockFieldJsonMetadata->getMock())
      ->add('moderation_state', $mockModerationState->getMock());

    $mockNode = (new Chain($this))
      ->add(Node::class, 'uuid', 'dataset-id')
      ->add(Node::class, 'id', '1')
      ->add(Node::class, 'getRevisionId', '1')
      ->add(Node::class, 'get', $nodeGetOptions);

    $mockStorage = (new Chain($this))
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      ->add(NodeData::class, 'getEntityLatestRevision', $mockNode->getMock())
      ->add(NodeData::class, 'getEntityPublishedRevision', $mockNode->getMock());

    $datasetInfo = DatasetInfo::create($this->getCommonChain()->getMock());
    $datasetInfo->setStorage($mockStorage->getMock());

    $result = $datasetInfo->gather('foo');
    $this->assertEquals('No resource found', $result["latest_revision"]["distributions"][0][0]);
  }

  /**
   *
   */
  private function getCommonChain() {
    $options = (new Options())
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $options);
  }

}
