<?php

namespace Drupal\Tests\common\Unit\Storage;

use Drupal\common\Storage\Query;
use Drupal\common\Storage\SelectFactory;
use Drupal\Core\Database\Query\Select;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Drupal\Tests\common\Unit\Connection;

/**
 * @coversDefaultClass \Drupal\common\Storage\SelectFactory
 *
 * @group dkan
 * @group common
 * @group unit
 */
class SelectFactoryTest extends TestCase {
  /**
   * SelectFactory object.
   *
   * @var \Drupal\common\Storage\SelectFactory
   */
  private $selectFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->selectFactory = $this->getSelectFactory();
  }

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
   * @covers ::safeJoinOperator
   */
  public function testSafeJoinOperator() {
    foreach (['=', '!=', '<>', '>', '>=', '<', '<=', 'LIKE'] as $operator) {
      $this->assertTrue(SelectFactory::safeJoinOperator($operator));
    }
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Invalid join operator: foo');
    SelectFactory::safeJoinOperator('foo');
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

}
