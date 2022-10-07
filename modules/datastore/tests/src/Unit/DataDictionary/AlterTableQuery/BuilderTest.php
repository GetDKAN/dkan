<?php

namespace Drupal\Tests\datastore\Unit\DataDictionary\AlterTableQuery;

use Drupal\Core\Database\Connection;
use Drupal\common\Storage\DatabaseConnectionFactoryInterface;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryBase;
use Drupal\datastore\DataDictionary\AlterTableQueryBuilderBase;
use Drupal\datastore\DataDictionary\AlterTableQueryBuilderInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;

use MockChain\Chain;
use PDLT\ConverterInterface;
use PHPUnit\Framework\TestCase;

class TestQuery extends AlterTableQueryBase {
  public function getTable(): string {
    return $this->table;
  }

  public function getFields(): array {
    return $this->fields;
  }

  public function getIndexes(): array {
    return $this->indexes;
  }

  protected function doExecute(): void {
    // Required method; do nothing.
  }

}

class TestBuilder extends AlterTableQueryBuilderBase {
  public function getQuery(): AlterTableQueryInterface {
    return new TestQuery(
      $this->databaseConnectionFactory->getConnection(),
      $this->dateFormatConverter,
      $this->table,
      $this->fields,
      $this->indexes,
    );
  }
}

/**
 * Unit tests for Drupal\datastore\DataDictionary\AlterTableQueryBuilderBase.
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
    $uuid = (new Chain($this))
      ->add(UuidInterface::class)
      ->getMock();

    $builder = new TestBuilder($connection, $converter, $uuid);

    // Test Builder's setConnectionTimeout() returns what's expected.
    $result = $builder->setConnectionTimeout(1);
    $this->assertTrue(is_a($result, AlterTableQueryBuilderInterface::class));

    // Test Builder's setTable() returns what's expected.
    $table = 'datastore_' . uniqid();
    $result = $builder->setTable($table);
    $this->assertTrue(is_a($result, AlterTableQueryBuilderInterface::class));

    // Test Builder's addFields() returns what's expected.
    $fields = [
      [
        'name' => 'some_date_field',
        'title' => 'Some Date Field',
        'type' => 'date',
        'format' => '%Y-%m-%d'
      ],
    ];
    $result = $builder->addFields($fields);
    $this->assertTrue(is_a($result, AlterTableQueryBuilderInterface::class));

    // Test Builder's addIndexes() returns what's expected.
    $indexes = [
      [
        'name' => 'some_date_field',
        'type' => 'fulltext',
        'fields' => [
          [
            'name' => 'field_a',
            'length' => 10
          ],
          [
            'name' => 'field_b',
            'length' => 8
          ],
        ],
        'description' => 'Buzz'
      ]
    ];
    $result = $builder->addIndexes($indexes);
    $this->assertTrue(is_a($result, AlterTableQueryBuilderInterface::class));

    // Test Builder's getQuery() returns what's expected.
    $query = $builder->getQuery();
    $this->assertTrue(is_a($query, AlterTableQueryInterface::class));

    $this->assertEquals($table, $query->getTable());
    $this->assertEquals($fields, $query->getFields());
    $this->assertEquals($indexes, $query->getIndexes());
  }

}
