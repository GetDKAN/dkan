<?php

namespace Drupal\Tests\dkan_datastore\Unit\Controller;

use Dkan\Datastore\Manager;
use Dkan\Datastore\Resource;
use Drupal\dkan_datastore\Manager\Helper;
use Drupal\dkan_datastore\Storage\Database;
use Drupal\dkan_sql_endpoint\Controller\Api;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_datastore\Storage\Query;

use SqlParser\SqlParser;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @coversDefaultClass \Drupal\dkan_datastore\Controller\Api
 * @group dkan
 */
class ApiTest extends DkanTestBase {

  /**
   * Data provider.
   */
  public function dataTest() {

    $o1 = new Query();

    $o2 = clone $o1;
    $o2->filterByProperty('city');
    $o2->filterByProperty('state');

    $o3 = clone $o2;
    $o3->conditionByIsEqualTo('def', 'hij');
    $o3->conditionByIsEqualTo('klm', 'nop');

    $o4 = clone $o2;
    $o4->sortByAscending('qrs');

    $o5 = clone $o2;
    $o5->sortByDescending('qrs');

    $o6 = clone $o2;
    $o6->limitTo(4);
    $o6->offsetBy(5);

    $o7 = clone $o1;
    $o7->count();

    return [
      [
        '[SELECT * FROM abc];',
        $o1,
      ],
      [
        '[SELECT COUNT(*) FROM abc];',
        $o7,
      ],
      [
        '[SELECT city,state FROM abc];',
        $o2,
      ],
      [
        "[SELECT city,state FROM abc][WHERE def = 'hij' AND klm = 'nop'];",
        $o3,
      ],
      [
        "[SELECT city,state FROM abc][ORDER BY qrs ASC];",
        $o4,
      ],
      [
        "[SELECT city,state FROM abc][ORDER BY qrs DESC];",
        $o5,
      ],
      [
        "[SELECT city,state FROM abc][LIMIT 4 OFFSET 5];",
        $o6,
      ],
    ];
  }

  /**
   * Tests explode().
   *
   * @param string $sqlString
   *   A sql string.
   * @param mixed $expected
   *   The object that the getQueryObject function should return.
   *
   * @dataProvider dataTest
   */
  public function testGetQueryObject($sqlString, $expected) {
    $controller = $this->getMockBuilder(Api::class)
      ->disableOriginalConstructor()
      ->setMethods(['getDatastoreManager'])
      ->getMock();

    $manager = $this->getMockBuilder(Manager::class)
      ->disableOriginalConstructor()
      ->getMock();

    $controller->method("getDatastoreManager")->willReturn($manager);

    $parser = new SqlParser();
    $parser->validate($sqlString);
    $object = $this->invokeProtectedMethod($controller, 'getQueryObject', $parser->getValidatingMachine());

    $this->assertEquals(json_encode($expected), json_encode($object));
  }

  public function testRunQuery() {

    $helper = $this->getMockBuilder(Helper::class)
      ->disableOriginalConstructor()
      ->setMethods(['getResourceFromEntity'])
      ->getMock();

    $helper->method('getResourceFromEntity')->willReturn(new Resource("1", "blah"));

    $controller = $this->getMockBuilder(Api::class)
      ->disableOriginalConstructor()
      ->setMethods(['getParser', 'getDatabase', 'getQueryObject', 'response', 'getDatastoreManagerBuilderHelper'])
      ->getMock();

    $controller->method('getParser')->willReturn(new SqlParser());
    $controller->method('getQueryObject')->willReturn(new Query());
    $controller->method('response')->willReturn(new JsonResponse([], 200));
    $controller->method('getDatastoreManagerBuilderHelper')->willReturn($helper);


    $database = $this->getMockBuilder(Database::class)
      ->disableOriginalConstructor()
      ->setMethods(['query', 'setResource'])
      ->getMock();

    $database->method('query')->willReturn([]);

    $controller->method('getDatabase')->willReturn($database);

    $response = $controller->runQuery('[SELECT * FROM abc];');
    $this->assertTrue(is_object($response));
  }

}
