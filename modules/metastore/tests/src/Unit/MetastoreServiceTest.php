<?php

namespace Drupal\Tests\metastore\Unit;

use Drupal\common\Events\Event;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Component\DependencyInjection\Container;
use Drupal\metastore\Exception\ExistingObjectException;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Exception\UnmodifiedObjectException;
use Drupal\metastore\ValidMetadataFactory;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\SchemaRetriever;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\MetastoreStorageInterface;
use Drupal\metastore\Storage\NodeData;

use MockChain\Chain;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;
use MockChain\Options;
use RootedData\RootedJsonData;

/**
 * @coversDefaultClass Drupal\metastore\MetastoreService
 */
class MetastoreServiceTest extends TestCase {

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->validMetadataFactory = self::getValidMetadataFactory($this);
  }

  /**
   * Test \Drupal\metastore\Service::isPublished() method.
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
   *
   */
  public function testGetSchemas() {
    $container = self::getCommonMockChain($this)
      ->add(SchemaRetriever::class, "getAllIds", ["1"]);

    $service = MetastoreService::create($container->getMock());
    $this->assertEquals(json_encode(["1" => ['foo' => 'bar']]), json_encode($service->getSchemas()));
  }

  /**
   *
   */
  public function testGetSchema() {
    $container = self::getCommonMockChain($this);

    $service = MetastoreService::create($container->getMock());
    $this->assertEquals(json_encode(['foo' => 'bar']), json_encode($service->getSchema("1")));
  }

  /**
   *
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

  public function testGetAllException() {
    $data = $this->validMetadataFactory->get(json_encode(['foo' => 'bar']), 'dataset');

    $event = new Event($data);
    $event->setException(new \Exception("blah"));

    $event2 = new Event($data);
    $event2->setData([$data]);

    $sequence = (new Sequence())
      ->add($event)
      ->add($event2);

    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, 'retrieveAll', [json_encode(['foo' => 'bar'])])
      ->add(ValidMetadataFactory::class, 'get', $data)
      ->add(ContainerAwareEventDispatcher::class, 'dispatch', $sequence);

    \Drupal::setContainer($container->getMock());

    $service = MetastoreService::create($container->getMock());

    $this->assertEquals(
      json_encode([$data]),
      json_encode($service->getAll("dataset"))
    );
  }

  /**
   *
   */
  public function testPost() {
    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, 'store', '1');

    $service = MetastoreService::create($container->getMock());

    $data = $this->validMetadataFactory->get(json_encode(['foo' => 'bar']), 'dataset');
    $this->assertEquals("1", $service->post("dataset", $data));
  }

  /**
   *
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
   *
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
   *
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
   *
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
   *
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
   *
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
   *
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
   *
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
   *
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
   *
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
   *
   */
  public function testPublishMissingObjectExpection() {
    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, "retrieve", new \Exception());

    $service = MetastoreService::create($container->getMock());

    $this->expectException(MissingObjectException::class);
    $service->publish('dataset', "foobar");
  }

  /**
   * Test \Drupal\metastore\Service::count() method.
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
   * Test \Drupal\metastore\Service::getIdentifiers() method.
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
   *
   */
  public function testDelete() {
    $container = self::getCommonMockChain($this)
      ->add(NodeData::class, "retrieve", "1")
      ->add(NodeData::class, "remove", "1");

    $service = MetastoreService::create($container->getMock());

    $this->assertEquals("1", $service->delete("dataset", "1"));
  }

  /**
   *
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
   * @return \Drupal\common\Tests\Mock\Chain
   */
  public static function getCommonMockChain(TestCase $case, Options $services = null) {

    $options = (new Options)
      ->add('dkan.metastore.schema_retriever', SchemaRetriever::class)
      ->add('dkan.metastore.storage', DataFactory::class)
      ->add('event_dispatcher', ContainerAwareEventDispatcher::class)
      ->add('dkan.metastore.valid_metadata', ValidMetadataFactory::class)
      ->index(0);

    return (new Chain($case))
      ->add(Container::class, "get", $options)
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      // ->add(NodeData::class, 'getDefaultModerationState', 'published')
      ->add(NodeData::class, 'retrieve', '{"data":"somedata"}')
      ->add(SchemaRetriever::class, "retrieve", json_encode(['foo' => 'bar']));
  }

  public static function getValidMetadataFactory(TestCase $case) {
    $options = (new Options())
      ->add('metastore.schema_retriever', SchemaRetriever::class)
      ->index(0);

    $container = (new Chain($case))
      ->add(Container::class, "get", $options)
      ->add(SchemaRetriever::class, "retrieve", json_encode(['foo' => 'bar']));

    return ValidMetadataFactory::create($container->getMock());
  }

}
