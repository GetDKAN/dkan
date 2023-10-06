<?php

namespace Drupal\metastore\EventSubscriber;

use Drupal\common\Events\Event;
use Drupal\common\DataResource;
use Drupal\Core\Logger\LoggerChannelFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\metastore\Plugin\QueueWorker\OrphanReferenceProcessor;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\ResourceMapper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\metastore\Reference\Referencer;

/**
 * Event subscriber for Metastore.
 */
class MetastoreSubscriber implements EventSubscriberInterface {

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $loggerFactory;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory'),
      $container->get('dkan.metastore.service'),
      $container->get('dkan.metastore.resource_mapper')
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
   */
  public function __construct(LoggerChannelFactory $logger_factory, MetastoreService $service, ResourceMapper $resourceMapper) {
    $this->loggerFactory = $logger_factory;
    $this->service = $service;
    $this->resourceMapper = $resourceMapper;
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
      $resource_id = $resourceParams['identifier'] ?? NULL;
      $perspective = $resourceParams['perspective'] ?? NULL;
      $version = $resourceParams['version'] ?? NULL;
      $resource = $this->resourceMapper->get($resource_id, $perspective, $version);
      // Ensure a valid ID, perspective, and version were found for the given
      // distribution.
      if ($resource instanceof DataResource && !$this->resourceInUseElsewhere($distribution_id, $resource->getFilePath())) {
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
   * @param string $file_path
   *   The file path of the resource being checked.
   *
   * @return bool
   *   Whether the resource is in use elsewhere.
   *
   * @todo Abstract out "distribution" and field_data_type.
   */
  private function resourceInUseElsewhere(string $dist_id, string $file_path): bool {
    // Iterate over the metadata for all dataset distributions.
    foreach ($this->service->getAll('distribution') as $metadata) {
      // Attempt to determine the filepath for this distribution's resource.
      $dist_file_path = Referencer::hostify($metadata->{'$.data.downloadURL'} ?? '');
      // If the current distribution does is not the excluded distribution, and
      // it's resource file path matches the supplied file path...
      if ($metadata->{'$.identifier'} !== $dist_id && !empty($dist_file_path) && $dist_file_path === $file_path) {
        // Another distribution with the same resource was found, meaning the
        // resource is still in use.
        return TRUE;
      }
    }
    // No other distributions were found using this resource.
    return FALSE;
  }

}
