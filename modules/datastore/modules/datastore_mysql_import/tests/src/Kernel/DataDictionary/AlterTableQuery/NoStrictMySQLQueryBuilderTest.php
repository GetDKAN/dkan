<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore_mysql_import\Kernel\DataDictionary\AlterTableQuery;

use Drupal\datastore_mysql_import\DataDictionary\AlterTableQuery\NoStrictMySQLQueryBuilder;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test that our service decorator is set up properly.
 *
 * @coversDefaultClass \Drupal\datastore_mysql_import\DataDictionary\AlterTableQuery\NoStrictMySQLQueryBuilder
 *
 * @group dkan
 * @group datastore_mysql_import
 * @group kernel
 */
class NoStrictMySQLQueryBuilderTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'datastore',
    'datastore_mysql_import',
    'metastore',
  ];

  /**
   * Ensure the query builder service decoration is defined properly.
   */
  public function testServiceDecorator() {
    // Get the datastore module's service name, but it should be our decorator
    // class.
    $this->assertInstanceOf(
      NoStrictMySQLQueryBuilder::class,
      $this->container->get('dkan.datastore.data_dictionary.alter_table_query_builder.mysql')
    );
  }

}
