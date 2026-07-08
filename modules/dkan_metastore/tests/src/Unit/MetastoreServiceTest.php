<?php

namespace Drupal\Tests\dkan_metastore\Unit;

use ColinODell\PsrTestLogger\TestLogger;
use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\dkan_metastore\Exception\ExistingObjectException;
use Drupal\dkan_metastore\Exception\MissingObjectException;
use Drupal\dkan_metastore\Exception\UnmodifiedObjectException;
use Drupal\dkan_metastore\MetastoreService;
use Drupal\dkan_metastore\SchemaRetriever;
use Drupal\dkan_metastore\Storage\Data;
use Drupal\dkan_metastore\Storage\DataFactory;
use Drupal\dkan_metastore\Storage\MetastoreStorageInterface;
use Drupal\dkan_metastore\Storage\NodeData;
use Drupal\dkan_metastore\ValidMetadataFactory;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use RootedData\RootedJsonData;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @coversDefaultClass \Drupal\dkan_metastore\MetastoreService
 *
 * @group dkan
 * @group metastore
 * @group unit
 */
class MetastoreServiceTest extends TestCase {

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\dkan_metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->validMetadataFactory = self::getValidMetadataFactory($this);
  }

  /**
   * Test \Drupal\dkan_metastore\Service::isPublished() method.
   *
   * @covers ::isPublished
   */
  public function testIsPublished() {
    $service = (new Chain($this))
      ->add(MetastoreService::class, 'getStorage', MetastoreStorageInterface::class)
      ->add(MetastoreStorageInterface::class, 'isPublished', TRUE)
      ->getMock();

    $this->assertTrue($service->isPublished('dataset', 1));
  }

  /**
   * Get a dataset.
   *
   * @covers ::get
   */
  public function testGet() {
    $data = $this->validMetadataFactory->get(json_encode(['foo' => 'bar']), 'dataset');

    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, 'retrieve', json_encode(['foo' => 'bar']))
      ->add(ValidMetadataFactory::class, 'get', $data);

    \Drupal::setContainer($container->getMock());

    $service = MetastoreService::create($container->getMock());

    $this->assertEquals(json_encode(['foo' => 'bar']), $service->get("dataset", "1"));
  }

  /**
   * @covers ::getSchemas
   */
  public function testGetSchemas() {
    $container = self::getCommonMockChain($this)
      ->add(SchemaRetriever::class, "getAllIds", ["1"]);

    $service = MetastoreService::create($container->getMock());
    $this->assertEquals(json_encode(["1" => ['foo' => 'bar']]), json_encode($service->getSchemas()));
  }

  /**
   * @covers ::getSchema
   */
  public function testGetSchema() {
    $container = self::getCommonMockChain($this);

    $service = MetastoreService::create($container->getMock());
    $this->assertEquals(json_encode(['foo' => 'bar']), json_encode($service->getSchema("1")));
  }

  /**
   * @covers ::getAll
   */
  public function testGetAll() {
    $expected = $this->validMetadataFactory->get(json_encode(['foo' => 'bar']), 'dataset');

    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, 'retrieveAll', [json_encode(['foo' => 'bar'])])
      ->add(ValidMetadataFactory::class, 'get', $expected);

    \Drupal::setContainer($container->getMock());

    $service = MetastoreService::create($container->getMock());

    $this->assertEquals([$expected], $service->getAll("dataset"));
  }

  /**
   * Test getAll() with an exception.
   *
   * @covers ::getAll
   */
  public function testGetAllException() {
    // Add a logger we can assert against.
    $logger = new TestLogger();

    $container = self::getCommonMockChain($this, NULL, $logger)
      ->add(NodeData::class, 'retrieveAll', [json_encode(['foo' => 'bar'])])
      ->add(ValidMetadataFactory::class, 'get', new \Exception())
      ->getMock();

    \Drupal::setContainer($container);
    $service = MetastoreService::create($container);

    $service->getAll('dataset');

    $this->assertTrue(
      $logger->hasErrorThatContains('A JSON string failed validation.')
    );
  }

  /**
   * @covers ::post
   */
  public function testPost() {
    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, 'store', '1');

    $service = MetastoreService::create($container->getMock());

    $data = $this->validMetadataFactory->get(json_encode(['foo' => 'bar']), 'dataset');
    $this->assertEquals("1", $service->post("dataset", $data));
  }

  /**
   * @covers ::post
   */
  public function testPostAlreadyExisting() {
    $container = self::getCommonMockChain($this)
      ->add(Data::class, "retrieve", "1");

    $service = MetastoreService::create($container->getMock());

    $this->expectException(ExistingObjectException::class);

    $data = $this->validMetadataFactory->get('{"identifier":1,"title":"FooBar"}', 'dataset');
    $service->post("dataset", $data);
  }

  /**
   * @covers ::put
   */
  public function testPut() {
    $existing = '{"identifier":"1","title":"Foo"}';
    $updating = '{"identifier":"1","title":"Bar"}';

    $data_existing = $this->validMetadataFactory->get($existing, 'dataset');
    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, "retrieve", $existing)
      ->add(NodeData::class, "store", "1")
      ->add(ValidMetadataFactory::class, 'get', $data_existing);

    $service = MetastoreService::create($container->getMock());

    $data_updating = $this->validMetadataFactory->get($updating, 'dataset');
    $info = $service->put("dataset", "1", $data_updating);

    $this->assertEquals("1", $info['identifier']);
  }

  /**
   * @covers ::put
   */
  public function testPutModifyIdentifierException() {
    $existing = '{"identifier":"1","title":"Foo"}';
    $updating = '{"identifier":"2","title":"Bar"}';

    $container = self::getCommonMockChain($this)
      ->add(Data::class, "retrieve", $existing);

    $service = MetastoreService::create($container->getMock());

    $this->expectExceptionMessage("Identifier cannot be modified");

    $data = $this->validMetadataFactory->get($updating, 'dataset');
    $service->put("dataset", "1", $data);
  }

  /**
   * @covers ::put
   */
  public function testPutResultingInNewData() {
    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, "retrieve", new \Exception())
      ->add(NodeData::class, "store", "3");

    $service = MetastoreService::create($container->getMock());

    $data = $this->validMetadataFactory->get('{"identifier":"3","title":"FooBar"}', 'dataset');
    $info = $service->put("dataset", "3", $data);
    $this->assertEquals("3", $info['identifier']);
  }

  /**
   * @covers ::put
   */
  public function testPutObjectUnchangedException() {
    $existing = '{"identifier":"1","title":"Foo"}';

    $data = $this->validMetadataFactory->get($existing, 'dataset');
    $container = self::getCommonMockChain($this)
      ->add(Data::class, "retrieve", $existing)
      ->add(ValidMetadataFactory::class, 'get', $data);

    $service = MetastoreService::create($container->getMock());
    $this->expectException(UnmodifiedObjectException::class);

    $service->put("dataset", "1", $data);
  }

  /**
   * @covers ::put
   */
  public function testPutEquivalentDataObjectUnchangedException() {
    $existing = '{"identifier":"1","title":"Foo"}';
    $updating = <<<EOF
      {
        "title":"Foo",
        "identifier":"1"
      }
EOF;

    $data_existing = $this->validMetadataFactory->get($existing, 'dataset');
    $container = self::getCommonMockChain($this)
      ->add(Data::class, "retrieve", $existing)
      ->add(ValidMetadataFactory::class, 'get', $data_existing);

    $service = MetastoreService::create($container->getMock());
    $this->expectException(UnmodifiedObjectException::class);

    $data_updating = $this->validMetadataFactory->get($updating, 'dataset');
    $service->put("dataset", "1", $data_updating);
  }

  /**
   * Test the patch method.
   *
   * @covers ::patch
   */
  public function testPatch() {
    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, "retrieve", "1")
      ->add(NodeData::class, "store", "1")
      ->add(ValidMetadataFactory::class, 'get', new RootedJsonData('{"id":"1"}'));

    $service = MetastoreService::create($container->getMock());

    $this->assertEquals("1", $service->patch("dataset", "1", json_encode("blah")));
  }

  /**
   * Test patch() method with a missing object exception.
   *
   * @covers ::patch
   */
  public function testPatchObjectNotFoundException() {
    $data = '{"identifier":"1","title":"FooBar"}';

    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, "retrieve", new \Exception());

    $service = MetastoreService::create($container->getMock());
    $this->expectException(MissingObjectException::class);
    $service->patch("dataset", "1", $data);
  }

  /**
   * Test the publish () method.
   *
   * @covers ::publish
   */
  public function testPublish() {
    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, "retrieve", "1")
      ->add(NodeData::class, "publish", TRUE);

    $service = MetastoreService::create($container->getMock());
    $result = $service->publish('dataset', 1);
    $this->assertTrue($result);
  }

  /**
   * Test the archive method.
   *
   * @covers ::archive
   */
  public function testArchive() {
    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, "retrieve", "1")
      ->add(NodeData::class, "archive", TRUE);

    $service = MetastoreService::create($container->getMock());
    $result = $service->archive('dataset', 1);
    $this->assertTrue($result);
  }

  /**
   * Test publish() method with a missing object exception.
   *
   * @covers ::publish
   */
  public function testPublishMissingObjectExpection() {
    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, "retrieve", new \Exception());

    $service = MetastoreService::create($container->getMock());

    $this->expectException(MissingObjectException::class);
    $service->publish('dataset', "foobar");
  }

  /**
   * Test \Drupal\dkan_metastore\Service::count() method.
   *
   * @covers ::count
   */
  public function testCount(): void {
    // Set constant which should be returned by the ::count() method.
    $count = 5;

    // Create mock chain for testing ::count() method.
    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, 'count', $count);

    // Create metastore service object.
    $service = MetastoreService::create($container->getMock());
    // Ensure count matches return value.
    $this->assertEquals($count, $service->count('test'));
  }

  /**
   * Test \Drupal\dkan_metastore\Service::getIdentifiers() method.
   *
   * @covers ::getIdentifiers
   */
  public function testGetIdentifiers(): void {
    // Set constant which should be returned by the ::getIdentifiers() method.
    $uuids = ['a', 'b', 'c'];

    // Create mock chain for testing ::getIdentifiers() method.
    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, 'retrieveIds', $uuids);

    // Create metastore service object.
    $service = MetastoreService::create($container->getMock());
    // Ensure count matches return value.
    $this->assertEquals($uuids, $service->getIdentifiers('test', 1, 5));
  }

  /**
   * @covers ::delete
   */
  public function testDelete() {
    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, "retrieve", "1")
      ->add(NodeData::class, "remove", "1");

    $service = MetastoreService::create($container->getMock());

    $this->assertEquals("1", $service->delete("dataset", "1"));
  }

  /**
   * @covers ::getCatalog
   */
  public function testGetCatalog() {
    $dataset = $this->validMetadataFactory->get(json_encode(["foo" => "bar"]), 'blah');

    $catalog = (object) [
      "@id" => "http://catalog",
      "dataset" => [],
    ];

    $container = self::getCommonMockChain($this)
      ->add(SchemaRetriever::class, "retrieve", json_encode($catalog))
      ->add(NodeData::class, 'retrieveAll', [json_encode($dataset), json_encode($dataset)])
      ->add(ValidMetadataFactory::class, 'get', $dataset);

    \Drupal::setContainer($container->getMock());

    $service = MetastoreService::create($container->getMock());
    $catalog->dataset = [
      (object) $dataset->{'$'},
      (object) $dataset->{'$'},
    ];
    $this->assertEquals($catalog, $service->getCatalog());
  }

  /**
   * Get a common container mock chain for testing.
   *
   * @return \MockChain\Chain
   *   A MockChain container.
   */
  public static function getCommonMockChain(TestCase $case, ?Options $services = NULL, $logger = NULL) {

    $options = (new Options)
      ->add('dkan.metastore.schema_retriever', SchemaRetriever::class)
      ->add('dkan.metastore.storage', DataFactory::class)
      ->add('event_dispatcher', EventDispatcher::class)
      ->add('dkan.metastore.valid_metadata', ValidMetadataFactory::class)
      ->add('dkan.common.logger_channel', LoggerChannelInterface::class)
      ->index(0);

    if ($logger) {
      $options->add('dkan.common.logger_channel', $logger);
    }

    return (new Chain($case))
      ->add(Container::class, "get", $options)
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      // ->add(NodeData::class, 'getDefaultModerationState', 'published')
      ->add(NodeData::class, 'retrieve', '{"data":"somedata"}')
      ->add(SchemaRetriever::class, "retrieve", json_encode(['foo' => 'bar']));
  }

  public static function getValidMetadataFactory(TestCase $case) {
    $options = (new Options())
      ->add('dkan.metastore.schema_retriever', SchemaRetriever::class)
      ->index(0);

    $container = (new Chain($case))
      ->add(Container::class, "get", $options)
      ->add(SchemaRetriever::class, "retrieve", json_encode(['foo' => 'bar']));

    return ValidMetadataFactory::create($container->getMock());
  }

  /**
   * Test removeReferences() method.
   *
   * @covers ::removeReferences
   * @covers ::removeReferncesRecursive
   */
  public function testRemoveReferences() {
    $input = new RootedJsonData(json_encode((object) [
      "distribution" => [
        (object) [
          "downloadURL" => "http://example.com/file.csv",
          "%Ref:downloadURL" => ["foo" => "bar"],
          "describedBy" => (object) [
            "downloadURL" => "http://example.com/schema.json",
            "%Ref:downloadURL" => ["foo" => "bar"],
          ],
        ],
      ],
      "%Ref:distribution" => [
        "foo" => "bar",
      ],
      "%modified" => "2024-06-05T00:00:00Z",
    ]));

    $expected = new RootedJsonData(json_encode([
      "distribution" => [
        (object) [
          "downloadURL" => "http://example.com/file.csv",
          "describedBy" => (object) [
            "downloadURL" => "http://example.com/schema.json",
          ],
        ],
      ],
      "%modified" => "2024-06-05T00:00:00Z",
    ]));

    $this->assertEquals($expected, MetastoreService::removeReferences($input));
  }

}
