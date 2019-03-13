<?php
/**
 * @group dkan
 */
namespace Drupal\Tests\dkan_datastore\Functional;

use Drupal\dkan_datastore\Storage\Variable;
use Drupal\Tests\BrowserTestBase;

/**
 * @group dkan
 */
class VariableTest extends BrowserTestBase
{
  protected $strictConfigSchema = FALSE;

  public function testInstantiation() {
    $store = new Variable();
    $this->assertTrue(is_object($store));
  }

  public function testInMemoryRetrieval() {
    $store = new Variable();
    $store->set("hello", "friend");
    $this->assertEquals("friend", $store->get("hello"));
  }

  public function testConfigRetrieval() {
    $store = new Variable();
    $store->set("hello", "friend");

    unset($store);

    $store2 = new Variable();
    $this->assertEquals("friend", $store2->get("hello"));
  }

  public function testObjectStorage() {
    $store = new Variable();
    $store->set("object", (object)['my_name_is' => "Jhon"]);

    unset($store);

    $store2 = new Variable();
    $object =  $store2->get('object');
    $this->assertEquals("Jhon", $object->my_name_is);
  }
}