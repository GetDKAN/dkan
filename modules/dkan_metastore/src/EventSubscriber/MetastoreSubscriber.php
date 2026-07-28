<?php

namespace Drupal\dkan_metastore\EventSubscriber;

use Drupal\dkan_common\DataResource;
use Drupal\dkan_metastore\LifeCycle\LifeCycle;
use Drupal\dkan_metastore\LifeCycle\LifeCycleEvent;
use Drupal\dkan_metastore\MetastoreService;
use Drupal\dkan_metastore\Plugin\QueueWorker\OrphanReferenceProcessor;
use Drupal\dkan_metastore\Reference\Dereferencer;
use Drupal\dkan_metastore\ReferenceLookupInterface;
use Drupal\dkan_metastore\ResourceMapper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for Metastore.
 */
class MetastoreSubscriber implements EventSubscriberInterface {

  /**
   * Metastore service.
   */
  protected MetastoreService $service;

  /**
   * Resource mapper service.
   */
  protected ResourceMapper $resourceMapper;

  /**
   * The dkan.metastore.reference_lookup service.
   *
   * @var \Drupal\dkan_metastore\ReferenceLookupInterface
   */
  private $referenceLookup;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.metastore.service'),
      $container->get('dkan.metastore.resource_mapper'),
      $container->get('dkan.metastore.reference_lookup')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\dkan_metastore\MetastoreService $service
   *   The dkan.metastore.service service.
   * @param \Drupal\dkan_metastore\ResourceMapper $resourceMapper
   *   The dkan.metastore.resource_mapper.
   * @param \Drupal\dkan_metastore\ReferenceLookupInterface $referenceLookup
   *   The dkan.metastore.reference_lookup service.
   */
  public function __construct(
    MetastoreService $service,
    ResourceMapper $resourceMapper,
    ReferenceLookupInterface $referenceLookup,
  ) {
    $this->service = $service;
    $this->resourceMapper = $resourceMapper;
    $this->referenceLookup = $referenceLookup;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = [];
    $events[OrphanReferenceProcessor::EVENT_ORPHANING_DISTRIBUTION][] = ['clearDistributionResources'];
    $events[LifeCycle::EVENT_DELETING_DISTRIBUTION][] = ['clearDistributionResources'];
    // $events[LifeCycle::EVENT_DELETING_DATASET][] = ['clearDistributionResources'];
    return $events;
  }

  /**
   * Clear resources associated with a distribution.
   *
   * @param \Drupal\dkan_metastore\LifeCycle\LifeCycleEvent $event
   *   The event object containing the distribution identifier.
   */
  public function clearDistributionResources(LifeCycleEvent $event) {
    $resources = [];
    $schema_id = $event->getSchemaId();
    $identifier = $event->getIdentifier();
    $item = $this->service->get($schema_id, $identifier, FALSE);
    // Attempt to extract all resources for the given distribution.
    $resource_refs = $item->{'$.data["' . Dereferencer::REF_PREFIX . 'downloadURL"]..data'} ?? [];
    foreach ($resource_refs as $resourceParams) {
      $resource_id = $resourceParams['identifier'] ?? NULL;
      $perspective = $resourceParams['perspective'] ?? NULL;
      $version = $resourceParams['version'] ?? NULL;
      $resources[] = $this->resourceMapper->get($resource_id, $perspective, $version);
    }
    $this->cleanResourceMapperTable($resources, $schema_id, $identifier);
  }

  /**
   * React to a distribution being orphaned or deleted.
   *
   * Removes resources associated with the orphaned distribution.
   *
   * @param \Drupal\dkan_common\DataResource[] $resources
   *   The resources associated with the orphaned distribution.
   * @param string $schema_id
   *   The schema ID of the distribution.
   * @param string $item_id
   *   The identifier of the distribution item.
   */
  public function cleanResourceMapperTable(array $resources, string $schema_id, string $item_id): void {
    // Remove all resource entries associated with this distribution from the
    // metadata resource mapper.
    foreach ($resources as $resource) {
      if (!$resource instanceof DataResource) {
        throw new \InvalidArgumentException("Expected DataResource, got " . gettype($resource));
      }
      // Ensure a valid ID, perspective, and version were found for the given
      // distribution.
      $resource_id_wo_perspective = $resource->getIdentifier() . '__' . $resource->getVersion();
      if ($resource instanceof DataResource && !$this->resourceInUseElsewhere($schema_id, $item_id, $resource_id_wo_perspective)) {
        // Remove resource entry for metadata resource mapper.
        $this->resourceMapper->remove($resource);
      }
    }
  }

  /**
   * Determine if a resource is in use in another distribution.
   *
   * @param string $schema_id
   *   The schema ID of the distribution.
   * @param string $item_id
   *   The identifier of the distribution item.
   * @param string $resource_id
   *   The identifier of the resource.
   *
   * @return bool
   *   Whether the resource is in use elsewhere.
   *
   * @todo Abstract out "distribution" and field_data_type.
   */
  private function resourceInUseElsewhere(string $schema_id, string $item_id, string $resource_id): bool {
    $referencers = $this->referenceLookup->getReferencers($schema_id, $resource_id, 'downloadURL');

    // Check if any other distributions reference it.
    foreach ($referencers as $referencer) {
      if ($referencer != $item_id) {
        return TRUE;
      }
    }
    // No other distributions were found using this resource.
    return FALSE;
  }

}
