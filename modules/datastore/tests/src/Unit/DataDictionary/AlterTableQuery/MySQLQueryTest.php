<?php

namespace Drupal\Tests\datastore\Unit\DataDictionary\AlterTableQuery;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\StatementInterface;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\KeyValueStore\MemoryStorage;

use Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQuery;
use Drupal\datastore\DataDictionary\IncompatibleTypeException;
use Drupal\Tests\datastore\Unit\DataDictionary\UpdateQueryMock;

use MockChain\Chain;
use MockChain\Options;
use PDLT\ConverterInterface;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQuery.
 */
class MySQLQueryTest extends TestCase {

  /**
   * Prepare for tests.
   */
  public function setUp(): void {
    // Build container with 'state' service for testing.
    $container_options = (new Options())
      ->add('state', (new MemoryStorage('test_storage')))
      ->index(0);
    $container = (new Chain($this))
      ->add(Container::class, 'get', $container_options)
      ->getMock();
    \Drupal::setContainer($container);
  }

  /**
   * Build MySQLQuery arguments.
   */
  public function buildConnectionChain(): Chain {
    return (new Chain($this))
      ->add(Connection::class, 'getDriverClass', UpdateQueryMock::class)
      ->add(Connection::class, 'prepareStatement', StatementInterface::class, 'prepare')
      ->add(Connection::class, 'query', StatementInterface::class)
      ->add(StatementInterface::class, 'execute', TRUE)
      ->add(StatementInterface::class, 'fetchAllKeyed', [
        'foo' => 'Foo',
        'bar' => 'Bar',
        'baz' => 'Baz',
      ]);
  }

  /**
   * Build MySQLQuery object for testing.
   */
  public function buildMySQLQuery(Connection $connection, ?string $table = NULL, ?array $dictionary_fields = NULL): MySQLQuery {
    $converter = (new Chain($this))
      ->add(ConverterInterface::class)
      ->getMock();

    $table ??= 'datastore_' . uniqid();
    $dictionary_fields ??= [
      ['name' => 'foo', 'type' => 'string', 'format' => 'default', 'title' => 'Foo'],
      ['name' => 'bar', 'type' => 'number', 'format' => 'default', 'title' => 'Bar'],
      ['name' => 'baz', 'type' => 'date', 'format' => '%Y-%m-%d', 'title' => 'Baz'],
    ];

    return new MySQLQuery($connection, $converter, $table, $dictionary_fields);
  }

  /**
   * Test via main entrypoint, applyDataTypes().
   */
  public function testApplyDataTypes(): void {
    // Build MySQLQuery object.
    $connection_chain = $this->buildConnectionChain();
    $table = 'datastore_' . uniqid();
    $mysql_query = $this->buildMySQLQuery($connection_chain->getMock(), $table);

    // Perform query and validate return values.
    $return = $mysql_query->applyDataTypes();
    $this->assertNull($return);

    // Validate return value and generated queries.
    $query = $connection_chain->getStoredInput('prepare')[0];
    $this->assertEquals(
      "ALTER TABLE {{$table}} " .
      "MODIFY COLUMN foo TEXT COMMENT 'Foo', " .
      "MODIFY COLUMN bar DECIMAL(0, ) COMMENT 'Bar', " .
      "MODIFY COLUMN baz DATE COMMENT 'Baz';",
      $query);
  }

  /**
   * Ensure alter fails when attempting to apply decimal type to large numbers.
   */
  public function testApplyDataTypesWithTooLargeDecimal(): void {
    $connection_chain = $this->buildConnectionChain()
      ->add(StatementInterface::class, 'fetchField', 100);
    $column = 'bar';
    $mysql_query = $this->buildMySQLQuery($connection_chain->getMock(), NULL, [['name' => $column, 'type' => 'number', 'format' => 'default', 'title' => 'Bar']]);

    $this->expectException(IncompatibleTypeException::class);
    $this->expectExceptionMessage("Decimal values found in column too large for DECIMAL type; please use type 'string' for column '{$column}'");
    $mysql_query->applyDataTypes();
  }

}
