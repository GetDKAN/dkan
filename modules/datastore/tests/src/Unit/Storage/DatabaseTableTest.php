<?php

namespace Drupal\Tests\datastore\Storage;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Database\Query\Insert;
use Drupal\Core\Database\Query\Select;
use Drupal\Core\Database\StatementWrapper;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\common\Storage\Query;
use Drupal\datastore\DatastoreResource;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\mysql\Driver\Database\mysql\Schema;
use MockChain\Chain;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;

/**
 * @group dkan
 * @group datastore
 * @group unit
 */
class DatabaseTableTest extends TestCase {

  /**
   *
   */
  public function testConstruction() {

    $databaseTable = new DatabaseTable(
      $this->getConnectionChain()->getMock(),
      $this->getResource(),
      $this->createStub(LoggerChannelInterface::class)
    );
    $this->assertTrue(is_object($databaseTable));
  }

  /**
   *
   */
  public function testGetSchema() {
    $connectionChain = $this->getConnectionChain();

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource(),
      $this->createStub(LoggerChannelInterface::class)
    );

    $schema = $databaseTable->getSchema();

    $expectedSchema = [
      "fields" => [
        "record_number" => [
          "type" => "serial",
          "unsigned" => TRUE,
          "not null" => TRUE,
          'length' => 10,
          'mysql_type' => 'int',
        ],
        "first_name" => [
          "type" => "varchar",
          "description" => "First Name",
          'length' => 10,
          'mysql_type' => 'varchar'
        ],
        "last_name" => [
          "type" => "text",
          "description" => "lAST nAME",
          "mysql_type" => "text",
        ],
      ],
      "indexes" => [
        "idx1" => [
          "first_name",
        ],
      ],
      "fulltext indexes" => [
        "ftx1" => [
          "first_name",
          "last_name",
        ],
      ],
    ];

    $this->assertEquals($expectedSchema['fields'], $schema['fields']);
  }

  /**
   *
   */
  public function testRetrieveAll() {

    $fieldInfo = [
      (object) ['Field' => "first_name", 'Type' => "varchar(10)"],
      (object) ['Field' => "last_name", 'Type' => 'text']
    ];

    $sequence = (new Sequence())
      ->add($fieldInfo)
      ->add([]);

    $connection = $this->getConnectionChain()
      ->add(Connection::class, "select", Select::class)
      ->add(Select::class, "fields", Select::class)
      ->add(Select::class, "execute", StatementWrapper::class)
      ->add(StatementWrapper::class, 'fetchAll', $sequence)
      ->getMock();

    $databaseTable = new DatabaseTable(
      $connection,
      $this->getResource(),
      $this->createStub(LoggerChannelInterface::class)
    );
    $this->assertEquals([], $databaseTable->retrieveAll());
  }

  /**
   *
   */
  public function testStore() {
    $connectionChain = $this->getConnectionChain()
      ->add(Connection::class, 'insert', Insert::class)
      ->add(Insert::class, 'fields', Insert::class)
      ->add(Insert::class, 'values', Insert::class)
      ->add(Insert::class, 'execute', "1")
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'condition', Select::class)
      ->add(Select::class, 'execute', StatementWrapper::class)
      ->add(StatementWrapper::class, 'fetch', NULL);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource(),
      $this->createStub(LoggerChannelInterface::class)
    );
    $this->assertEquals("1", $databaseTable->store('["Gerardo", "Gonzalez"]', "1"));
  }

  /**
   *
   */
  public function testStoreFieldCountException() {
    $connectionChain = $this->getConnectionChain()
      ->add(Connection::class, 'insert', Insert::class)
      ->add(Insert::class, 'fields', Insert::class)
      ->add(Insert::class, 'values', Insert::class)
      ->add(Insert::class, 'execute', "1")
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'condition', Select::class)
      ->add(Select::class, 'execute', StatementWrapper::class)
      ->add(StatementWrapper::class, 'fetch', NULL);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource(),
      $this->createStub(LoggerChannelInterface::class)
    );
    $this->expectExceptionMessageMatches("/The number of fields and data given do not match:/");
    $this->assertEquals("1", $databaseTable->store('["Foobar"]', "1"));
  }

  /**
   *
   */
  public function testStoreMultiple() {
    $connectionChain = $this->getConnectionChain()
      ->add(Connection::class, 'insert', Insert::class)
      ->add(Insert::class, 'fields', Insert::class)
      ->add(Insert::class, 'values', Insert::class)
      ->add(Insert::class, 'execute', "1")
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'condition', Select::class)
      ->add(Select::class, 'execute', StatementWrapper::class)
      ->add(StatementWrapper::class, 'fetch', NULL);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource(),
      $this->createStub(LoggerChannelInterface::class)
    );
    $data = [
      '["Gerardo", "Gonzalez"]',
      '["Thierry", "Dallacroce"]',
      '["Foo", "Bar"]',
    ];
    $this->assertEquals("1", $databaseTable->storeMultiple($data, "1"));
  }

  /**
   *
   */
  public function testStoreMultipleFieldCountException() {
    $connectionChain = $this->getConnectionChain()
      ->add(Connection::class, 'insert', Insert::class)
      ->add(Insert::class, 'fields', Insert::class)
      ->add(Insert::class, 'values', Insert::class)
      ->add(Insert::class, 'execute', "1")
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'condition', Select::class)
      ->add(Select::class, 'execute', StatementWrapper::class)
      ->add(StatementWrapper::class, 'fetch', NULL);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource(),
      $this->createStub(LoggerChannelInterface::class)
    );
    $data = [
      '["One"]',
      '["Two"]',
      '["Three"]',
    ];
    $this->expectExceptionMessageMatches("/The number of fields and data given do not match:/");
    $this->assertEquals("1", $databaseTable->storeMultiple($data, "1"));
  }

  /**
   *
   */
  public function testCount() {
    $connectionChain = $this->getConnectionChain()
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'countQuery', Select::class)
      ->add(Select::class, 'execute', StatementWrapper::class)
      ->add(StatementWrapper::class, 'fetchField', 1);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource(),
      $this->createStub(LoggerChannelInterface::class)
    );
    $this->assertEquals(1, $databaseTable->count());
  }

  /**
   *
   */
  public function testGetSummary() {
    $connectionChain = $this->getConnectionChain()
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'countQuery', Select::class)
      ->add(Select::class, 'execute', StatementWrapper::class)
      ->add(StatementWrapper::class, 'fetchField', 1);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource(),
      $this->createStub(LoggerChannelInterface::class)
    );

    $actual = json_decode(json_encode(
      $databaseTable->getSummary()
    ));

    $this->assertEquals(3, $actual->numOfColumns);
    $this->assertEquals(1, $actual->numOfRows);
    $this->assertEquals(["record_number", "first_name", "last_name"],
      array_keys((array) $actual->columns));
  }

  /**
   *
   */
  public function testDestruct() {
    $connectionChain = $this->getConnectionChain();

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource(),
      $this->createStub(LoggerChannelInterface::class)
    );
    $databaseTable->destruct();
    $this->assertTrue(TRUE);

  }

  /**
   *
   */
  public function testPrepareDataJsonDecodeNull() {
    $connectionChain = $this->getConnectionChain()
      ->add(Connection::class, 'insert', Insert::class)
      ->add(Insert::class, 'fields', Insert::class)
      ->add(Insert::class, 'values', Insert::class)
      ->add(Insert::class, 'execute', "1")
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'condition', Select::class)
      ->add(Select::class, 'execute', StatementWrapper::class)
      ->add(StatementWrapper::class, 'fetch', NULL);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource(),
      $this->createStub(LoggerChannelInterface::class)
    );
    $this->expectExceptionMessage('Import for 1 returned an error when preparing table header: {"foo":"bar"}');
    $this->assertEquals("1", $databaseTable->store('{"foo":"bar"}', "1"));
  }

  /**
   *
   */
  public function testPrepareDataNonArray() {
    $connectionChain = $this->getConnectionChain()
      ->add(Connection::class, 'insert', Insert::class)
      ->add(Insert::class, 'fields', Insert::class)
      ->add(Insert::class, 'values', Insert::class)
      ->add(Insert::class, 'execute', "1")
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'condition', Select::class)
      ->add(Select::class, 'execute', StatementWrapper::class)
      ->add(StatementWrapper::class, 'fetch', NULL);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource(),
      $this->createStub(LoggerChannelInterface::class)
    );
    $this->expectExceptionMessage("Import for 1 error when decoding foobar");
    $this->assertEquals("1", $databaseTable->store("foobar", "1"));
  }

  /**
   *
   */
  public function testQuery() {
    $query = new Query();

    $connectionChain = $this->getConnectionChain()
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'condition', Select::class)
      ->add(Select::class, 'execute', StatementWrapper::class)
      ->add(StatementWrapper::class, 'fetchAll', []);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource(),
      $this->createStub(LoggerChannelInterface::class)
    );

    $this->assertEquals([], $databaseTable->query($query));
  }

  /**
   *
   */
  public function testQueryExceptionDatabaseInternalError() {
    $query = new Query();

    $connectionChain = $this->getConnectionChain()
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'condition', Select::class)
      ->add(Select::class, 'execute', new DatabaseExceptionWrapper("Integrity constraint violation"));

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource(),
      $this->createStub(LoggerChannelInterface::class)
    );

    $this->expectExceptionMessage("Database internal error.");
    $databaseTable->query($query);
  }

  /**
   *
   */
  public function testQueryColumnNotFound() {
    $query = new Query();

    $connectionChain = $this->getConnectionChain()
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'condition', Select::class)
      ->add(Select::class, 'execute', new DatabaseExceptionWrapper("SQLSTATE[42S22]: Column not found: 1054 Unknown column 'sensitive_information'..."));

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource(),
      $this->createStub(LoggerChannelInterface::class)
    );

    $this->expectExceptionMessage("Column not found");
    $databaseTable->query($query);
  }

  /**
   *
   */
  public function testNoFulltextIndexFound() {
    $query = new Query();

    $connectionChain = $this->getConnectionChain()
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'condition', Select::class)
      ->add(Select::class, 'execute', new DatabaseExceptionWrapper("SQLSTATE[HY000]: General error: 1191 Can't find FULLTEXT index matching the column list..."));

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource(),
      $this->createStub(LoggerChannelInterface::class)
    );

    $this->expectExceptionMessage("You have attempted a fulltext match against a column that is not indexed for fulltext searching");
    $databaseTable->query($query);
  }

  /**
   * Private.
   */
  private function getConnectionChain() {
    $fieldInfo = [
      (object) [
        'Field' => "record_number",
        'Type' => "int(10)",
        'Extra' => "auto_increment",
      ],
      (object) [
        'Field' => "first_name",
        'Type' => "varchar(10)"
      ],
      (object) [
        'Field' =>
        "last_name",
        'Type' => 'text'
      ]
    ];

    $indexInfo = [
      (object) [
        'Key_name' => "idx1",
        'Column_name' => 'first_name',
        'Index_type' => 'FOO',
      ],
      (object) [
        'Key_name' => "ftx1",
        'Column_name' => 'first_name',
        'Index_type' => 'FULLTEXT',
      ],
      (object) [
        'Key_name' => "ftx2",
        'Column_name' => 'first_name',
        'Index_type' => 'FULLTEXT',
      ],
    ];

    $chain = (new Chain($this))
      // Construction.
      ->add(Connection::class, "schema", Schema::class)
      ->add(Connection::class, 'query', StatementWrapper::class)
      ->add(Connection::class, 'getConnectionOptions', ['driver' => 'mysql'])
      ->add(StatementWrapper::class, 'fetchAll',
        (new Sequence())->add($fieldInfo)->add($indexInfo)
      )
      ->add(Schema::class, "tableExists", TRUE)
      ->add(Schema::class, 'getComment',
        (new Sequence())->add(NULL)->add('First Name')->add('lAST nAME')
      )
      ->add(Schema::class, 'dropTable', NULL);

    return $chain;
  }

  /**
   * Private.
   */
  private function getResource() {
    return new DatastoreResource("people", "", "text/csv");
  }

}
