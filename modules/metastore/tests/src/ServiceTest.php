<?php

namespace Drupal\Tests\metastore\Unit;

use Drupal\Core\DependencyInjection\Container;
use Drupal\metastore\Exception\ExistingObjectException;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Exception\UnmodifiedObjectException;
use Drupal\metastore\Factory\Sae;
use Drupal\metastore\Service;
use Drupal\metastore\FileSchemaRetriever;
use Drupal\metastore\Storage\AbstractEntityStorage;
use Drupal\metastore\Storage\NodeStorageFactory;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class ServiceTest extends TestCase {

  /**
   *
   */
  public function testGetSchemas() {
    $container = $this->getCommonMockChain()
      ->add(FileSchemaRetriever::class, "getAllIds", ["1"]);

    $service = Service::create($container->getMock());
    $this->assertEquals(json_encode(["1" => ['foo' => 'bar']]), json_encode($service->getSchemas()));
  }

  /**
   *
   */
  public function testGetSchema() {
    $container = $this->getCommonMockChain();

    $service = Service::create($container->getMock());
    $this->assertEquals(json_encode(['foo' => 'bar']), json_encode($service->getSchema("1")));
  }

  /**
   *
   */
  public function testGetAll() {
    $container = $this->getCommonMockChain()
      ->add(AbstractEntityStorage::class, 'retrieveAll', [json_encode(['foo' => 'bar'])]);

    $service = Service::create($container->getMock());

    $expected = $service->jsonStringToRootedJsonData('blah', json_encode(['foo' => 'bar']));
    $this->assertEquals([$expected], $service->getAll("dataset"));
  }

  /**
   *
   */
  public function testGet() {
    $container = $this->getCommonMockChain()
      ->add(AbstractEntityStorage::class, "retrievePublished", json_encode("blah"));

    $service = Service::create($container->getMock());

    $this->assertEquals(json_encode("blah"), $service->get("dataset", "1"));
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

    $container = $this->getCommonMockChain()
      ->add(AbstractEntityStorage::class, "retrieve", json_encode($dataset));

    $service = Service::create($container->getMock());

    $this->assertEquals(json_encode([["title" => "hello"]]),
      json_encode($service->getResources("dataset", "1")));
  }

  /**
   *
   */
  public function testPost() {
    $container = $this->getCommonMockChain()
      ->add(AbstractEntityStorage::class, 'store', '1');

    $service = Service::create($container->getMock());

    $data = $service->jsonStringToRootedJsonData('blah', json_encode(['foo' => 'bar']));
    $this->assertEquals("1", $service->post("dataset", $data));
  }

  /**
   *
   */
  public function testPostAlreadyExisting() {
    $container = $this->getCommonMockChain()
      ->add(AbstractEntityStorage::class, "retrieve", "1");

    $service = Service::create($container->getMock());

    $this->expectException(ExistingObjectException::class);

    $data = $service->jsonStringToRootedJsonData('blah', '{"identifier":1,"title":"FooBar"}');
    $service->post("dataset", $data);
  }

  /**
   *
   */
  public function testPut() {
    $container = $this->getCommonMockChain()
      ->add(AbstractEntityStorage::class, "retrieve", "1")
      ->add(AbstractEntityStorage::class, "store", "1");

    $service = Service::create($container->getMock());

    $data = $service->jsonStringToRootedJsonData('blah', json_encode(['foo' => 'bar']));
    $info = $service->put("dataset", "1", $data);

    $this->assertEquals("1", $info['identifier']);
  }

  /**
   *
   */
  public function testPutModifyIdentifierException() {
    $existing = '{"identifier":"1","title":"Foo"}';
    $updating = '{"identifier":"2","title":"Bar"}';

    $container = $this->getCommonMockChain()
      ->add(AbstractEntityStorage::class, "retrieve", $existing);

    $service = Service::create($container->getMock());

    $this->expectExceptionMessage("Identifier cannot be modified");

    $data = $service->jsonStringToRootedJsonData('blah', $updating);
    $service->put("dataset", "1", $data);
  }

  /**
   *
   */
  public function testPutResultingInNewData() {
    $data = '{"identifier":"3","title":"FooBar"}';
    $container = $this->getCommonMockChain()
      ->add(AbstractEntityStorage::class, "retrieve", new \Exception())
      ->add(AbstractEntityStorage::class, "store", "3");

    $service = Service::create($container->getMock());

    $data = $service->jsonStringToRootedJsonData('blah', $data);
    $info = $service->put("dataset", "3", $data);
    $this->assertEquals("3", $info['identifier']);
  }

  /**
   *
   */
  public function testPutObjectUnchangedException() {
    $existing = '{"identifier":"1","title":"Foo"}';

    $container = $this->getCommonMockChain()
      ->add(AbstractEntityStorage::class, "retrieve", $existing);

    $service = Service::create($container->getMock());
    $this->expectException(UnmodifiedObjectException::class);

    $data = $service->jsonStringToRootedJsonData('blah', $existing);
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

    $container = $this->getCommonMockChain()
      ->add(AbstractEntityStorage::class, "retrieve", $existing);

    $service = Service::create($container->getMock());
    $this->expectException(UnmodifiedObjectException::class);

    $data = $service->jsonStringToRootedJsonData('blah', $updating);
    $service->put("dataset", "1", $data);
  }

  /**
   *
   */
  public function testPatch() {
    $container = $this->getCommonMockChain()
      ->add(AbstractEntityStorage::class, "retrieve", "1")
      ->add(AbstractEntityStorage::class, "store", "1");

    $service = Service::create($container->getMock());

    $data = $service->jsonStringToRootedJsonData('blah', json_encode(['foo' => 'bar']));
    $this->assertEquals("1", $service->patch("dataset", "1", $data));
  }

  /**
   *
   */
  public function testPatchObjectNotFoundException() {
    $data = '{"identifier":"1","title":"FooBar"}';

    $container = $this->getCommonMockChain()
      ->add(AbstractEntityStorage::class, "retrieve", new \Exception());

    $service = Service::create($container->getMock());
    $this->expectException(MissingObjectException::class);

    $data = $service->jsonStringToRootedJsonData('blah', $data);
    $service->patch("dataset", "1", $data);
  }

  /**
   *
   */
  public function testPublish() {
    $container = $this->getCommonMockChain()
      ->add(AbstractEntityStorage::class, "retrieve", "1")
      ->add(AbstractEntityStorage::class, "publish", "1");

    $service = Service::create($container->getMock());
    $result = $service->publish('dataset', 1);
    $this->assertEquals("1", $result);
  }

  /**
   *
   */
  public function testPublishMissingObjectExpection() {
    $container = $this->getCommonMockChain()
      ->add(AbstractEntityStorage::class, "retrieve", new \Exception());

    $service = Service::create($container->getMock());

    $this->expectException(MissingObjectException::class);
    $service->publish('dataset', "foobar");
  }

  /**
   *
   */
  public function testDelete() {
    $container = $this->getCommonMockChain()
      ->add(AbstractEntityStorage::class, "retrieve", "1")
      ->add(AbstractEntityStorage::class, "remove", "1");

    $service = Service::create($container->getMock());

    $this->assertEquals("1", $service->delete("dataset", "1"));
  }

  /**
   *
   */
  public function testGetCatalog() {
    $catalog = (object) [
      "@id" => "http://catalog",
      "dataset" => [],
    ];

    $container = $this->getCommonMockChain()
      ->add(FileSchemaRetriever::class, "retrieve", json_encode($catalog))
      ->add(AbstractEntityStorage::class, 'retrieveAll', [json_encode(["foo" => "bar"]), json_encode(["foo" => "bar"])]);

    $service = Service::create($container->getMock());
    $dataset = $service->jsonStringToRootedJsonData('blah', json_encode(["foo" => "bar"]));
    $catalog->dataset = [
      $dataset,
      $dataset,
    ];
    $this->assertEquals($catalog, $service->getCatalog());
  }

  /**
   * @return \Drupal\common\Tests\Mock\Chain
   */
  public function getCommonMockChain() {
    $options = (new Options())
      ->add('metastore.schema_retriever', FileSchemaRetriever::class)
      ->add('dkan.metastore.storage', NodeStorageFactory::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(NodeStorageFactory::class, 'getInstance', AbstractEntityStorage::class)
      ->add(FileSchemaRetriever::class, "retrieve", json_encode(['foo' => 'bar']));
  }

}
