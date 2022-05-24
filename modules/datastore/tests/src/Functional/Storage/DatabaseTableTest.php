<?php

namespace Drupal\Tests\datastore\Functional\Storage;

use Dkan\Datastore\Resource;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\Tests\common\Traits\CleanUp;
use Drupal\Tests\common\Traits\GetDataTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test ResourcePurger service.
 *
 * @package Drupal\Tests\datastore\Functional
 * @group datastore
 */
class DatabaseTableTest extends ExistingSiteBase {
  use GetDataTrait;
  use CleanUp;

  /**
   * Test that setInnodbMode() turns strict mode off for datastore.
   */
  public function testSetInnodbMode() {
    $connection = \Drupal::service('dkan.datastore.database');
    $result = $connection->query("SHOW SESSION VARIABLES LIKE 'innodb_strict_mode'")->fetchObject();
    $this->assertEquals($result->Value, "ON");

    $resource = new Resource('123', '/tmp', 'text/csv');

    $databaseTable = new DatabaseTable($connection, $resource);
    $databaseTable->innodbStrictMode(FALSE);

    $result = $connection->query("SHOW SESSION VARIABLES LIKE 'innodb_strict_mode'")->fetchObject();
    $this->assertEquals($result->Value, "OFF");

    // Other database connection not affected.
    $connection2 = \Drupal::service('database');
    $result = $connection2->query("SHOW SESSION VARIABLES LIKE 'innodb_strict_mode'")->fetchObject();
    $this->assertEquals($result->Value, "ON");

    // Safe mode can be turned back on.
    $databaseTable->innodbStrictMode(TRUE);
    $result = $connection->query("SHOW SESSION VARIABLES LIKE 'innodb_strict_mode'")->fetchObject();
    $this->assertEquals($result->Value, "ON");
  }

}
