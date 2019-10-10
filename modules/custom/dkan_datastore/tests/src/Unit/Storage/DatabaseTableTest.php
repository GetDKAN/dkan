<?php

namespace Drupal\Tests\dkan_datastore\Unit\Storage;

use Dkan\Datastore\Resource;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Insert;
use Drupal\Core\Database\Query\Select;
use Drupal\Core\Database\Schema;
use Drupal\Core\Database\Statement;
use Drupal\dkan_common\Tests\Mock\Chain;
use Drupal\dkan_datastore\Storage\DatabaseTable;
use Drupal\dkan_datastore\Storage\TableSummary;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class DatabaseTableTest extends TestCase {

  /**
   *
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
  public function testRetrieve() {
    $databaseTable = new DatabaseTable(
      $this->getConnectionChain()->getMock(),
      $this->getResource()
    );
    $this->assertEquals("1", $databaseTable->retrieve("1"));
  }

  /**
   *
   */
  public function testRetrieveAll() {
    $databaseTable = new DatabaseTable(
      $this->getConnectionChain()->getMock(),
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
      ->add(Insert::class, 'execute', NULL);

    $databaseTable = new DatabaseTable(
     $connectionChain->getMock(),
      $this->getResource()
    );
    $this->assertEquals("SUCCESS", $databaseTable->store('["Gerardo", "Gonzalez"]'));
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
    $this->assertEquals(
      new TableSummary(
        2,
        ["first_name", "last_name"],
        1
      ), $databaseTable->getSummary());
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
  private function getConnectionChain() {
    $fieldInfo = [
      (object) ['Field' => "first_name"],
      (object) ['Field' => "last_name"],
    ];

    $chain = (new Chain($this))
      // Construction.
      ->add(Connection::class, "schema", Schema::class)
      ->add(Connection::class, 'query', Statement::class)
      ->add(Statement::class, 'fetchAll', $fieldInfo)
      ->add(Schema::class, "tableExists", TRUE);

    return $chain;
  }

  /**
   *
   */
  private function getResource() {
    return new Resource("people", "");
  }

}
