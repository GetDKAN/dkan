<?php

namespace Drupal\datastore\EventSubscriber;

use Drupal\common\Resource;
use Drupal\common\LoggerTrait;
use Drupal\metastore\Events\DatasetUpdate;
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
    $events[Data::EVENT_DATASET_UPDATE][] = ['purgeResources'];
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

    if ($resource->getPerspective() == 'source' && $this->isDataStorable($resource)) {
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
   * @param \Drupal\metastore\Events\DatasetUpdate $event
   *   Dataset publication.
   */
  public function purgeResources(DatasetUpdate $event) {
    $node = $event->getNode();

    /** @var \Drupal\datastore\Service\ResourcePurger $resourcePurger */
    $resourcePurger = \Drupal::service('dkan.datastore.service.resource_purger');
    $resourcePurger->schedule([$node->uuid()]);
  }

}
