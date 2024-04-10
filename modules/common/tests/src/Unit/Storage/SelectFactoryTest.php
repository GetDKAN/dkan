<?php

namespace Drupal\Tests\common\Unit\Storage;

use Drupal\common\Storage\Query;
use Drupal\common\Storage\SelectFactory;
use Drupal\Core\Database\Query\Select;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Drupal\Tests\common\Unit\Connection;

/**
 *
 */
class SelectFactoryTest extends TestCase {
  private $query;

  /**
   * SelectFactory object.
   *
   * @var \Drupal\common\Storage\SelectFactory
   */
  private $selectFactory;

  /**
   * @test
   *
   * @dataProvider \Drupal\Tests\common\Unit\Storage\QueryDataProvider::getAllData()
   */
  public function testQuery(Query $query, string $sql, string $message, array $values = []) {
    if ($message) {
      $this->expectExceptionMessage($message);
      $this->selectFactory->create($query);
    }
    else {
      $db_query = $this->selectFactory->create($query);
      $this->assertStringContainsString($sql, $this->selectToString($db_query));

      if (!empty($values)) {
        $this->assertEquals($values, array_values($db_query->arguments()));
      }
    }
  }
  
  /**
   * Test two variations of Query::testConditionByIsEqualTo()
   */
  public function testConditionByIsEqualTo() {
    $query = new Query();
    $query->properties = ["field1", "field2"];
    $query->conditionByIsEqualTo('prop1', 'value1');
    $db_query = $this->selectFactory->create($query);
    $this->assertStringContainsString('t.prop1 LIKE :db_condition_placeholder_0', $this->selectToString($db_query));
  }

  public function testConditionByIsEqualToCaseInsensitive() {
    $query = new Query();
    $query->conditionByIsEqualTo('prop1', 'value1', TRUE);
    $db_query = $this->selectFactory->create($query);
    $this->assertStringContainsString('t.prop1 LIKE BINARY :db_condition_placeholder_0', $this->selectToString($db_query));
  }

  /**
   * Test two variations of Query::testConditionByIsEqualTo()
   */
  public function testAddDateExpressions() {
    $query = new Query();
    $query->dataDictionaryFields = [
      [
        'name' => 'date',
        'type' => 'date',
        'format'=>'%m/%d/%Y',
      ]
    ];
    $query->properties = ["date", "field2"];
    $db_query = $this->selectFactory->create($query);
    $this->assertStringContainsString("DATE_FORMAT(date, '%m/%d/%Y') AS date", $this->selectToString($db_query));
  }

  /**
   *
   */
  private function getSelectFactory() {
    return new SelectFactory($this->getConnection());
  }

  /**
   *
   */
  private function getConnection() {
    return (new Chain($this))
      ->add(
        Connection::class,
        "select",
        new Select(new Connection(new \PDO('sqlite::memory:'), []), "table", "t")
      )
      ->getMock();
  }

  /**
   *
   */
  private function selectToString(Select $select): string {
    return preg_replace("/\n/", " ", "$select");
  }

  /**
   *
   */
  private function queryDebug() {
    print_r($this->query);
    print "\n" . $this->selectToString($this->selectFactory->create($this->query));
  }

  /**
   *
   */
  public function setUp():void {
    $this->query = new Query();
    $this->selectFactory = $this->getSelectFactory();
  }

}
