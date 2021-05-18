<?php

namespace Drupal\Tests\harvest\Load;

use Contracts\Mock\Storage\Memory;
use Drupal\Core\DependencyInjection\Container;
use Drupal\harvest\Load\Dataset;
use Drupal\metastore\Exception\ExistingObjectException;
use Drupal\metastore\ValidMetadataFactory;
use Drupal\metastore\Service;
use Drupal\Tests\metastore\Unit\ServiceTest;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\harvest\Load\Dataset
 * @group harvest
 */
class DatasetTest extends TestCase {

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->validMetadataFactory = ServiceTest::getValidMetadataFactory($this);
  }

  /**
   *
   */
  public function testNew() {
    $containerOptions = (new Options())
      ->add('dkan.metastore.service', Service::class)
      ->index(0);

    $object = (object) ["identifier" => "1"];
    $expected = $this->validMetadataFactory->get('dummy_schema_id', json_encode($object));

    $containerChain = (new Chain($this))
      ->add(Container::class, "get", $containerOptions)
      ->add(Service::class, "getValidMetadataFactory", ValidMetadataFactory::class)
      ->add(ValidMetadataFactory::class, "get", $expected)
      ->add(Service::class, "post", "1", 'post');

    $container = $containerChain->getMock();

    \Drupal::setContainer($container);

    $plan = (object) ["identifier" => "plan"];
    $hashStorage = new Memory();
    $itemStorage = new Memory();

    $load = new Dataset($plan, $hashStorage, $itemStorage);
    $load->run($object);

    $input = $containerChain->getStoredInput('post');

    $this->assertEquals('dataset', $input[0]);
    $this->assertEquals($expected, $input[1]);
  }

  /**
   *
   */
  public function testUpdate() {
    $containerOptions = (new Options())
      ->add('dkan.metastore.service', Service::class)
      ->index(0);

    $object = (object) ["identifier" => "1"];
    $expected = $this->validMetadataFactory->get('dummy_schema_id', json_encode($object));

    $containerChain = (new Chain($this))
      ->add(Container::class, "get", $containerOptions)
      ->add(Service::class, "getValidMetadataFactory", ValidMetadataFactory::class)
      ->add(ValidMetadataFactory::class, "get", $expected)
      ->add(Service::class, 'post', new ExistingObjectException())
      ->add(Service::class, "put", [], 'put');

    $container = $containerChain->getMock();

    \Drupal::setContainer($container);

    $plan = (object) ["identifier" => "plan"];
    $hashStorage = new Memory();
    $itemStorage = new Memory();

    $load = new Dataset($plan, $hashStorage, $itemStorage);
    $load->run($object);

    $input = $containerChain->getStoredInput('put');

    $this->assertEquals('dataset', $input[0]);
    $this->assertEquals('1', $input[1]);
    $this->assertEquals($expected, $input[2]);
  }

}
