<?php

namespace Drupal\Tests\dkan_datastore\Unit\Manager;

use Dkan\Datastore\Manager;
use Dkan\Datastore\Resource;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\DataType\FieldItem;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_datastore\Manager\Builder;
use Drupal\dkan_datastore\Manager\Helper;
use Drupal\dkan_datastore\Storage\Database;
use Drupal\node\Entity\Node;

/**
 * @coversDefaultClass Drupal\dkan_datastore\Manager\Builder
 * @group              dkan_datastore
 */
class BuilderTest extends DkanTestBase {

  /**
   * @var \Drupal\dkan_datastore\Manager\Builder
   */
  private $builder;

  /**
   * Public.
   */
  public function setUp() {
    parent::setUp();

    $field = $this->getMockBuilder(FieldItem::class)
      ->disableOriginalConstructor()
      ->setMethods(['getValue'])
      ->getMock();
    $field->method('getValue')->willReturn(
          [
    'value' =>
          json_encode((object) ['data' => (object) ['downloadURL' => "http://blah"]])
  ]
      );

    $field_list = $this->getMockBuilder(FieldItemList::class)
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMock();

    $field_list->method("get")->willReturn($field);

    $entity = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->setMethods(['get', 'id'])
      ->getMock();
    $entity->method('get')->willReturn($field_list);
    $entity->method('id')->willReturn("1");

    $entity_repository = $this->getMockBuilder(EntityRepository::class)
      ->disableOriginalConstructor()
      ->setMethods(["loadEntityByUuid"])
      ->getMock();
    $entity_repository->method('loadEntityByUuid')->willReturn($entity);

    $database = $this->getMockBuilder(Database::class)
      ->disableOriginalConstructor()
      ->getMock();

    $helper = new Helper($entity_repository, $database);
    $this->builder = new Builder($helper);
  }

  /**
   * Public.
   */
  public function testBuild() {
    $this->builder->setResource(new Resource("1", "blah.txt"));
    $manager = $this->builder->build();
    $this->assertEquals(get_class($manager), Manager::class);
  }

  /**
   * Public.
   */
  public function testUuidBuil() {
    $this->builder->setResourceFromUUid("blah");
    $manager = $this->builder->build();
    $this->assertEquals(get_class($manager), Manager::class);
  }

}
