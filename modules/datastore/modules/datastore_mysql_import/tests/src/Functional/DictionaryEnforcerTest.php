<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore_mysql_import\Functional;

use Drupal\datastore_mysql_import\DataDictionary\AlterTableQuery\NoStrictMySQLQueryBuilder;
use Drupal\Tests\datastore\Functional\DictionaryEnforcerTest as DatastoreDictionaryEnforcerTest;

/**
 * Does the dictionary enforcer work with the mysql importer?
 *
 * @group datastore_mysql_import
 * @group functional
 * @group btb
 */
class DictionaryEnforcerTest extends DatastoreDictionaryEnforcerTest {

  protected static $modules = [
    'datastore_mysql_import',
  ];

  public function testDictionaryEnforcement(): void {
    // Get the datastore module's service name, but it should be our class.
    $this->assertInstanceOf(
      NoStrictMySQLQueryBuilder::class,
      $this->container->get('dkan.datastore.data_dictionary.alter_table_query_builder.mysql')
    );
    parent::testDictionaryEnforcement();
  }

}
