<?php

namespace Drupal\datastore\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\LoggerTrait;
use Drupal\datastore\Service;
use Drupal\metastore\Storage\DataFactory;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ResourcePurger service.
 */
class ResourcePurger implements ContainerInjectionInterface {
  use LoggerTrait;

  /**
   * The datastore.settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * The dataset storage.
   *
   * @var \Drupal\metastore\Storage\Data
   */
  private $storage;

  /**
   * The datastore service.
   *
   * @var \Drupal\datastore\Service
   */
  private $datastore;

  /**
   * Constructs a ResourcePurger object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config.factory service.
   * @param \Drupal\metastore\Storage\DataFactory $dataFactory
   *   The dkan.metastore.storage service.
   * @param \Drupal\datastore\Service $datastore
   *   The dkan.datastore.service service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, DataFactory $dataFactory, Service $datastore) {
    $this->config = $configFactory->get('datastore.settings');
    $this->storage = $dataFactory->getInstance('dataset');
    $this->datastore = $datastore;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('dkan.metastore.storage'),
      $container->get('dkan.datastore.service')
    );
  }

  /**
   * Purge unneeded resources from every dataset, now or later.
   *
   * @param bool $deferred
   *   (optional) Whether to process the purge later. Defaults to TRUE.
   * @param bool $allRevisions
   *   (optional) Whether to include all dataset revisions. Defaults to FALSE.
   */
  public function schedulePurgingAll(bool $deferred = TRUE, bool $allRevisions = FALSE) {
    $uuids = $this->storage->getNodeStorage()
      ->getQuery()
      ->condition('type', 'data')
      ->condition('field_data_type', 'dataset')
      ->execute();

    $this->schedulePurging($uuids, $deferred, $allRevisions);
  }

  /**
   * Purge unneeded resources from one or more specific dataset, now or later.
   *
   * @param array $uuids
   *   Dataset identifiers.
   * @param bool $deferred
   *   (optional) Whether to process the purge later. Defaults to TRUE.
   * @param bool $allRevisions
   *   (optional) Whether to include all dataset revisions. Defaults to FALSE.
   */
  public function schedulePurging(array $uuids, bool $deferred = TRUE, bool $allRevisions = FALSE) {
    if (!$this->validatePurging()) {
      return;
    }
    if ($deferred) {
      $queue = $this->datastore->getQueueFactory()->get('resource_purger');
      $queueId = $queue->createItem([
        'uuids' => $uuids,
        'allRevisions' => $allRevisions,
      ]);
      // @todo Log message and include $queueId.
      $this->notice('Queued resource purging with queueId:%queueId uuids:%uuids',
        [
          '%queueId' => $queueId,
          '%uuids' => implode(', ', $uuids),
        ]
      );
    }
    else {
      $this->purgeMultiple($uuids, $allRevisions);
    }
  }

  /**
   * Purge unneeded resources of multiple datasets.
   */
  private function purgeMultiple(array $uuids, bool $allRevisions = FALSE) {
    if ($this->validatePurging()) {
      foreach ($uuids as $uuid) {
        $this->purgeSingle($uuid, $allRevisions);
      }
    }
  }

  /**
   * Purge a dataset's unneeded resources.
   */
  private function purgeSingle(string $uuid, bool $allRevisions = FALSE) {
    $dataset = $this->storage->getNodeLatestRevision($uuid);
    if (!$dataset) {
      return;
    }
    foreach ($this->getResourcesToPurge($dataset, $allRevisions) as [$id, $version]) {
      $this->purge($id, $version);
    }
  }

  /**
   * Purge a resource's file and/or table, based on enabled config settings.
   */
  private function purge(string $id, string $version) {
    if ($this->getPurgeFileSetting()) {
      $this->datastore->getResourceLocalizer()->remove($id, $version);
    }
    if ($this->getPurgeTableSetting()) {
      $this->datastore->getStorage($id, $version)->destroy();
    }
  }

  /**
   * Determine which resources from various dataset revisions can be purged.
   */
  private function getResourcesToPurge(NodeInterface $dataset, bool $allRevisions = FALSE) : array {
    $publishedCount = 0;
    $purge = $keep = [];

    $vids = array_reverse($this->storage->getNodeStorage()->revisionIds($dataset));
    foreach ($vids as $key => $vid) {
      $data = $this->getRevisionData($vid);
      $resource = $data['resource'];
      if ($published = $data['published']) {
        $publishedCount++;
      }
      if ($this->isRevisionNeeded($published, $publishedCount, $key, $vids)) {
        $keep[$vid] = $resource;
      }
      if ($this->isResourceUnneeded($resource, $keep, $purge)) {
        $purge[$vid] = $resource;
      }
      if ($this->isPurgeScopeReduced($publishedCount, $allRevisions)) {
        break;
      }
    }

    return $purge;
  }

  /**
   * Important revisions are the latest one, and the latest published one.
   */
  private function isRevisionNeeded(bool $published, int $publishedCount, int $key, array $vids) : bool {
    $isLatestRevision = $key === array_key_first($vids);
    $isLatestPublished = $published && $publishedCount == 1;
    return $isLatestRevision || $isLatestPublished;
  }

  /**
   * Unneeded resources have not yet been considered for keeping or discarding.
   */
  private function isResourceUnneeded(array $resource, array $keep, array $purge) : bool {
    $toKeep = in_array($resource, $keep);
    $toPurge = in_array($resource, $purge);
    return !$toKeep && !$toPurge;
  }

  /**
   * Determine if the scope of the purge is reduced or not.
   */
  private function isPurgeScopeReduced(int $publishedCount, bool $allRevisions) : bool {
    return !$allRevisions && $publishedCount >= 2;
  }

  /**
   * Get data about each revision of a dataset.
   */
  private function getRevisionData(string $vid) : array {
    $revision = $this->storage->getNodeStorage()->loadRevision($vid);
    return [
      'published' => $revision->get('moderation_state')->getString() == 'published',
      'resource' => $this->getResourceIdAndVersion($revision),
    ];
  }

  /**
   * Get a dataset's resource identifier and version.
   */
  private function getResourceIdAndVersion(NodeInterface $dataset) {
    $metadata = json_decode($dataset->get('field_json_metadata')->getString());
    $refDistData = $metadata->{'%Ref:distribution'}[0]->data;
    $resource = $refDistData->{'%Ref:downloadURL'}[0]->data;
    return [$resource->identifier, $resource->version];
  }

  /**
   * Get the purge_table value from datastore.settings config.
   */
  private function getPurgeTableSetting() : bool {
    return (bool) $this->config->get('purge_table');
  }

  /**
   * Get the purge_file value from datastore.settings config.
   */
  private function getPurgeFileSetting() : bool {
    return (bool) $this->config->get('purge_file');
  }

  /**
   * Verifies at least one purge setting is set to true.
   */
  private function validatePurging() : bool {
    return $this->getPurgeFileSetting() || $this->getPurgeTableSetting();
  }

}
