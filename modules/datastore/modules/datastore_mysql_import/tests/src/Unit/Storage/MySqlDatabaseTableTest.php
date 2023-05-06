<?php

namespace Drupal\Tests\dastastore_mysql_import\Unit\Storage;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\StatementWrapper;
use Drupal\datastore\DatastoreResource;
use Drupal\datastore_mysql_import\Storage\MySqlDatabaseTable;
use Drupal\datastore_mysql_import\Storage\MySqlDatabaseTableExistsException;
use Drupal\mysql\Driver\Database\mysql\Schema;
use MockChain\Chain;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\datastore_mysql_import\Storage\MySqlDatabaseTable
 * @coversDefaultClass  \Drupal\datastore_mysql_import\Storage\MySqlDatabaseTable
 *
 * @group dkan
 * @group datastore
 * @group datastore_mysql_import
 */
class MySqlDatabaseTableTest extends TestCase {

  /**
   * @covers ::setTable
   *
   * @see \Drupal\Tests\datastore\Unit\Storage\DatabaseTableTest::testGetSchema
   */
  public function testTableExistsException() {
    $this->expectException(MySqlDatabaseTableExistsException::class);
    $this->expectExceptionMessage('Table already exists: datastore_people');

    $expected_fields = [
      'record_number' => [
        'type' => 'serial',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'length' => 10,
        'mysql_type' => 'int',
      ],
      'first_name' => [
        'type' => 'varchar',
        'description' => 'First Name',
        'length' => 10,
        'mysql_type' => 'varchar',
      ],
      'last_name' => [
        'type' => 'text',
        'description' => 'lAST nAME',
        'mysql_type' => 'text',
      ],
    ];

    // Create the table.
    $database_table = new MySqlDatabaseTable(
      $this->getConnectionChain()->getMock(),
      $this->getResource()
    );
    $this->assertEquals($expected_fields, $database_table->getSchema()['fields']);

    // Create a new table object.
    $second_table = new MySqlDatabaseTable(
      $this->getConnectionChain()->getMock(),
      $this->getResource()
    );
    // Calling count() should trigger setTable() which should find an existing
    // table in the DB.
    $second_table->count();
  }

  /**
   * Private.
   */
  private function getConnectionChain() {
    $fieldInfo = [
      (object) [
        'Field' => 'record_number',
        'Type' => 'int(10)',
        'Extra' => 'auto_increment',
      ],
      (object) [
        'Field' => 'first_name',
        'Type' => 'varchar(10)',
      ],
      (object) [
        'Field' =>
        'last_name',
        'Type' => 'text',
      ],
    ];

    $indexInfo = [
      (object) [
        'Key_name' => 'idx1',
        'Column_name' => 'first_name',
        'Index_type' => 'FOO',
      ],
      (object) [
        'Key_name' => 'ftx1',
        'Column_name' => 'first_name',
        'Index_type' => 'FULLTEXT',
      ],
      (object) [
        'Key_name' => 'ftx2',
        'Column_name' => 'first_name',
        'Index_type' => 'FULLTEXT',
      ],
    ];

    return (new Chain($this))
      // Construction.
      ->add(Connection::class, 'schema', Schema::class)
      ->add(Connection::class, 'query', StatementWrapper::class)
      ->add(Connection::class, 'getConnectionOptions', ['driver' => 'mysql'])
      ->add(StatementWrapper::class, 'fetchAll',
        (new Sequence())->add($fieldInfo)->add($indexInfo)
      )
      ->add(Schema::class, 'tableExists', TRUE)
      ->add(Schema::class, 'getComment',
        (new Sequence())->add(NULL)->add('First Name')->add('lAST nAME')
      )
      ->add(Schema::class, 'dropTable', NULL);
  }

  /**
   * Private.
   */
  private function getResource(): DatastoreResource {
    return new DatastoreResource('people', '', 'text/csv');
  }

}
