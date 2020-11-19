<?php

namespace Drupal\datastore\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\datastore\Service;
use Drupal\metastore\Storage\DataFactory;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ResourcePurger service.
 */
class ResourcePurger implements ContainerInjectionInterface {

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
    // @todo Get all dataset uuids.
    $uuids = [];
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
        $this->purge($uuid, $allRevisions);
      }
    }
  }

  /**
   * Purge a dataset's unneeded resources.
   */
  private function purge(string $uuid, bool $allRevisions = FALSE) {
    $dataset = $this->storage->getNodeLatestRevision($uuid);
    if (!$dataset) {
      return;
    }
    foreach ($this->getResourcesToPurge($dataset, $allRevisions) as [$id, $version]) {
      if ($this->getPurgeFileSetting()) {
        $this->datastore->getResourceLocalizer()->remove($id, $version);
      }
      if ($this->getPurgeTableSetting()) {
        $this->datastore->getStorage($id, $version)->destroy();
      }
    }
  }

  /**
   * Get resources to purge.
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
      if (($published && $publishedCount == 1) || ($key === array_key_first($vids))) {
        $keep[$vid] = $resource;
      }
      if (!(in_array($resource, $keep) || in_array($resource, $purge))) {
        $purge[$vid] = $resource;
      }
      if (!$allRevisions && $publishedCount >= 2) {
        break;
      }
    }

    return $purge;
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
