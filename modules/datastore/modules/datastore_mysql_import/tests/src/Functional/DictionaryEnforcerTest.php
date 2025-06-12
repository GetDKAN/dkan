<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore_mysql_import\Functional;

use Drupal\Tests\datastore\Functional\DictionaryEnforcerTest as DatastoreDictionaryEnforcerTest;

/**
 * Ensure that datastore_mysql_import passes the same data dictionary test.
 *
 * We do this because datastore_mysql_import decorates the alter query system
 * that data dictionary uses.
 *
 * @group datastore_mysql_import
 * @group functional
 * @group btb
 * @group functional2
 */
class DictionaryEnforcerTest extends DatastoreDictionaryEnforcerTest {

  protected static $modules = [
    'datastore_mysql_import',
  ];

}
