<?php

namespace Drupal\Tests\dkan_datastore\Unit\Manager;

use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\Plugin\DataType\FieldItem;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_datastore\Manager\Helper;
use Drupal\dkan_datastore\Storage\Database;
use Drupal\node\Entity\Node;

/**
 * @coversDefaultClass Drupal\dkan_datastore\Manager\Helper
 * @group              dkan_datastore
 */
class HelperTest extends DkanTestBase {

  /**
   * Public.
   */
  public function testNoMetadata() {
    $field = $this->getMockBuilder(FieldItem::class)
      ->disableOriginalConstructor()
      ->setMethods(['getValue'])
      ->getMock();
    $field->method('getValue')->willReturn(NULL);

    $field_list = $this->getMockBuilder(FieldItemList::class)
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMock();

    $field_list->method("get")->willReturn($field);

    $entity = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->setMethods(['get', 'id', 'uuid'])
      ->getMock();
    $entity->method('uuid')->willReturn("blah");
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

    $this->expectExceptionMessage("Entity for blah does not have required field `field_json_metadata`.");
    $helper->getResourceFromEntity("blah");
  }

  /**
   * Public.
   */
  public function testNoObjectMetadata() {
    $field = $this->getMockBuilder(FieldItem::class)
      ->disableOriginalConstructor()
      ->setMethods(['getValue'])
      ->getMock();
    $field->method('getValue')->willReturn(
          [
    'value' =>
          json_encode([])
  ]
      );

    $field_list = $this->getMockBuilder(FieldItemList::class)
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMock();

    $field_list->method("get")->willReturn($field);

    $entity = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->setMethods(['get', 'id', 'uuid'])
      ->getMock();
    $entity->method('uuid')->willReturn("blah");
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

    $this->expectExceptionMessage("Invalid metadata information or missing file information.");
    $helper->getResourceFromEntity("blah");
  }

  /**
   * Public.
   */
  public function testBadMetadata() {
    $field = $this->getMockBuilder(FieldItem::class)
      ->disableOriginalConstructor()
      ->setMethods(['getValue'])
      ->getMock();
    $field->method('getValue')->willReturn(
          [
    'value' =>
          json_encode((object) [])
  ]
      );

    $field_list = $this->getMockBuilder(FieldItemList::class)
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMock();

    $field_list->method("get")->willReturn($field);

    $entity = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->setMethods(['get', 'id', 'uuid'])
      ->getMock();
    $entity->method('uuid')->willReturn("blah");
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

    $this->expectExceptionMessage("Invalid metadata information or missing file information.");
    $helper->getResourceFromEntity("blah");
  }

}
