<?php

namespace Drupal\Tests\metastore\Unit;

use Drupal\common\Events\Event;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\DependencyInjection\Container;
use Drupal\metastore\Exception\ExistingObjectException;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Exception\UnmodifiedObjectException;
use Drupal\metastore\RootedJsonDataFactory;
use Drupal\metastore\Service;
use Drupal\metastore\SchemaRetriever;
use Drupal\metastore\Storage\Data;
use Drupal\metastore\Storage\DataFactory;
use MockChain\Chain;
use MockChain\Options;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;
use RootedData\RootedJsonData;

/**
 *
 */
class ServiceTest extends TestCase {

  /**
   * The RootedJsonDataFactory class used for testing.
   *
   * @var \Drupal\metastore\RootedJsonDataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $rootedJsonDataFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->rootedJsonDataFactory = self::getJsonWrapper($this);
  }

  /**
   *
   */
  public function testGetSchemas() {
    $container = self::getCommonMockChain($this)
      ->add(SchemaRetriever::class, "getAllIds", ["1"]);

    $service = Service::create($container->getMock());
    $this->assertEquals(json_encode(["1" => ['foo' => 'bar']]), json_encode($service->getSchemas()));
  }

  /**
   *
   */
  public function testGetSchema() {
    $container = self::getCommonMockChain($this);

    $service = Service::create($container->getMock());
    $this->assertEquals(json_encode(['foo' => 'bar']), json_encode($service->getSchema("1")));
  }

  /**
   *
   */
  public function testGetAll() {
    $expected = $this->rootedJsonDataFactory->createRootedJsonData('dataset', json_encode(['foo' => 'bar']));

    $container = self::getCommonMockChain($this)
      ->add(Data::class, 'retrieveAll', [json_encode(['foo' => 'bar'])])
      ->add(RootedJsonDataFactory::class, 'createRootedJsonData', $expected);

    \Drupal::setContainer($container->getMock());

    $service = Service::create($container->getMock());

    $this->assertEquals([$expected], $service->getAll("dataset"));
  }

  public function testGetAllException() {
    $data = $this->rootedJsonDataFactory->createRootedJsonData('dataset', json_encode(['foo' => 'bar']));

    $event = new Event($data);
    $event->setException(new \Exception("blah"));

    $event2 = new Event($data);
    $event2->setData([$data]);

    $sequence = (new Sequence())
      ->add($event)
      ->add($event2);

    $container = self::getCommonMockChain($this)
      ->add(Data::class, 'retrieveAll', [json_encode(['foo' => 'bar'])])
      ->add(RootedJsonDataFactory::class, 'createRootedJsonData', $data)
      ->add(ContainerAwareEventDispatcher::class, 'dispatch', $sequence);

    \Drupal::setContainer($container->getMock());

    $service = Service::create($container->getMock());

    $this->assertEquals(
      json_encode([$data]),
      json_encode($service->getAll("dataset"))
    );
  }

  /**
   *
   */
  public function testGet() {
    $data = $this->rootedJsonDataFactory->createRootedJsonData('dataset', json_encode(['foo' => 'bar']));

    $container = self::getCommonMockChain($this)
      ->add(Data::class, "retrievePublished", json_encode(['foo' => 'bar']))
      ->add(RootedJsonDataFactory::class, 'createRootedJsonData', $data);

    \Drupal::setContainer($container->getMock());

    $service = Service::create($container->getMock());

    $this->assertEquals(json_encode(['foo' => 'bar']), $service->get("dataset", "1"));
  }

  /**
   *
   */
  public function testGetResources() {
    $dataset = [
      "identifier" => "1",
      "distribution" => [
        ["title" => "hello"],
      ],
    ];
    $data = $this->rootedJsonDataFactory->createRootedJsonData('dataset', json_encode($dataset));

    $container = self::getCommonMockChain($this)
      ->add(Data::class, "retrieve", json_encode($dataset))
      ->add(RootedJsonDataFactory::class, 'createRootedJsonData', $data);

    $service = Service::create($container->getMock());

    $this->assertEquals(json_encode([["title" => "hello"]]),
      json_encode($service->getResources("dataset", "1")));
  }

  /**
   *
   */
  public function testPost() {
    $container = self::getCommonMockChain($this)
      ->add(Data::class, 'store', '1');

    $service = Service::create($container->getMock());

    $data = $this->rootedJsonDataFactory->createRootedJsonData('dataset', json_encode(['foo' => 'bar']));
    $this->assertEquals("1", $service->post("dataset", $data));
  }

  /**
   *
   */
  public function testPostAlreadyExisting() {
    $container = self::getCommonMockChain($this)
      ->add(Data::class, "retrieve", "1");

    $service = Service::create($container->getMock());

    $this->expectException(ExistingObjectException::class);

    $data = $this->rootedJsonDataFactory->createRootedJsonData('dataset', '{"identifier":1,"title":"FooBar"}');
    $service->post("dataset", $data);
  }

  /**
   *
   */
  public function testPut() {
    $container = self::getCommonMockChain($this)
      ->add(Data::class, "retrieve", "1")
      ->add(Data::class, "store", "1");

    $service = Service::create($container->getMock());

    $data = $this->rootedJsonDataFactory->createRootedJsonData('dataset', json_encode(['foo' => 'bar']));
    $info = $service->put("dataset", "1", $data);

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

    $service = Service::create($container->getMock());

    $this->expectExceptionMessage("Identifier cannot be modified");

    $data = $this->rootedJsonDataFactory->createRootedJsonData('dataset', $updating);
    $service->put("dataset", "1", $data);
  }

  /**
   *
   */
  public function testPutResultingInNewData() {
    $container = self::getCommonMockChain($this)
      ->add(Data::class, "retrieve", new \Exception())
      ->add(Data::class, "store", "3");

    $service = Service::create($container->getMock());

    $data = $this->rootedJsonDataFactory->createRootedJsonData('dataset', '{"identifier":"3","title":"FooBar"}');
    $info = $service->put("dataset", "3", $data);
    $this->assertEquals("3", $info['identifier']);
  }

  /**
   *
   */
  public function testPutObjectUnchangedException() {
    $existing = '{"identifier":"1","title":"Foo"}';

    $container = self::getCommonMockChain($this)
      ->add(Data::class, "retrieve", $existing);

    $service = Service::create($container->getMock());
    $this->expectException(UnmodifiedObjectException::class);

    $data = $this->rootedJsonDataFactory->createRootedJsonData('dataset', $existing);
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

    $container = self::getCommonMockChain($this)
      ->add(Data::class, "retrieve", $existing);

    $service = Service::create($container->getMock());
    $this->expectException(UnmodifiedObjectException::class);

    $data = $this->rootedJsonDataFactory->createRootedJsonData('dataset', $updating);
    $service->put("dataset", "1", $data);
  }

  /**
   *
   */
  public function testPatch() {
    $container = self::getCommonMockChain($this)
      ->add(Data::class, "retrieve", "1")
      ->add(Data::class, "store", "1")
      ->add(RootedJsonDataFactory::class, 'createRootedJsonData', RootedJsonData::class);

    $service = Service::create($container->getMock());

    $this->assertEquals("1", $service->patch("dataset", "1", json_encode("blah")));
  }

  /**
   *
   */
  public function testPatchObjectNotFoundException() {
    $data = '{"identifier":"1","title":"FooBar"}';

    $container = self::getCommonMockChain($this)
      ->add(Data::class, "retrieve", new \Exception());

    $service = Service::create($container->getMock());
    $this->expectException(MissingObjectException::class);
    $service->patch("dataset", "1", $data);
  }

  /**
   *
   */
  public function testPublish() {
    $container = self::getCommonMockChain($this)
      ->add(Data::class, "retrieve", "1")
      ->add(Data::class, "publish", "1");

    $service = Service::create($container->getMock());
    $result = $service->publish('dataset', 1);
    $this->assertEquals("1", $result);
  }

  /**
   *
   */
  public function testPublishMissingObjectExpection() {
    $container = self::getCommonMockChain($this)
      ->add(Data::class, "retrieve", new \Exception());

    $service = Service::create($container->getMock());

    $this->expectException(MissingObjectException::class);
    $service->publish('dataset', "foobar");
  }

  /**
   *
   */
  public function testDelete() {
    $container = self::getCommonMockChain($this)
      ->add(Data::class, "retrieve", "1")
      ->add(Data::class, "remove", "1");

    $service = Service::create($container->getMock());

    $this->assertEquals("1", $service->delete("dataset", "1"));
  }

  /**
   *
   */
  public function testGetCatalog() {
    $dataset = $this->rootedJsonDataFactory->createRootedJsonData('blah', json_encode(["foo" => "bar"]));

    $catalog = (object) [
      "@id" => "http://catalog",
      "dataset" => [],
    ];

    $container = self::getCommonMockChain($this)
      ->add(SchemaRetriever::class, "retrieve", json_encode($catalog))
      ->add(Data::class, 'retrieveAll', [json_encode($dataset), json_encode($dataset)])
      ->add(RootedJsonDataFactory::class, 'createRootedJsonData', $dataset);

    \Drupal::setContainer($container->getMock());

    $service = Service::create($container->getMock());
    $catalog->dataset = [
      $dataset,
      $dataset,
    ];
    $this->assertEquals($catalog, $service->getCatalog());
  }

  /**
   * @return \Drupal\common\Tests\Mock\Chain
   */
  public static function getCommonMockChain(TestCase $case, Options $services = null) {
    if (!$services) {
      $services = new Options();
    }

    $myServices = [
      'dkan.metastore.schema_retriever' => SchemaRetriever::class,
      'dkan.metastore.storage' => DataFactory::class,
      'dkan.metastore.rooted_json_data_wrapper' => RootedJsonDataFactory::class,
      'event_dispatcher' => ContainerAwareEventDispatcher::class
    ];

    foreach ($myServices as $serviceName => $class) {
      $serviceClass = $services->return($serviceName);
      if (!isset($serviceClass)) {
        $services->add($serviceName, $class);
      }
    }

    $services->index(0);

    return (new Chain($case))
      ->add(Container::class, "get", $services)
      ->add(DataFactory::class, 'getInstance', Data::class)
      ->add(SchemaRetriever::class, "retrieve", json_encode(['foo' => 'bar']));
  }

  public static function getJsonWrapper(TestCase $case) {
    $options = (new Options())
      ->add('metastore.schema_retriever', SchemaRetriever::class)
      ->index(0);

    $container = (new Chain($case))
      ->add(Container::class, "get", $options)
      ->add(SchemaRetriever::class, "retrieve", json_encode(['foo' => 'bar']));

    return RootedJsonDataFactory::create($container->getMock());
  }

}
