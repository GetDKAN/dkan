<?php

namespace Drupal\Tests\datastore\Unit\DataDictionary\AlterTableQuery;

use Drupal\Core\Database\Connection;
use Drupal\common\Storage\DatabaseConnectionFactoryInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryBuilderInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;
use Drupal\datastore\DataDictionary\AlterTableQuery\Builder;
use Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQuery;

use MockChain\Chain;
use PDLT\ConverterInterface;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Drupal\datastore\DataDictionary\AlterTableQuery\Builder.
 */
class BuilderTest extends TestCase {

  /**
   * Test Builder.
   */
  public function test() {

    $connection = (new Chain($this))
      ->add(DatabaseConnectionFactoryInterface::class, 'setConnectionTimeout', DatabaseConnectionFactoryInterface::class)
      ->addd('getConnection', Connection::class)
      ->getMock();
    $converter = (new Chain($this))
      ->add(ConverterInterface::class)
      ->getMock();

    $builder = new Builder($connection, $converter, MySQLQuery::class);

    // Test Builder's setConnectionTimeout() returns what's expected.
    $result = $builder->setConnectionTimeout(1);
    $this->assertTrue(is_a($result, AlterTableQueryBuilderInterface::class));

    // Test Builder's setTable() returns what's expected.
    $result = $builder->setTable('datastore_' . uniqid());
    $this->assertTrue(is_a($result, AlterTableQueryBuilderInterface::class));

    // Test Builder's addFields() returns what's expected.
    $result = $builder->addFields([]);
    $this->assertTrue(is_a($result, AlterTableQueryBuilderInterface::class));

    // Test Builder's addIndexes() returns what's expected.
    $result = $builder->addIndexes([]);
    $this->assertTrue(is_a($result, AlterTableQueryBuilderInterface::class));

    // Test Builder's getQuery() returns what's expected.
    $query = $builder->getQuery();
    $this->assertTrue(is_a($query, AlterTableQueryInterface::class));
  }

}
