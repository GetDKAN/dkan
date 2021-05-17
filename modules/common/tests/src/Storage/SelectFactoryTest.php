<?php

namespace Drupal\Tests\common\Storage;

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
  private $selectFactory;

  /**
   * @test
   *
   * @dataProvider Drupal\Tests\common\Unit\Storage\QueryDataProvider::getAllData()
   */
  public function testQuery(Query $query, string $sql, string $message) {
    if ($message) {
      $this->expectExceptionMessage($message);
      $this->selectFactory->create($query);
    }
    else {
      $db_query = $this->selectFactory->create($query);
      $this->assertContains($sql, $this->selectToString($db_query));
    }
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
  public function setUp() {
    $this->query = new Query();
    $this->selectFactory = $this->getSelectFactory();
  }

}
