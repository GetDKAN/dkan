<?php

namespace Drupal\metastore\EventSubscriber;

use Drupal\common\Events\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class MapperCleanup.
 */
class MapperCleanup implements EventSubscriberInterface {

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[Event::EVENT_ORPHANING_DISTRIBUTION][] = ['cleanResourceMapperTable'];
    return $events;
  }

  /**
   * React to a distribution being orphaned.
   *
   * @param \Drupal\common\Events\Event $event
   *   The event object containing the resource uuid.
   */
  public function cleanResourceMapperTable(Event $event) {
    $uuid = $event->getData();
    // Use the metastore service to build a resource object.
    $service = \Drupal::service('dkan.metastore.service');
    $resource = $service->get('distribution', $uuid);
    $resource = json_decode($resource);
    $id = $resource->data->{'%Ref:downloadURL'}[0]->data->identifier;
    $perspective = $resource->data->{'%Ref:downloadURL'}[0]->data->perspective;
    $version = $resource->data->{'%Ref:downloadURL'}[0]->data->version;
    // Use the metastore resourceMapper to remove the source entry.
    $resourceMapper = \Drupal::service('dkan.metastore.resource_mapper');
    try {
      $resource = $resourceMapper->get($id, $perspective, $version);
      $resourceMapper->remove($resource);
    }
    catch (\Exception $e) {
      \Drupal::logger('datastore')->error('Failed to remove resource source mapping for @uuid. @message',
        [
          '@uuid' => $uuid,
          '@message' => $e->getMessage(),
        ]);
    }
  }

}
