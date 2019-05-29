<?php

namespace Drupal\Tests\dkan_datastore\Unit\Controller;

use Dkan\Datastore\Manager\SimpleImport\SimpleImport;
use Drupal\dkan_datastore\Controller\Api;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_datastore\Query;
use Drupal\dkan_datastore\SqlParser;

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
    $o1->setThingToRetrieve("dkan_datastore_1");

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

    return [
      [
        '[SELECT * FROM abc];',
        $o1,
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

    $manager = $this->getMockBuilder(SimpleImport::class)
      ->disableOriginalConstructor()
      ->setMethods(['getTableName'])
      ->getMock();

    $manager->expects($this->once())->method('getTableName')->willReturn("dkan_datastore_1");

    $controller->expects($this->once())->method("getDatastoreManager")->willReturn($manager);

    $parser = new SqlParser();
    $parser->validate($sqlString);
    $object = $this->invokeProtectedMethod($controller, 'getQueryObject', $parser->getValidatingMachine());

    $this->assertEquals(json_encode($expected), json_encode($object));
  }

}
