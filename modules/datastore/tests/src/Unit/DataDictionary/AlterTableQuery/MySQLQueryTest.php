<?php

namespace Drupal\Tests\datastore\Unit\DataDictionary\AlterTableQuery;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\StatementInterface;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\KeyValueStore\MemoryStorage;
use Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQuery;
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
   * Test via main entrypoint, applyDataTypes().
   */
  public function testApplyDataTypes() {
    // Build container with 'state' service for testing.
    $containerOptions = (new Options())
      ->add('state', (new MemoryStorage('test_storage')))
      ->index(0);
    $container = (new Chain($this))
      ->add(Container::class, 'get', $containerOptions)
      ->getMock();
    \Drupal::setContainer($container);

    // Build MySQLQuery arguments.
    $connectionChain = (new Chain($this))
      ->add(Connection::class, 'getDriverClass', UpdateQueryMock::class)
      ->add(Connection::class, 'prepareStatement', StatementInterface::class, 'prepare')
      ->add(Connection::class, 'query', StatementInterface::class)
      ->add(StatementInterface::class, 'fetchCol', ['foo', 'bar', 'baz'])
      ->add(StatementInterface::class, 'execute', TRUE);
    $connection = $connectionChain->getMock();

    $converter = (new Chain($this))
      ->add(ConverterInterface::class)
      ->getMock();

    $table = 'datastore_' . uniqid();
    $dictionaryFields = [
      ['name' => 'foo', 'type' => 'string', 'format' => 'default'],
      ['name' => 'bar', 'type' => 'number', 'format' => 'default'],
      ['name' => 'baz', 'type' => 'date', 'format' => '%Y-%m-%d'],
    ];

    // Build MySQLQuery object for testing.
    $mySqlQuery = new MySQLQuery($connection, $converter, $table, $dictionaryFields);
    // Extract return value and generate queries for validation.
    $return = $mySqlQuery->applyDataTypes();
    $update_query = \Drupal::state()->get('update_query');
    $query = $connectionChain->getStoredInput('prepare')[0];

    // Validate return value and generated queries.
    $this->assertNull($return);
    $this->assertEquals([
      'field' => 'baz',
      'expression' => 'STR_TO_DATE(baz, :date_format)',
      'arguments' => [
        ':date_format' => ''
      ],
    ], $update_query);
    $this->assertEquals("ALTER TABLE {{$table}} MODIFY COLUMN foo TEXT, MODIFY COLUMN bar DECIMAL(0, ), MODIFY COLUMN baz DATE;", $query);
  }

}
