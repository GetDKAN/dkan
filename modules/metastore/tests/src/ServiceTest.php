<?php

namespace Drupal\Tests\metastore\Unit;

use Drupal\Core\DependencyInjection\Container;
use Drupal\metastore\Exception\ExistingObjectException;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Exception\UnmodifiedObjectException;
use Drupal\metastore\Factory\Sae;
use Drupal\metastore\Service;
use Drupal\metastore\SchemaRetriever;
use Drupal\metastore\Storage\Data;
use Drupal\metastore\Storage\DataFactory;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Sae\Sae as Engine;

/**
 *
 */
class ServiceTest extends TestCase {

  /**
   *
   */
  public function testGetSchemas() {
    $container = $this->getCommonMockChain()
      ->add(SchemaRetriever::class, "getAllIds", ["1"]);

    $service = Service::create($container->getMock());
    $this->assertEquals(json_encode(["1" => "blah"]), json_encode($service->getSchemas()));
  }

  /**
   *
   */
  public function testGetSchema() {
    $container = $this->getCommonMockChain();

    $service = Service::create($container->getMock());
    $this->assertEquals(json_encode("blah"), json_encode($service->getSchema("1")));
  }

  /**
   *
   */
  public function testGetAll() {
    $container = $this->getCommonMockChain()
      ->add(Sae::class, "getInstance", Engine::class)
      ->add(Engine::class, "get", [json_encode("blah")]);

    $service = Service::create($container->getMock());

    $this->assertEquals(json_encode(["blah"]), json_encode($service->getAll("dataset")));
  }

  /**
   *
   */
  public function testGet() {
    $container = $this->getCommonMockChain()
      ->add(Data::class, "retrievePublished", json_encode("blah"));

    $service = Service::create($container->getMock());

    $this->assertEquals(json_encode("blah"), $service->get("dataset", "1"));
  }

  /**
   *
   */
  public function testGetResources() {
    $dataset = (object) [
      "identifier" => "1",
      "distribution" => [
        (object) ["title" => "hello"],
      ],
    ];

    $container = $this->getCommonMockChain()
      ->add(Sae::class, "getInstance", Engine::class)
      ->add(Engine::class, "get", json_encode($dataset));

    $service = Service::create($container->getMock());

    $this->assertEquals(json_encode([(object) ["title" => "hello"]]),
      json_encode($service->getResources("dataset", "1")));
  }

  /**
   *
   */
  public function testPost() {
    $container = $this->getCommonMockChain()
      ->add(Sae::class, "getInstance", Engine::class)
      ->add(Engine::class, "post", "1");

    $service = Service::create($container->getMock());

    $this->assertEquals("1", $service->post("dataset", json_encode("blah")));
  }

  /**
   *
   */
  public function testPostAlreadyExisting() {
    $container = $this->getCommonMockChain()
      ->add(Sae::class, "getInstance", Engine::class)
      ->add(Engine::class, "get", "1");

    $service = Service::create($container->getMock());

    $this->expectException(ExistingObjectException::class);
    $service->post("dataset", '{"identifier":1,"title":"FooBar"}');
  }

  /**
   *
   */
  public function testPut() {
    $container = $this->getCommonMockChain()
      ->add(Sae::class, "getInstance", Engine::class)
      ->add(Engine::class, "put", "1")
      ->add(Engine::class, "get", "1");

    $service = Service::create($container->getMock());

    $info = $service->put("dataset", "1", json_encode("blah"));
    $this->assertEquals("1", $info['identifier']);
  }

  /**
   *
   */
  public function testPutModifyIdentifierException() {
    $existing = '{"identifier":"1","title":"Foo"}';
    $updating = '{"identifier":"2","title":"Bar"}';

    $container = $this->getCommonMockChain()
      ->add(Sae::class, "getInstance", Engine::class)
      ->add(Engine::class, "get", $existing);

    $service = Service::create($container->getMock());

    $this->expectExceptionMessage("Identifier cannot be modified");
    $service->put("dataset", "1", $updating);
  }

  /**
   *
   */
  public function testPutResultingInNewData() {
    $data = '{"identifier":"3","title":"FooBar"}';
    $container = $this->getCommonMockChain()
      ->add(Sae::class, "getInstance", Engine::class)
      ->add(Engine::class, "get", new \Exception())
      ->add(Engine::class, "put", "3")
      ->add(Engine::class, "post", "3");

    $service = Service::create($container->getMock());
    $info = $service->put("dataset", "3", $data);
    $this->assertEquals("3", $info['identifier']);
  }

  /**
   *
   */
  public function testPutObjectUnchangedException() {
    $existing = '{"identifier":"1","title":"Foo"}';

    $container = $this->getCommonMockChain()
      ->add(Sae::class, "getInstance", Engine::class)
      ->add(Engine::class, "get", $existing);

    $service = Service::create($container->getMock());
    $this->expectException(UnmodifiedObjectException::class);
    $service->put("dataset", "1", $existing);
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
      ->add(Sae::class, "getInstance", Engine::class)
      ->add(Engine::class, "get", $existing);

    $service = Service::create($container->getMock());
    $this->expectException(UnmodifiedObjectException::class);
    $service->put("dataset", "1", $updating);
  }

  /**
   *
   */
  public function testPatch() {
    $container = $this->getCommonMockChain()
      ->add(Sae::class, "getInstance", Engine::class)
      ->add(Engine::class, "patch", "1")
      ->add(Engine::class, "get", "1");

    $service = Service::create($container->getMock());

    $this->assertEquals("1", $service->patch("dataset", "1", json_encode("blah")));
  }

  /**
   *
   */
  public function testPatchObjectNotFoundException() {
    $data = '{"identifier":"1","title":"FooBar"}';

    $container = $this->getCommonMockChain()
      ->add(Sae::class, "getInstance", Engine::class)
      ->add(Engine::class, "get", new \Exception());

    $service = Service::create($container->getMock());
    $this->expectException(MissingObjectException::class);
    $service->patch("dataset", "1", $data);
  }

  /**
   *
   */
  public function testPublish() {
    $container = $this->getCommonMockChain()
      ->add(Sae::class, "getInstance", Engine::class)
      ->add(Engine::class, "get", "1")
      ->add(Data::class, "publish", "1");

    $service = Service::create($container->getMock());
    $result = $service->publish('dataset', 1);
    $this->assertEquals("1", $result);
  }

  /**
   *
   */
  public function testPublishMissingObjectExpection() {
    $container = $this->getCommonMockChain()
      ->add(Sae::class, "getInstance", Engine::class)
      ->add(Engine::class, "get", new \Exception());

    $service = Service::create($container->getMock());

    $this->expectException(MissingObjectException::class);
    $service->publish('dataset', "foobar");
  }

  /**
   *
   */
  public function testDelete() {
    $container = $this->getCommonMockChain()
      ->add(Sae::class, "getInstance", Engine::class)
      ->add(Engine::class, "delete", "1")
      ->add(Engine::class, "get", "1");

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
    $dataset = (object) ["foo" => "bar"];

    $container = $this->getCommonMockChain()
      ->add(SchemaRetriever::class, "retrieve", json_encode($catalog))
      ->add(Sae::class, "getInstance", Engine::class)
      ->add(Engine::class, "get", [json_encode($dataset), json_encode($dataset)]);

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
  public function getCommonMockChain() {
    $options = (new Options())
      ->add('metastore.schema_retriever', SchemaRetriever::class)
      ->add('metastore.sae_factory', Sae::class)
      ->add('dkan.metastore.storage', DataFactory::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(DataFactory::class, 'getInstance', Data::class)
      ->add(SchemaRetriever::class, "retrieve", json_encode("blah"));
  }

}
