<?php

namespace Drupal\Tests\datastore\Unit\DataDictionary\AlterTableQuery;

use Drupal\Core\Database\Connection;
use Drupal\common\Storage\DatabaseConnectionFactoryInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryBuilderInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;
use Drupal\datastore\DataDictionary\AlterTableQuery\Factory;
use Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQuery;

use MockChain\Chain;
use PDLT\ConverterInterface;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Drupal\datastore\DataDictionary\AlterTableQuery\Factory.
 */
class FactoryTest extends TestCase {

  /**
   * Test Factory.
   */
  public function test() {

    $connection = (new Chain($this))
      ->add(DatabaseConnectionFactoryInterface::class, 'setConnectionTimeout', DatabaseConnectionFactoryInterface::class)
      ->addd('getConnection', Connection::class)
      ->getMock();
    $converter = (new Chain($this))
      ->add(ConverterInterface::class)
      ->getMock();

    $factory = new Factory($connection, $converter, MySQLQuery::class);

    // Test Factory's setConnectionTimeout() returns what's expected.
    $result = $factory->setConnectionTimeout(1);
    $this->assertTrue(is_a($result, AlterTableQueryBuilderInterface::class));

    // Test Factory's getQuery() returns what's expected.
    $query = $factory->getQuery('datastore_' . uniqid(), []);
    $this->assertTrue(is_a($query, AlterTableQueryInterface::class));
  }

}
