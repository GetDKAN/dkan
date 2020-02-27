<?php

namespace Drupal\Tests\dkan_harvest\Unit\Extract;

use Contracts\Mock\Storage\Memory;
use Drupal\Core\DependencyInjection\Container;
use PHPUnit\Framework\TestCase;
use MockChain\Chain;
use MockChain\Options;
use Drupal\dkan_harvest\Load\Dataset;
use Drupal\dkan_metastore\Service;

/**
 * Tests Drupal\dkan_harvest\Load\Dataset.
 *
 * @coversDefaultClass Drupal\dkan_harvest\Load\Dataset
 * @group dkan_harvest
 */
class DatasetTest extends TestCase {

  /**
   * Public.
   */
  public function test() {
    $container = (new Chain($this))
      ->add(Container::class, "get", (new Options())
        ->add('dkan_metastore.service', Service::class)
        ->index(0)
    )
      ->add(Service::class, "post", "1");

    \Drupal::setContainer($container->getMock());

    $hashStorage = new Memory();
    $itemStorage = new Memory();
    $load = new Dataset((object) ["identifier" => "plan"], $hashStorage, $itemStorage);
    $load->run((object) ["identifier" => "1"]);

    // We just want to run the code.
    $this->assertTrue(TRUE);
  }

}
