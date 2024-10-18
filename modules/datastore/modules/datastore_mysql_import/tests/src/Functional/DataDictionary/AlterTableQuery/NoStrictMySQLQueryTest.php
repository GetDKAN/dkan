<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore_mysql_import\Functional\DataDictionary\AlterTableQuery;

use Drupal\datastore_mysql_import\DataDictionary\AlterTableQuery\NoStrictMySQLQueryBuilder;
use Drupal\Tests\datastore\Functional\DataDictionary\AlterTableQuery\MySQLQueryTest;

/**
 * @coversDefaultClass \Drupal\datastore_mysql_import\DataDictionary\AlterTableQuery\NoStrictMySQLQuery
 *
 * @group dkan
 * @group datastore_mysql_import
 * @group functional
 */
class NoStrictMySQLQueryTest extends MySQLQueryTest {

  protected static $modules = [
    'datastore_mysql_import',
  ];

  public function testPostImport() {
    // Get the datastore module's service name, but it should be our class.
    $this->assertInstanceOf(
    NoStrictMySQLQueryBuilder::class,
    $this->container->get('dkan.datastore.data_dictionary.alter_table_query_builder.mysql')
    );
    parent::testPostImport();
  }

}
