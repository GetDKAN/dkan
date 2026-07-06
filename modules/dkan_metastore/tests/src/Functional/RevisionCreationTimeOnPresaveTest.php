<?php

namespace Drupal\Tests\dkan_metastore\Functional;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\dkan_common\Traits\GetDataTrait;
use Drupal\node\NodeInterface;

/**
 * @group dkan
 * @group metastore
 * @group functional
 * @group btb
 * @group functional1
 */
class RevisionCreationTimeOnPresaveTest extends BrowserTestBase {
  use GetDataTrait;

  protected static $modules = [
    'dkan_datastore',
    'dkan_metastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

  /**
   * Ensures new revisions get a fresh revision creation timestamp.
   */
  public function test() {
    /** @var \Drupal\dkan_metastore\MetastoreService $metastore */
    $metastore = $this->container->get('dkan.metastore.service');
    $metadata = $metastore->getValidMetadataFactory()->get(
      $this->getDataset('revision-time-test', 'Revision Time Test', ['district_centerpoints_small.csv']),
      'dataset'
    );
    $identifier = $metastore->post('dataset', $metadata);

    /** @var \Drupal\dkan_metastore\Storage\DataFactory $storage_factory */
    $storage_factory = $this->container->get('dkan.metastore.storage');
    /** @var \Drupal\dkan_metastore\Storage\MetastoreEntityStorageInterface $storage */
    $storage = $storage_factory->getInstance('dataset');

    $entity = $storage->getEntityLatestRevision($identifier);
    $this->assertNotEmpty($entity, 'Dataset entity is available for revision testing.');
    $this->assertInstanceOf(NodeInterface::class, $entity);
    $this->assertInstanceOf(RevisionLogInterface::class, $entity);
    /** @var \Drupal\node\NodeInterface&\Drupal\Core\Entity\RevisionLogInterface $entity */

    $past_timestamp = 1000;
    $entity->setRevisionCreationTime($past_timestamp);
    $entity->save();

    $entity = $storage->getEntityLatestRevision($identifier);
    $this->assertInstanceOf(NodeInterface::class, $entity);
    $this->assertInstanceOf(RevisionLogInterface::class, $entity);
    /** @var \Drupal\node\NodeInterface&\Drupal\Core\Entity\RevisionLogInterface $entity */
    $previous_revision_id = $entity->getRevisionId();

    $entity->setNewRevision();
    $entity->setRevisionLogMessage('Create a new revision in functional test.');
    $entity->setTitle('Revision Time Test Updated');
    $save_start = \Drupal::time()->getCurrentTime();
    $entity->save();

    $latest_revision = $storage->getEntityLatestRevision($identifier);
    $this->assertInstanceOf(RevisionLogInterface::class, $latest_revision);
    /** @var \Drupal\Core\Entity\RevisionLogInterface $latest_revision */

    $this->assertGreaterThan(
      $previous_revision_id,
      $latest_revision->getRevisionId(),
      'A new revision was created.'
    );
    $this->assertNotEquals(
      $past_timestamp,
      $latest_revision->getRevisionCreationTime(),
      'Revision creation time is not copied from the previous revision.'
    );
    $this->assertGreaterThanOrEqual(
      $save_start,
      $latest_revision->getRevisionCreationTime(),
      'Revision creation time is updated to the current save time.'
    );
  }

}
