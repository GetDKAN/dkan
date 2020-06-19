<?php

namespace Drupal\Tests\common\Unit\Storage;

use Drupal\content_moderation\Plugin\WorkflowType\ContentModeration;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\metastore\Storage\Data;
use Drupal\node\Entity\Node;
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
      ->add(NodeStorageInterface::class, 'loadByProperties', []);

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
    $jsonMetadataField = (new Chain($this))
      ->add(FieldItemInterface::class, 'getString', '{"foo":"bar"}')
      ->getMock();

    $node = (new Chain($this))
      ->add(NodeInterface::class, 'get', $jsonMetadataField)
      ->getMock();

    $entityTypeManager = $this->getEntityTypeManager()
      ->add(NodeStorageInterface::class, 'loadRevision', $node);
    $data = new Data($entityTypeManager->getMock());
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
      ->add(NodeStorageInterface::class, 'loadRevision', NULL);

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
      ->add(NodeStorageInterface::class, 'load', NULL);

    $data = new Data($entityTypeManager->getMock());
    $data->setSchema('dataset');
    $this->expectExceptionMessage('No data with that identifier was found.');

    $data->remove('1');
  }

  /**
   *
   */
  private function setNodeMock($uuid, $moderationState) {
    $jsonMetadataField = (new Chain($this))
      ->add(FieldItemInterface::class, 'getString', '{"foo":"bar"}')
      ->getMock();
    $moderationStateField = (new Chain($this))
      ->add(FieldItemInterface::class, 'getString', $moderationState)
      ->getMock();
    $fields = (new Sequence())
      ->add($moderationStateField)
      ->add($jsonMetadataField);

    return (new Chain($this))
      ->add(NodeInterface::class, 'get', $fields)
      ->add(NodeInterface::class, 'uuid', $uuid)
      ->add(NodeInterface::class, 'save', '1')
      ->getMock();
  }

  /**
   *
   */
  private function publishedNode() {
    return $this->setNodeMock('1', 'published');
  }

  /**
   *
   */
  private function draftNode() {
    return $this->setNodeMock('2', 'draft');
  }

  /**
   *
   */
  private function getEntityTypeManager() {
    $storages = (new Sequence())
      ->add(NodeStorageInterface::class)
      ->add(ConfigEntityStorage::class);

    $node1 = $this->publishedNode();

    $nodes = (new Sequence())
      ->add($node1)
      ->add($this->draftNode())
      ->add($this->publishedNode());

    return (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', $storages)
      ->add(NodeStorageInterface::class, 'getQuery', QueryInterface::class)
      ->add(QueryInterface::class, 'condition', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', [1, 2, 3])
      ->add(NodeStorageInterface::class, 'load', $nodes)
      ->add(NodeStorageInterface::class, 'loadByProperties', [$node1])
      ->add(NodeStorageInterface::class, 'create', $node1)
      ->add(NodeStorageInterface::class, 'getLatestRevisionId', '1')
      ->add(NodeStorageInterface::class, 'loadRevision', $node1)
      ->add(ConfigEntityStorage::class, 'load', Workflow::class)
      ->add(Workflow::class, 'getTypePlugin', ContentModeration::class)
      ->add(ContentModeration::class, 'getConfiguration', [
        'default_moderation_state' => 'published']
      );
  }

}
