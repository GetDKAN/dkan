<?php

namespace Drupal\Tests\data_content_type\Unit;

use Drupal\Core\DependencyInjection\Container;
use Drupal\data_content_type\ConfigurationOverrider;
use Drupal\dkan_schema\SchemaRetriever;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class ConfigurationOverriderTest extends TestCase {

  /**
   *
   */
  public function testEmptyMethods() {
    $cofigOverrider = new ConfigurationOverrider();
    $this->assertNull($cofigOverrider->getCacheSuffix());
    $this->assertNull($cofigOverrider->createConfigObject("blah"));
    $this->assertNull($cofigOverrider->getCacheableMetadata("blah"));
  }

  /**
   *
   */
  public function testLoadOverrides() {
    $container = (new Chain($this))
      ->add(Container::class, "get", SchemaRetriever::class)
      ->add(SchemaRetriever::class, "retrieve", "{}")
      ->getMock();

    \Drupal::setContainer($container);

    $cofigOverrider = new ConfigurationOverrider();
    $config = $cofigOverrider->loadOverrides(["core.entity_form_display.node.data.default"]);
    $this->assertTrue(is_array($config));

    $config = $cofigOverrider->loadOverrides(["blah"]);
    $this->assertTrue(is_array($config));
  }

}
