<?php

namespace Drupal\Tests\metastore\Unit;

use Drupal\content_moderation\Plugin\WorkflowType\ContentModeration;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\metastore\Storage\Data;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\workflows\Entity\Workflow;
use MockChain\Chain;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;

/**
 * Tests Drupal\metastore\Storage\Data.
 *
 * @coversDefaultClass \Drupal\metastore\Storage\Data
 * @group common
 */
class DataTest extends TestCase {

  protected $node1;
  protected $node2;
  protected $node3;

  /**
   *
   */
  public function testRetrieveAllMissingSchema() {
    $data = new Data($this->getEntityTypeManager()->getMock());
    $this->expectExceptionMessage('Data schema id not set.');
    $data->retrieveAll();
  }

  /**
   *
   */
  public function testRetrieveAll() {
    $item = '{"foo":"bar"}';
    $data = new Data($this->getEntityTypeManager()->getMock());
    $data->setSchema('foobar');
    $result = $data->retrieveAll();
    $this->assertEquals([$item, $item], $result);
  }

  /**
   *
   */
  public function testRetrievePublishedMissingSchema() {
    $data = new Data($this->getEntityTypeManager()->getMock());
    $this->expectExceptionMessage('Data schema id not set.');
    $data->retrievePublished('1');
  }

  /**
   *
   */
  public function testRetrievePublished() {
    $data = new Data($this->getEntityTypeManager()->getMock());
    $data->setSchema('dataset');
    $expected = '{"foo":"bar"}';
    $result = $data->retrievePublished('1');
    $this->assertEquals($expected, $result);
  }

  /**
   *
   */
  public function testRetrievePublishedNotFound() {
    $entityTypeManager = $this->getEntityTypeManager()
      ->add(QueryInterface::class, 'execute', []);

    $data = new Data($entityTypeManager->getMock());
    $data->setSchema('dataset');
    $this->expectExceptionMessage('No data with that identifier was found.');
    $data->retrievePublished('1');
  }

  /**
   *
   */
  public function testRetrieveMissingSchema() {
    $data = new Data($this->getEntityTypeManager()->getMock());
    $this->expectExceptionMessage('Data schema id not set.');
    $data->retrieve('1');
  }

  /**
   *
   */
  public function testRetrieve() {
    $this->node1 = $this->setNodeMock('1', '{"foo":"bar"}', "");
    $data = new Data($this->getEntityTypeManager()->getMock());
    $data->setSchema('dataset');

    $expected = '{"foo":"bar"}';
    $result = $data->retrieve('1');

    $this->assertEquals($expected, $result);
  }

  /**
   *
   */
  public function testRetrieveNotFound() {
    $entityTypeManager = $this->getEntityTypeManager()
      ->add(QueryInterface::class, 'execute', []);

    $data = new Data($entityTypeManager->getMock());
    $data->setSchema('dataset');
    $this->expectExceptionMessage('No data with that identifier was found.');
    $data->retrieve('4');
  }

  /**
   *
   */
  public function testPublishMissingSchema() {
    $data = new Data($this->getEntityTypeManager()->getMock());
    $this->expectExceptionMessage('Data schema id not set.');
    $data->publish('1');
  }

  /**
   *
   */
  public function testPublishNonDataset() {
    $data = new Data($this->getEntityTypeManager()->getMock());
    $data->setSchema('foobar');
    $this->expectExceptionMessage('Publishing currently only implemented for datasets.');
    $data->publish('1');
  }

  /**
   *
   */
  public function testPublish() {
    $data = new Data($this->getEntityTypeManager()->getMock());
    $data->setSchema('dataset');
    $result = $data->publish('1');
    $this->assertEquals($result, '1');
  }

  /**
   *
   */
  public function testPublishNotFound() {
    $entityTypeManager = $this->getEntityTypeManager()
      ->add(NodeStorageInterface::class, 'loadRevision', NULL);

    $data = new Data($entityTypeManager->getMock());
    $data->setSchema('dataset');
    $this->expectExceptionMessage('No data with that identifier was found.');
    $data->publish('2');
  }

  /**
   *
   */
  public function testStoreUpdateExistingNode() {
    $data = new Data($this->getEntityTypeManager()->getMock());
    $data->setSchema('dataset');
    $id = $data->store('{"foo":"bar"}', 1);
    $this->assertEquals(1, $id);
  }

  /**
   *
   */
  public function testStoreCreateNewNode() {
    $data = new Data($this->getEntityTypeManager()->getMock());
    $data->setSchema('dataset');
    $id = $data->store('{"title":"foobar", "number":1}', NULL);
    $this->assertEquals(1, $id);
  }

  /**
   *
   */
  public function testRemove() {
    $data = new Data($this->getEntityTypeManager()->getMock());
    $removed = $data->remove('1');
    $this->assertEquals(NULL, $removed);
  }

  /**
   *
   */
  public function testRemoveNotFound() {
    $entityTypeManager = $this->getEntityTypeManager()
      ->add(QueryInterface::class, 'execute', []);

    $data = new Data($entityTypeManager->getMock());
    $removed = $data->remove('4');
    $this->assertEquals(NULL, $removed);
  }

  /**
   *
   */
  private function setNodeMock($uuid, $firstString, $secondString) {
    $firstGetStringValue = (new Chain($this))
      ->add(FieldItemInterface::class, 'getString', $firstString)
      ->getMock();
    $secondGetStringValue = (new Chain($this))
      ->add(FieldItemInterface::class, 'getString', $secondString)
      ->getMock();
    $fields = (new Sequence())
      ->add($firstGetStringValue)
      ->add($secondGetStringValue);

    return (new Chain($this))
      ->add(NodeInterface::class, 'get', $fields)
      ->add(NodeInterface::class, 'uuid', $uuid)
      ->add(NodeInterface::class, 'save', '1')
      ->getMock();
  }

  /**
   *
   */
  private function getEntityTypeManager() {
    $storages = (new Sequence())
      ->add(NodeStorageInterface::class)
      ->add(ConfigEntityStorage::class);

    if (!isset($this->node1)) {
      $this->node1 = $this->setNodeMock('1', 'published', '{"foo":"bar"}');
    }
    if (!isset($this->node2)) {
      $this->node2 = $this->setNodeMock('2', 'draft', '{"foo":"bar"}');
    }
    if (!isset($this->node3)) {
      $this->node3 = $this->setNodeMock('3', 'published', '{"foo":"bar"}');
    }

    $nodes = (new Sequence())
      ->add($this->node1)
      ->add($this->node2)
      ->add($this->node3);

    return (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', $storages)
      ->add(NodeStorageInterface::class, 'getQuery', QueryInterface::class)
      ->add(QueryInterface::class, 'condition', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', [1, 2, 3])
      ->add(NodeStorageInterface::class, 'load', $nodes)
      ->add(NodeStorageInterface::class, 'create', $this->node1)
      ->add(NodeStorageInterface::class, 'getLatestRevisionId', '1')
      ->add(NodeStorageInterface::class, 'loadRevision', $this->node1)
      ->add(ConfigEntityStorage::class, 'load', Workflow::class)
      ->add(Workflow::class, 'getTypePlugin', ContentModeration::class)
      ->add(ContentModeration::class, 'getConfiguration', [
        'default_moderation_state' => 'published',
      ]
      );
  }

}
