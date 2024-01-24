<?php

namespace Drupal\metastore\EventSubscriber;

use Drupal\common\DataResource;
use Drupal\common\Events\Event;
use Drupal\Core\Logger\LoggerChannelFactory;
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
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected LoggerChannelFactory $loggerFactory;

  /**
   * Metastore service.
   *
   * @var \Drupal\metastore\MetastoreService
   */
  protected MetastoreService $service;

  /**
   * Resource mapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
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
      $container->get('logger.factory'),
      $container->get('dkan.metastore.service'),
      $container->get('dkan.metastore.resource_mapper'),
      $container->get('dkan.metastore.reference_lookup')
    );
  }

  /**
   * Constructor.
   *
   * @param Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory service.
   * @param \Drupal\metastore\MetastoreService $service
   *   The dkan.metastore.service service.
   * @param \Drupal\metastore\ResourceMapper $resourceMapper
   *   The dkan.metastore.resource_mapper.
   * @param \Drupal\metastore\ReferenceLookupInterface $referenceLookup
   *   The dkan.metastore.reference_lookup service.
   */
  public function __construct(LoggerChannelFactory $logger_factory, MetastoreService $service, ResourceMapper $resourceMapper, ReferenceLookupInterface $referenceLookup) {
    $this->loggerFactory = $logger_factory;
    $this->service = $service;
    $this->resourceMapper = $resourceMapper;
    $this->referenceLookup = $referenceLookup;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
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
      $resource_identifier = $resourceParams['identifier'] ?? NULL;
      $perspective = $resourceParams['perspective'] ?? NULL;
      $version = $resourceParams['version'] ?? NULL;
      $resource_id = $resource_identifier . '__' . $version . '__' . $perspective;
      $resource = $this->resourceMapper->get($resource_identifier, $perspective, $version);

      // Ensure a valid ID, perspective, and version were found for the given
      // distribution.
      if ($resource instanceof DataResource && !$this->resourceInUseElsewhere($resource_id, $distribution_id)) {
        // Remove resource entry for metadata resource mapper.
        $this->resourceMapper->remove($resource);
      }
    }
  }

  /**
   * Determine if a resource is in use in another distribution.
   *
   * @param string $resource_id
   *   The identifier of the resource.
   * @param string $distribution_id
   *    The identifier of the orphaned distribution.
   *
   * @return bool
   *   Whether the resource is in use elsewhere.
   *
   * @todo Abstract out "distribution" and field_data_type.
   */
  private function resourceInUseElsewhere(string $resource_id, string $distribution_id): bool {
    $distributions = $this->referenceLookup->getReferencers('distribution', $resource_id, 'downloadURL');

    // Check if any other distributions reference it.
    foreach ($distributions as $distribution) {
      if ($distribution != $distribution_id) {
        return true;
      }
    }
    return false;
  }

}
