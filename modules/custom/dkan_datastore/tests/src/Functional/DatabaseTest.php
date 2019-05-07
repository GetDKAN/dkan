<?php

namespace Drupal\Tests\dkan_datastore\Functional;

use Drupal\dkan_datastore\Storage\Database;
use Drupal\Tests\BrowserTestBase;
use Dkan\Datastore\Storage\Database\Query\Insert;

/**
 * @group dkan
 */
class DatabaseTest extends BrowserTestBase {

  /**
   *
   */
  public function testCreateInsertDrop() {
    $store = new Database(\Drupal::database());
    $schema = [
      'fields' => [
        'first' => [
          'type' => 'text',
        ],
        'last' => [
          'type' => 'text',
        ],
      ],
    ];
    $store->tableCreate("dkan_datastore_test", $schema);

    $data = new Insert("dkan_datastore_test");
    $data->fields = ["first", "last"];
    $data->values = [
      ['Gerardo', 'Gonzalez'],
      ['Jeanette', 'Day'],
      ['Aaron', 'Couch'],
    ];

    $store->insert($data);

    $this->assertEquals(3, $store->count('dkan_datastore_test'));

    $store->tableDrop("dkan_datastore_test");

    $this->expectExceptionMessage("Table dkan_datastore_test does not exist.");
    $store->count('dkan_datastore_test');
  }

}
