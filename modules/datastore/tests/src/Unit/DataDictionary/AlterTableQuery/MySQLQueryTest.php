<?php

namespace Drupal\Tests\datastore\Unit\DataDictionary\AlterTableQuery;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\StatementInterface;
use Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQuery;
use PDLT\ConverterInterface;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQuery.
 */
class MySQLQueryTest extends TestCase {

  /**
   * Test via main entrypoint, applyDataTypes().
   */
  public function testApplyDataTypes() {

    $connection = (new Chain($this))
      ->add(Connection::class, 'query', StatementInterface::class)
      ->add(StatementInterface::class, 'fetchCol', ['foo', 'bar', 'baz'])
      ->getMock();
    $converter = (new Chain($this))
      ->add(ConverterInterface::class)
      ->getMock();
    $table = 'datastore_' . uniqid();
    $dictionaryFields = [
      ['name' => 'foo', 'type' => 'string', 'format' => 'default'],
      ['name' => 'bar', 'type' => 'number', 'format' => 'default'],
      ['name' => 'baz', 'type' => 'date', 'format' => 'default'],
    ];

    $mySqlQuery = new MySQLQuery($connection, $converter, $table, $dictionaryFields);
    $return = $mySqlQuery->applyDataTypes();

    $this->assertNull($return);
  }

}
