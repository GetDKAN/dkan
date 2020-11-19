<?php

namespace Drupal\datastore\EventSubscriber;

use Drupal\common\Resource;
use Drupal\common\LoggerTrait;
use Drupal\metastore\Events\DatasetPublication;
use Drupal\metastore\Events\Registration;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Storage\Data;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber.
 */
class Subscriber implements EventSubscriberInterface {
  use LoggerTrait;

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ResourceMapper::EVENT_REGISTRATION][] = ['onRegistration'];
    $events[Data::EVENT_DATASET_PUBLICATION][] = ['purgeResources'];
    return $events;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function onRegistration(Registration $event) {

    /** @var \Drupal\common\Resource $resouce */
    $resource = $event->getResource();

    if ($this->isDataStorable($resource)) {
      try {
        /** @var \Drupal\datastore\Service $datastoreService */
        $datastoreService = \Drupal::service('dkan.datastore.service');
        $datastoreService->import($resource->getIdentifier(), TRUE, $resource->getVersion());
      }
      catch (\Exception $e) {
        $this->setLoggerFactory(\Drupal::service('logger.factory'));
        $this->log('datastore', $e->getMessage());
      }
    }

  }

  /**
   * Private.
   */
  private function isDataStorable(Resource $resource) : bool {
    return in_array($resource->getMimeType(), [
      'text/csv',
      'text/tab-separated-values',
    ]);
  }

  /**
   * Purge resources.
   *
   * @param \Drupal\metastore\Events\DatasetPublication $event
   *   Dataset publication.
   */
  public function purgeResources(DatasetPublication $event) {
    $node = $event->getNode();

    /** @var \Drupal\datastore\Service\ResourcePurger $resourcePurger */
    $resourcePurger = \Drupal::service('dkan.datastore.service.resource_purger');
    // Queue limited purging.
    $resourcePurger->schedulePurging([$node->uuid()]);
  }

}
