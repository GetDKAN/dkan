<?php

namespace Drupal\dkan_metastore\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\dkan_common\DataResource;
use Drupal\dkan_metastore\LifeCycle\LifeCycle;
use Drupal\dkan_metastore\LifeCycle\LifeCycleEvent;
use Drupal\dkan_metastore\MetastoreService;
use Drupal\dkan_metastore\Plugin\QueueWorker\OrphanReferenceProcessor;
use Drupal\dkan_metastore\Reference\Dereferencer;
use Drupal\dkan_metastore\Reference\HelperTrait;
use Drupal\dkan_metastore\ReferenceLookupInterface;
use Drupal\dkan_metastore\ResourceMapper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for Metastore.
 */
class MetastoreSubscriber implements EventSubscriberInterface {

  use HelperTrait;

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
      $container->get('dkan.metastore.reference_lookup'),
      $container->get('config.factory')
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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config.factory service.
   */
  public function __construct(
    MetastoreService $service,
    ResourceMapper $resourceMapper,
    ReferenceLookupInterface $referenceLookup,
    ConfigFactoryInterface $configFactory,
  ) {
    $this->service = $service;
    $this->resourceMapper = $resourceMapper;
    $this->referenceLookup = $referenceLookup;
    $this->setConfigService($configFactory);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = [];
    $events[OrphanReferenceProcessor::EVENT_ORPHANING_DISTRIBUTION][] = ['clearItemResources'];
    $events[LifeCycle::EVENT_DELETING_DISTRIBUTION][] = ['clearItemResources'];
    $events[LifeCycle::EVENT_DELETING_DATASET][] = ['clearItemResources'];
    return $events;
  }

  /**
   * Clear resources associated with a metastore item.
   *
   * This will almost always be for a distribution or dataset, depending on
   * whether distributions are set to be referenced.
   *
   * @param \Drupal\dkan_metastore\LifeCycle\LifeCycleEvent $event
   *   The event object containing the distribution identifier.
   */
  public function clearItemResources(LifeCycleEvent $event) {
    $schema_id = $event->getSchemaId();
    $identifier = $event->getIdentifier();
    // In referenced mode, a deleted dataset's distributions get orphaned
    // (and their resources cleaned up) separately.
    if ($schema_id === 'dataset' && $this->distributionsAreReferenced()) {
      return;
    }
    $resources = [];
    $item = $this->service->get($schema_id, $identifier, FALSE);
    // Attempt to extract all resources for the given metastore item.
    $resource_refs = $item->{'$..["' . Dereferencer::REF_PREFIX . 'downloadURL"]..data'} ?? [];
    foreach ($resource_refs as $resourceParams) {
      $resource_id = $resourceParams['identifier'] ?? NULL;
      $perspective = $resourceParams['perspective'] ?? NULL;
      $version = $resourceParams['version'] ?? NULL;
      $resources[] = $this->resourceMapper->get($resource_id, $perspective, $version);
    }
    $this->cleanResourceMapperTable($resources, $schema_id, $identifier);
  }

  /**
   * Removes resources associated with the orphaned metastore item.
   *
   * @param \Drupal\dkan_common\DataResource[] $resources
   *   The resources associated with the orphaned metastore item.
   * @param string $schema_id
   *   The schema ID of the metastore item.
   * @param string $identifier
   *   The identifier of the metastore item.
   */
  public function cleanResourceMapperTable(array $resources, string $schema_id, string $identifier): void {
    // Remove all resource entries associated with this item from the
    // metadata resource mapper.
    foreach ($resources as $resource) {
      if (!$resource instanceof DataResource) {
        throw new \InvalidArgumentException("Expected DataResource, got " . gettype($resource));
      }
      // Ensure a valid ID, perspective, and version were found for the given
      // item.
      $resource_id_wo_perspective = $resource->getIdentifier() . '__' . $resource->getVersion();
      if ($resource instanceof DataResource && !$this->resourceInUseElsewhere($schema_id, $identifier, $resource_id_wo_perspective)) {
        // Remove resource entry for metadata resource mapper.
        $this->resourceMapper->remove($resource);
      }
    }
  }

  /**
   * Determine if a resource is in use in another metastore item.
   *
   * @param string $schema_id
   *   The schema ID of the metastore item.
   * @param string $item_id
   *   The identifier of the metastore item.
   * @param string $resource_id
   *   The identifier of the resource.
   *
   * @return bool
   *   Whether the resource is in use elsewhere.
   */
  private function resourceInUseElsewhere(string $schema_id, string $item_id, string $resource_id): bool {
    $schemas_to_check = array_unique(['dataset', $schema_id]);
    $referencers = [];
    foreach ($schemas_to_check as $schema) {
      $referencers = array_merge($referencers, $this->referenceLookup->getReferencers($schema, $resource_id, 'downloadURL'));
    }

    // Check if any other metastore items reference it.
    foreach ($referencers as $referencer) {
      if ($referencer != $item_id) {
        return TRUE;
      }
    }
    // No other metastore items were found using this resource.
    return FALSE;
  }

}
