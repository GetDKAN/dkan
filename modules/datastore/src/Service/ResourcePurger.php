<?php

namespace Drupal\datastore\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

use Drupal\common\LoggerTrait;
use Drupal\common\DataResource;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\datastore\DatastoreService;
use Drupal\metastore\ReferenceLookupInterface;
use Drupal\metastore\Storage\DataFactory;
use Drupal\node\NodeInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Resource purger service.
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
   * The dkan.metastore.reference_lookup service.
   *
   * @var \Drupal\metastore\ReferenceLookupInterface
   */
  private $referenceLookup;

  /**
   * The datastore service.
   *
   * @var \Drupal\datastore\DatastoreService
   */
  private $datastore;

  /**
   * The dataset storage.
   *
   * @var \Drupal\metastore\Storage\Data
   */
  private $storage;

  /**
   * Constructs a ResourcePurger object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config.factory service.
   * @param \Drupal\metastore\ReferenceLookupInterface $referenceLookup
   *   The dkan.metastore.reference_lookup service.
   * @param \Drupal\metastore\Storage\DataFactory $dataFactory
   *   The dkan.metastore.storage service.
   * @param \Drupal\datastore\DatastoreService $datastore
   *   The dkan.datastore.service service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, ReferenceLookupInterface $referenceLookup, DataFactory $dataFactory, DatastoreService $datastore) {
    $this->config = $configFactory->get('datastore.settings');
    $this->referenceLookup = $referenceLookup;
    $this->storage = $dataFactory->getInstance('dataset');
    $this->datastore = $datastore;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('dkan.metastore.reference_lookup'),
      $container->get('dkan.metastore.storage'),
      $container->get('dkan.datastore.service')
    );
  }

  /**
   * Validate and schedule the purging of unneeded resources.
   *
   * @param array $uuids
   *   Dataset identifiers.
   * @param bool $deferred
   *   Defaults to TRUE to process later, otherwise now.
   * @param bool $prior
   *   Defaults to FALSE to only consider last 2 published revisions, otherwise
   *   consider all older revisions.
   */
  public function schedule(array $uuids = [], bool $deferred = TRUE, bool $prior = FALSE) {
    $indexedUuids = $this->getIndexedUuids($uuids);
    if (!$this->validate() || empty($indexedUuids)) {
      return;
    }
    if ($deferred) {
      $this->queue($indexedUuids, $prior);
    }
    else {
      $this->purgeMultiple($indexedUuids, $prior);
    }
  }

  /**
   * Get all dataset uuids, and schedule the purge of their unneeded resources.
   *
   * @param bool $deferred
   *   Defaults to TRUE to process later, otherwise now.
   * @param bool $prior
   *   Defaults to FALSE to only consider last 2 published revisions, otherwise
   *   consider all older revisions.
   */
  public function scheduleAllUuids(bool $deferred = TRUE, bool $prior = FALSE) {

    $uuids = [];
    $nodeStorage = $this->storage->getEntityStorage();

    $nids = $nodeStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'data')
      ->condition('field_data_type', 'dataset')
      ->execute();

    foreach ($nids as $nid) {
      if ($node = $nodeStorage->load($nid)) {
        $uuids[] = $node->uuid();
      }
    }

    $this->schedule($uuids, $deferred, $prior);
  }

  /**
   * Schedule purging to run at a later time.
   *
   * @param array $uuids
   *   The dataset identifiers.
   * @param bool $prior
   *   Whether to include all prior revisions.
   */
  private function queue(array $uuids, bool $prior) {
    $queue = $this->datastore->getQueueFactory()->get('resource_purger');
    $queueId = $queue->createItem([
      'uuids' => $uuids,
      'prior' => $prior,
    ]);
    $this->notice('Queued resource purging with queueId:%queueId uuids:%uuids', [
      '%queueId' => $queueId,
      '%uuids' => implode(', ', $uuids),
    ]);
  }

  /**
   * Purge unneeded resources of multiple datasets.
   *
   * @param array $uuids
   *   The dataset identifiers.
   * @param bool $prior
   *   Whether to include all prior revisions.
   */
  public function purgeMultiple(array $uuids, bool $prior = FALSE) {
    if ($this->validate()) {
      foreach ($uuids as $vid => $uuid) {
        $this->purgeHelper($vid, $uuid, $prior);
      }
    }
  }

  /**
   * Helps reduce code complexity between purgeMultiple and purge.
   *
   * @param int $vid
   *   The dataset revision.
   * @param string $uuid
   *   The dataset identifier.
   * @param bool $prior
   *   Whether to include all prior revisions.
   */
  public function purgeHelper(int $vid, string $uuid, bool $prior) {
    try {
      $this->purge($vid, $uuid, $prior);
    }
    catch (\Exception $e) {
      $this->error("Error purging uuid {$uuid}, revision id {$vid}: " . $e->getMessage());
    }
  }

  /**
   * Purge a dataset's unneeded resources.
   *
   * @param int $vid
   *   The dataset revision.
   * @param string $uuid
   *   The dataset identifier.
   * @param bool $prior
   *   Whether to include all prior revisions.
   */
  private function purge(int $vid, string $uuid, bool $prior) {
    $node = $this->storage->getEntityStorage()->loadRevision($vid);
    if (!$node) {
      return;
    }
    $keep = $this->getResourcesToKeep($uuid);
    $purge = $this->getResourcesToPurge($vid, $node, $prior);

    foreach (array_diff($purge, $keep) as $idAndVersion) {
      // $idAndVersion is a json encoded array with resource's id and version.
      [$id, $version] = json_decode($idAndVersion);
      $this->delete($id, $version);
    }
  }

  /**
   * Determine which resources from various dataset revisions can be purged.
   *
   * @param int $initialVid
   *   The initial dataset revision from which to search resources to purge.
   * @param \Drupal\node\NodeInterface $node
   *   The dataset.
   * @param bool $prior
   *   Whether to include all prior revisions.
   *
   * @return array
   *   Array of revisions whose resource may be purged.
   */
  private function getResourcesToPurge(int $initialVid, NodeInterface $node, bool $prior) : array {
    $publishedCount = 0;
    $purge = [];

    foreach ($this->getOlderRevisionIds($initialVid, $node) as $vid) {
      [$published, $resource] = $this->getRevisionData($vid);
      $purge = array_merge($purge, $resource);
      $publishedCount = $published ? $publishedCount + 1 : $publishedCount;
      if (!$prior && $publishedCount >= 2) {
        break;
      }
    }

    // Remove duplicates and filter out resources in use elsewhere.
    return array_filter(array_unique($purge), [$this, 'resourceNotShared']);
  }

  /**
   * Determine whether a resource is in use by only one distribution.
   *
   * @param string $resource_details
   *   A JSON array of identifying resource details (id and version).
   *
   * @return bool
   *   Whether the given resource is being used by a single distribution.
   */
  private function resourceNotShared(string $resource_details): bool {
    // Extract the identifier and version from the supplied resource details.
    $identifier = DataResource::buildUniqueIdentifier(...json_decode($resource_details));
    // Determine the number of distributions making use of the current
    // resource.
    $distributions = $this->referenceLookup->getReferencers('distribution', $identifier, 'downloadURL');
    // If more than one distribution is using this resource, remove it from
    // the purge list.
    return count($distributions) <= 1;
  }

  /**
   * Get older revision ids to consider.
   *
   * @param int $initialVid
   *   The initial dataset revision from which to search resources to purge.
   * @param \Drupal\node\NodeInterface $dataset
   *   The dataset.
   *
   * @return array
   *   List of revisions to consider.
   */
  private function getOlderRevisionIds(int $initialVid, NodeInterface $dataset) : array {
    $vids = array_reverse($this->storage->getEntityStorage()->revisionIds($dataset));

    return array_filter($vids, function ($vid) use ($initialVid) {
      return $vid < $initialVid;
    });
  }

  /**
   * Resources to keep.
   *
   * @param string $uuid
   *   Dataset identifier.
   *
   * @return array
   *   The revisions important to keep.
   */
  private function getResourcesToKeep(string $uuid) : array {
    // Always keep resources associated with the latest revision.
    $latestRevision = $this->storage->getEntityLatestRevision($uuid);
    $resourcesToKeep = $this->getResources($latestRevision);

    // Keep resources of the currently published revision, if any.
    $currentlyPublished = $this->storage->getEntityPublishedRevision($uuid);
    if ($currentlyPublished) {
      $resourcesToKeep = array_merge($resourcesToKeep, $this->getResources($currentlyPublished));
    }

    return array_unique($resourcesToKeep);
  }

  /**
   * Get dataset published status and resource from a dataset revision.
   *
   * @param string $vid
   *   A dataset revision.
   *
   * @return array
   *   The revision's moderation state, and resource information json string.
   */
  private function getRevisionData(string $vid) : array {
    $revision = $this->storage->getEntityStorage()->loadRevision($vid);
    $published = FALSE;
    if ($revision instanceof EntityPublishedInterface) {
      $published = $revision->isPublished();
    }
    return [
      $published,
      $this->getResources($revision),
    ];
  }

  /**
   * Get the identifier and version of all resources for a dataset.
   *
   * @param \Drupal\node\NodeInterface $dataset
   *   The dataset.
   *
   * @return array
   *   Array of resource identifiers and versions. Each array element is a
   *   JSON-encoded array containing a resource's identifier and version.
   */
  private function getResources(NodeInterface $dataset) : array {
    $resources = [];
    $metadata = json_decode($dataset->get('field_json_metadata')->getString());
    $distributions = $metadata->{'%Ref:distribution'} ?? [];

    foreach ($distributions as $distribution) {
      // Retrieve and validate the resource for this distribution before adding
      // it to the resources list.
      $resource = $distribution->data->{'%Ref:downloadURL'}[0] ?? NULL;
      if (isset($resource->data->identifier, $resource->data->version)) {
        $resources[] = json_encode([
          $resource->data->identifier,
          $resource->data->version,
          $resource->data->perspective,
        ]);
      }
    }

    return $resources;
  }

  /**
   * Delete a resource's file and/or table, based on enabled config settings.
   *
   * @param string $id
   *   Resource identifier.
   * @param string $version
   *   Resource version.
   */
  private function delete(string $id, string $version) {
    if ($this->getPurgeTableSetting()) {
      $this->removeDatastoreStorage($id, $version);
    }
    if ($this->getPurgeFileSetting()) {
      $this->removeResourceLocalizer($id, $version);
    }
  }

  /**
   * Helper to remove resource localizer.
   *
   * @param string $id
   *   Resource identifier.
   * @param string $version
   *   Resource version.
   */
  private function removeResourceLocalizer(string $id, string $version) {
    try {
      $this->datastore->getResourceLocalizer()->remove($id, $version);
    }
    catch (\Exception $e) {
      $this->error("Error removing resource localizer id {$id}, version {$version}: " . $e->getMessage());
    }
  }

  /**
   * Helper to remove Datastore storage table.
   *
   * @param string $id
   *   Resource identifier.
   * @param string $version
   *   Resource version.
   */
  private function removeDatastoreStorage(string $id, string $version) {
    try {
      $this->datastore->getStorage($id, $version)->destruct();
    }
    catch (\Exception $e) {
      $this->error("Error deleting datastore id {$id}, version {$version}: " . $e->getMessage());
    }
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
  private function validate() : bool {
    return $this->getPurgeFileSetting() || $this->getPurgeTableSetting();
  }

  /**
   * Pair the dataset identifiers with their revision id.
   *
   * @param array $uuids
   *   Array of dataset identifiers.
   *
   * @return array
   *   Array of dataset identifiers indexed by their revision id.
   */
  private function getIndexedUuids(array $uuids) : array {
    $indexed = [];

    foreach ($uuids as $uuid) {
      $nid = $this->storage->getEntityIdFromUuid($uuid);
      if ($nid) {
        $vid = $this->storage->getEntityStorage()->getLatestRevisionId($nid);
        $indexed[$vid] = $uuid;
      }
    }

    return $indexed;
  }

}
