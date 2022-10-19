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
use MockChain\Sequence;
use PDLT\ConverterInterface;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQuery.
 *
 * @coversDefaultClass Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQuery
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
   *
   * @todo Include an update() mock for buildBoolPreAlterCommands().
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
        'boolz' => 'Boolz',
      ])
      ->add(StatementInterface::class, 'fetchField', (new Sequence())
        ->add('5')
        ->add('5')
        ->add('50')
      );
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
      ['name' => 'boolz', 'type' => 'boolean', 'format' => 'default', 'title' => 'Boolz'],
    ];
    $dictionary_indexes ??= [
      ['name' => 'index1', 'type' => 'index', 'description' => 'Fizz', 'fields' => [
        ['name' => 'foo', 'length' => 12],
        ['name' => 'bar', 'length' => 6],
        ['name' => 'baz', 'length' => NULL],
      ]],
      ['name' => 'index2', 'type' => 'fulltext', 'description' => '', 'fields' => [
        ['name' => 'foo', 'length' => 6],
        ['name' => 'baz', 'length' => NULL],
      ]],
      ['name' => 'index3', 'type' => 'fulltext', 'description' => '', 'fields' => [
        ['name' => 'foo', 'length' => 6],
        ['name' => 'missing', 'length' => 3],
      ]],
    ];

    return new MySQLQuery($connection, $converter, $table, $dictionary_fields, $dictionary_indexes);
  }

  /**
   * Test via main entrypoint, execute().
   */
  public function testExecute(): void {
    $connection_chain = $this->buildConnectionChain();
    $table = 'datastore_' . uniqid();
    $mysql_query = $this->buildMySQLQuery($connection_chain->getMock(), $table);

    // Extract return value and generate queries for validation.
    $return = $mysql_query->execute();
    $update_query = \Drupal::state()->get('update_query');
    $query = $connection_chain->getStoredInput('prepare')[0];

    // Validate return value and generated queries.
    $this->assertNull($return);
    $this->assertEquals([
      'field' => 'baz',
      'expression' => 'STR_TO_DATE(baz, :date_format)',
      'arguments' => [
        ':date_format' => ''
      ],
    ], $update_query);

    $this->assertEquals("ALTER TABLE {" . $table . "} MODIFY COLUMN foo TEXT COMMENT 'Foo', " .
    "MODIFY COLUMN bar DECIMAL(10, 5) COMMENT 'Bar', " .
    "MODIFY COLUMN baz DATE COMMENT 'Baz', " .
    "ADD  INDEX index1 (foo (12), bar, baz) COMMENT 'Fizz', " .
    "ADD FULLTEXT INDEX index2 (foo (6), baz) COMMENT '';", $query);
  }


  /**
   * Ensure alter fails when attempting to apply decimal type to large numbers.
   */
  public function testExecuteWithTooLargeDecimal(): void {
    $connection_chain = $this->buildConnectionChain()
      ->add(StatementInterface::class, 'fetchField', 100);
    $column = 'bar';
    $mysql_query = $this->buildMySQLQuery($connection_chain->getMock(), NULL, [['name' => $column, 'type' => 'number', 'format' => 'default', 'title' => 'Bar']]);

    $this->expectException(IncompatibleTypeException::class);
    $this->expectExceptionMessage("Decimal values found in column too large for DECIMAL type; please use type 'string' for column '{$column}'");
    $mysql_query->execute();
  }

  public function baseTypeProvider() {
    return [
      'string' => ['string', 'TEXT'],
      // @todo getBaseType() performs no type checking, has no error result.
      'getBaseType-does-no-error-checking' => ['not-a-frictionless-type', NULL],
    ];
  }

  /**
   * @dataProvider baseTypeProvider
   * @covers ::getBaseType
   */
  public function _testGetBaseType($frictionless_type, $expected_type) {
    // Instantiate a MySQL query object.
    $connection_chain = $this->buildConnectionChain();
    $table = 'datastore_' . uniqid();
    $mysql_query = $this->buildMySQLQuery($connection_chain->getMock(), $table);

    // getBaseType() is protected.
    $get_test_base = new \ReflectionMethod($mysql_query, 'getBaseType');
    $get_test_base->setAccessible(TRUE);
    $this->assertEquals(
      $expected_type,
      $get_test_base->invokeArgs($mysql_query, [$frictionless_type])
    );
  }

}
