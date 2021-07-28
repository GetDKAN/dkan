<?php

namespace Drupal\metastore\EventSubscriber;

use Drupal\common\Events\Event;
use Drupal\Core\Logger\LoggerChannelFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\metastore\Plugin\QueueWorker\OrphanReferenceProcessor;
use Drupal\metastore\Service;
use Drupal\metastore\ResourceMapper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MetastoreSubscriber.
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
   * @param \Drupal\metastore\Service $service
   *   The dkan.metastore.service service.
   * @param \Drupal\metastore\ResourceMapper $resourceMapper
   *   The dkan.metastore.resource_mapper.
   */
  public function __construct(LoggerChannelFactory $logger_factory, Service $service, ResourceMapper $resourceMapper) {
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
    $distribution = $this->service->get('distribution', $distribution_id);
    // Attempt to extract all resources for the given distribution.
    $resources = $distribution->{'$.data["%Ref:downloadURL"]..data'} ?? [];

    // Remove all resource entries associated with this distribution from the
    // metadata resource mapper.
    foreach ($resources as $resource) {
      // Retrieve the distributions ID, perspective, and version metadata.
      $resource_id = $resource['identifier'] ?? NULL;
      $perspective = $resource['perspective'] ?? NULL;
      $version = $resource['version'] ?? NULL;
      // Ensure a valid ID, perspective, and version were found for the given
      // distribution.
      if (isset($resource_id) && $resource = $this->resourceMapper->get($resource_id, $perspective, $version)) {
        // Remove resource entry for metadata resource mapper.
        $this->resourceMapper->remove($resource);
      }
      else {
        $this->loggerFactory->get('metastore')->error('Failed to remove resource with id "@distribution_id" from source mapping for distribution with id "@resource_id".', [
          '@distribution_id' => $distribution_id,
          '@resource_id' => $resource_id,
        ]);
      }
    }
  }

}
