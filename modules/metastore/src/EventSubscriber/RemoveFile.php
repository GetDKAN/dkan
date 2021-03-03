<?php

namespace Drupal\metastore\EventSubscriber;

use Drupal\metastore\Events\ResourceCleanup;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\common\Resource;

/**
 * Class RemoveFile.
 */
class RemoveFile implements EventSubscriberInterface {

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ResourceCleanup::EVENT_RESOURCE_CLEANUP][] = ['fileCleanup'];
    return $events;
  }

  /**
   * React to a distribution being orphaned.
   *
   * @param \Drupal\metastore\Events\ResourceCleanup $event
   *   The event object containing the resource object.
   */
  public function fileCleanup(ResourceCleanup $event) {

    /** @var \Drupal\common\Resource $resouce */
    $resource = $event->getResource();
    if ($resource->getPerspective() == 'source') {
      $resourceLocalizer = \Drupal::service('dkan.datastore.service.resource_localizer');
      try {
        $resourceLocalizer->remove($resource->getIdentifier(), $resource->getVersion());
      }
      catch (\Exception $e) {
        \Drupal::logger('datastore')->error('Failed to remove the file for @id. @message',
          [
            '@id' => $obect->identifier,
            '@message' => $e->getMessage(),
          ]);
      }
    }
  }

}
