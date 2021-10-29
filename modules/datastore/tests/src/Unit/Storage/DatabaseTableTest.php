<?php

namespace Drupal\Tests\datastore\Storage;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Database\Query\Insert;
use Drupal\Core\Database\Query\Select;
use Drupal\Core\Database\Driver\mysql\Schema;
use Drupal\Core\Database\Statement;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

use Drupal\common\Resource;
use Drupal\common\Storage\Query;
use Drupal\Component\DependencyInjection\Container;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\indexer\IndexManager;

use MockChain\Chain;
use MockChain\Options;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class DatabaseTableTest extends TestCase {

  /**
   * Initialize \Drupal::container with necessary services for tests.
   */
  protected function setUp(): void {
    parent::setUp();

    $public_stream = (new Chain($this))
      ->add(PublicStream::class, 'getExternalUrl', 'http://example.org/test.csv')
      ->getMock();
    $options = (new Options())
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->index(0);
    $container = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(StreamWrapperManager::class, 'getViaUri', $public_stream)
      ->getMock();

    \Drupal::setContainer($container);
  }

  /**
   * Test construction of `\Drupal\datastore\Storage\DatabaseTable` object.
   */
  public function testConstruction() {
    $databaseTable = new DatabaseTable(
      $this->getConnectionChain()->getMock(),
      $this->getResource()
    );
    $this->assertTrue(is_object($databaseTable));
  }

  /**
   *
   */
  public function testGetSchema() {
    $databaseTable = new DatabaseTable(
      $this->getConnectionChain()->getMock(),
      $this->getResource()
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
    ];

    $this->assertEquals($expectedSchema['fields'], $schema['fields']);
  }

  /**
   * Ensure indexer service is used during create table code flow.
   */
  public function testIndexerService() {
    // Stub event dispatcher service.
    $eventDispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    $container = new ContainerBuilder();
    $container->set('event_dispatcher', $eventDispatcher);
    \Drupal::setContainer($container);

    $schema = [
      "fields" => [
        "record_number" => [
          "type" => "serial",
          "unsigned" => TRUE,
          "not null" => TRUE,
        ],
      ],
    ];

    $connection = $this->getConnectionChain()
      ->add(Schema::class, "tableExists", FALSE)
      ->add(Schema::class, "createTable", FALSE)
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'countQuery', Select::class)
      ->add(Select::class, 'execute', Statement::class)
      ->add(Statement::class, 'fetchField', 1)
      ->getMock();

    $databaseTable = new DatabaseTable(
      $connection,
      $this->getResource()
    );

    $indexerClass = $this->getMockBuilder(IndexManager::class);
    $indexer = $indexerClass
      ->setMethods(["modifySchema"])
      ->getMock();
    $indexer->expects($this->once())
      ->method('modifySchema');
    $databaseTable->setIndexManager($indexer);
    $databaseTable->setSchema($schema);
    $databaseTable->count();
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
      ->add(Select::class, "execute", Statement::class)
      ->add(Statement::class, 'fetchAll', $sequence)
      ->getMock();

    $databaseTable = new DatabaseTable(
      $connection,
      $this->getResource()
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
      ->add(Select::class, 'execute', Statement::class)
      ->add(Statement::class, 'fetch', NULL);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource()
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
      ->add(Select::class, 'execute', Statement::class)
      ->add(Statement::class, 'fetch', NULL);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource()
    );
    $this->expectExceptionMessageRegExp("/The number of fields and data given do not match:/");
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
      ->add(Select::class, 'execute', Statement::class)
      ->add(Statement::class, 'fetch', NULL);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource()
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
      ->add(Select::class, 'execute', Statement::class)
      ->add(Statement::class, 'fetch', NULL);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource()
    );
    $data = [
      '["One"]',
      '["Two"]',
      '["Three"]',
    ];
    $this->expectExceptionMessageRegExp("/The number of fields and data given do not match:/");
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
      ->add(Select::class, 'execute', Statement::class)
      ->add(Statement::class, 'fetchField', 1);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource()
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
      ->add(Select::class, 'execute', Statement::class)
      ->add(Statement::class, 'fetchField', 1);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource()
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
  public function testDestroy() {
    $connectionChain = $this->getConnectionChain();

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource()
    );
    $databaseTable->destroy();
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
      ->add(Select::class, 'execute', Statement::class)
      ->add(Statement::class, 'fetch', NULL);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource()
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
      ->add(Select::class, 'execute', Statement::class)
      ->add(Statement::class, 'fetch', NULL);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource()
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
      ->add(Select::class, 'execute', Statement::class)
      ->add(Statement::class, 'fetchAll', []);

    $databaseTable = new DatabaseTable(
      $connectionChain->getMock(),
      $this->getResource()
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
      $this->getResource()
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
      $this->getResource()
    );

    $this->expectExceptionMessage("Column not found");
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

    $chain = (new Chain($this))
      // Construction.
      ->add(Connection::class, "schema", Schema::class)
      ->add(Connection::class, 'query', Statement::class)
      ->add(Statement::class, 'fetchAll', $fieldInfo)
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
    return new Resource('http://example.org/test.csv', 'text/csv');
  }

}
