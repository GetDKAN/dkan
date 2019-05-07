<?php

namespace Drupal\Tests\dkan_datastore\Functional;

use Drupal\dkan_datastore\Storage\Variable;
use Drupal\Tests\BrowserTestBase;

/**
 * @group dkan
 */
class VariableTest extends BrowserTestBase {
  protected $strictConfigSchema = FALSE;

  /**
   *
   */
  public function testInstantiation() {
    // todo: service container is not initialised properly in browser testcase.
    $store = new Variable(\Drupal::service('config.factory'));
    $this->assertTrue(is_object($store));
  }

  /**
   *
   */
  public function testInMemoryRetrieval() {
    $store = new Variable(\Drupal::service('config.factory'));;
    $store->set("hello", "friend");
    $this->assertEquals("friend", $store->get("hello"));
  }

  /**
   *
   */
  public function testConfigRetrieval() {
    $store = new Variable(\Drupal::service('config.factory'));;
    $store->set("hello", "friend");

    unset($store);

    $store2 = new Variable(\Drupal::service('config.factory'));;
    $this->assertEquals("friend", $store2->get("hello"));
  }

  /**
   *
   */
  public function testObjectStorage() {
    $store = new Variable(\Drupal::service('config.factory'));;
    $store->set("object", (object) ['my_name_is' => "Jhon"]);

    unset($store);

    $store2 = new Variable(\Drupal::service('config.factory'));;
    $object = $store2->get('object');
    $this->assertEquals("Jhon", $object->my_name_is);
  }

}
