<?php

namespace Drupal\metastore\EventSubscriber;

use Drupal\common\DataResource;
use Drupal\common\Events\Event;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\Plugin\QueueWorker\OrphanReferenceProcessor;
use Drupal\metastore\ReferenceLookupInterface;
use Drupal\metastore\ResourceMapper;
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
   * @var \Drupal\metastore\ReferenceLookupInterface
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
   * @param \Drupal\metastore\MetastoreService $service
   *   The dkan.metastore.service service.
   * @param \Drupal\metastore\ResourceMapper $resourceMapper
   *   The dkan.metastore.resource_mapper.
   * @param \Drupal\metastore\ReferenceLookupInterface $referenceLookup
   *   The dkan.metastore.reference_lookup service.
   */
  public function __construct(
    MetastoreService $service,
    ResourceMapper $resourceMapper,
    ReferenceLookupInterface $referenceLookup
  ) {
    $this->service = $service;
    $this->resourceMapper = $resourceMapper;
    $this->referenceLookup = $referenceLookup;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents(): array {
    $events = [];
    $events[OrphanReferenceProcessor::EVENT_ORPHANING_DISTRIBUTION][] = ['cleanResourceMapperTable'];
    return $events;
  }

  /**
   * React to a distribution being orphaned.
   *
   * Removes resources associated with the orphaned distribution.
   *
   * @param \Drupal\common\Events\Event $event
   *   The event object containing the resource uuid.
   */
  public function cleanResourceMapperTable(Event $event) {
    $distribution_id = $event->getData();
    // Use the metastore service to build a distribution object.
    $distribution = $this->service->get('distribution', $distribution_id, FALSE);
    // Attempt to extract all resources for the given distribution.
    $resources = $distribution->{'$.data["%Ref:downloadURL"]..data'} ?? [];

    // Remove all resource entries associated with this distribution from the
    // metadata resource mapper.
    foreach ($resources as $resourceParams) {
      // Retrieve the distributions ID, perspective, and version metadata.
      $resource_id = $resourceParams['identifier'] ?? NULL;
      $perspective = $resourceParams['perspective'] ?? NULL;
      $version = $resourceParams['version'] ?? NULL;
      $resource_id_wo_perspective = $resource_id . '__' . $version;
      $resource = $this->resourceMapper->get($resource_id, $perspective, $version);

      // Ensure a valid ID, perspective, and version were found for the given
      // distribution.
      if ($resource instanceof DataResource && !$this->resourceInUseElsewhere($distribution_id, $resource_id_wo_perspective)) {
        // Remove resource entry for metadata resource mapper.
        $this->resourceMapper->remove($resource);
      }
    }
  }

  /**
   * Determine if a resource is in use in another distribution.
   *
   * @param string $dist_id
   *   The uuid of the distribution where this resource is know to be in use.
   * @param string $resource_id
   *   The identifier of the resource.
   *
   * @return bool
   *   Whether the resource is in use elsewhere.
   *
   * @todo Abstract out "distribution" and field_data_type.
   */
  private function resourceInUseElsewhere(string $dist_id, string $resource_id): bool {
    $distributions = $this->referenceLookup->getReferencers('distribution', $resource_id, 'downloadURL');

    // Check if any other distributions reference it.
    foreach ($distributions as $distribution) {
      if ($distribution != $dist_id) {
        return TRUE;
      }
    }
    // No other distributions were found using this resource.
    return FALSE;
  }

}
