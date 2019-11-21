<?php

namespace Drupal\Tests\dkan_harvest\Unit\Extract;

use Contracts\Mock\Storage\Memory;
use Drupal\Core\DependencyInjection\Container;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_common\Tests\Mock\Chain;
use Drupal\dkan_common\Tests\Mock\Options;
use Drupal\dkan_harvest\Load\Dataset;
use Drupal\dkan_metastore\Service;

/**
 * Tests Drupal\dkan_harvest\Load\Dataset.
 *
 * @coversDefaultClass Drupal\dkan_harvest\Load\Dataset
 * @group dkan_harvest
 */
class DatasetTest extends DkanTestBase {

  /**
   * Public.
   */
  public function test() {
    $container = (new Chain($this))
      ->add(Container::class, "get", (new Options())
        ->add('dkan_metastore.service', Service::class)
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
