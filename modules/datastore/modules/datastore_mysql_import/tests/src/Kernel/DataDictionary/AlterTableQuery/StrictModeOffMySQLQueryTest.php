<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore_mysql_import\Kernel\DataDictionary\AlterTableQuery;

use Drupal\Core\Database\Connection;
use Drupal\datastore_mysql_import\DataDictionary\AlterTableQuery\StrictModeOffMySQLQuery;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test StrictModeOffMySQLQuery functionality.
 *
 * @coversDefaultClass \Drupal\datastore_mysql_import\DataDictionary\AlterTableQuery\StrictModeOffMySQLQuery
 *
 * @group dkan
 * @group datastore_mysql_import
 * @group kernel
 */
class StrictModeOffMySQLQueryTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'common',
    'datastore',
    'datastore_mysql_import',
    'metastore',
  ];

  /**
   * Test that disableStrictModeConnection sets innodb_strict_mode to OFF.
   *
   * @covers ::disableStrictModeConnection
   */
  public function testDisableStrictModeConnection(): void {
    // Get the database connection and date format converter from container.
    $connection = $this->container->get('database');
    $date_format_converter = $this->container->get('pdlt.converter.strptime_to_mysql');

    // Assert that innodb_strict_mode is initially set to ON (1).
    $result = $connection->query("SELECT @@SESSION.innodb_strict_mode AS strict_mode")->fetchField();
    $this->assertEquals('1', $result, 'innodb_strict_mode should be set to ON (1) initially');

    // Create a StrictModeOffMySQLQuery object.
    $query = new StrictModeOffMySQLQuery(
      $connection,
      $date_format_converter,
      'test_table',
      [],
      []
    );

    // Call disableStrictModeConnection() method - this is what we're testing.
    $query->disableStrictModeConnection();

    // Use reflection to get the connection object from the query.
    $reflection = new \ReflectionClass($query);
    $connectionProperty = $reflection->getProperty('connection');
    $connectionProperty->setAccessible(TRUE);
    $queryConnection = $connectionProperty->getValue($query);

    // Assert that innodb_strict_mode is set to OFF (0).
    $this->assertEquals('alter_dkan_strict_off', $queryConnection->getKey(), 'Query connection key should be alter_dkan_strict_off');
    $result = $queryConnection->query("SELECT @@SESSION.innodb_strict_mode AS strict_mode")->fetchField();
    $this->assertEquals('0', $result, 'innodb_strict_mode should be set to OFF (0)');

    $query->execute();
    // After executing the query, the active connection should be reset to key
    // "default" and we should be back to seeing innodb_strict_mode as ON (1).
    $activeConnection = $this->container->get('database');
    $this->assertEquals("default", $activeConnection->getKey(), 'Active connection should be reset to default after executing the query');
    $result = $activeConnection->query("SELECT @@SESSION.innodb_strict_mode AS strict_mode")->fetchField();
    $this->assertEquals('1', $result, 'innodb_strict_mode should be set back to ON (1) after executing the query');
  }

}
