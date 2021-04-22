<?php

namespace Drupal\Tests\harvest\Load;

use Contracts\Mock\Storage\Memory;
use Drupal\Core\DependencyInjection\Container;
use Drupal\harvest\Load\Dataset;
use Drupal\metastore\Exception\ExistingObjectException;
use Drupal\metastore\Service;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\harvest\Load\Dataset
 * @group harvest
 */
class DatasetTest extends TestCase {

  /**
   *
   */
  public function testNew() {
    $containerOptions = (new Options())
      ->add('dkan.metastore.service', Service::class)
      ->index(0);

    $containerChain = (new Chain($this))
      ->add(Container::class, "get", $containerOptions)
      ->add(Service::class, "post", "1", 'post');

    $container = $containerChain->getMock();

    \Drupal::setContainer($container);

    $plan = (object) ["identifier" => "plan"];
    $hashStorage = new Memory();
    $itemStorage = new Memory();

    $load = new Dataset($plan, $hashStorage, $itemStorage);
    $object = (object) ["identifier" => "1"];

    $load->run($object);

    $input = $containerChain->getStoredInput('post');

    $this->assertEquals('dataset', $input[0]);
    $this->assertEquals(json_encode($object), $input[1]);
  }

  /**
   *
   */
  public function testUpdate() {
    $containerOptions = (new Options())
      ->add('dkan.metastore.service', Service::class)
      ->index(0);

    $containerChain = (new Chain($this))
      ->add(Container::class, "get", $containerOptions)
      ->add(Service::class, 'post', new ExistingObjectException())
      ->add(Service::class, "put", [], 'put');

    $container = $containerChain->getMock();

    \Drupal::setContainer($container);

    $plan = (object) ["identifier" => "plan"];
    $hashStorage = new Memory();
    $itemStorage = new Memory();

    $load = new Dataset($plan, $hashStorage, $itemStorage);
    $object = (object) ["identifier" => "1"];

    $load->run($object);

    $input = $containerChain->getStoredInput('put');

    $this->assertEquals('dataset', $input[0]);
    $this->assertEquals('1', $input[1]);
    $this->assertEquals(json_encode($object), $input[2]);
  }

}
