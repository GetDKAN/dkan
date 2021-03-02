<?php

namespace Drupal\metastore\EventSubscriber;

use Drupal\metastore\Events\ResourcePreRemove;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RemoveLocalFile.
 */
class RemoveLocalFile implements EventSubscriberInterface {

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ResourcePreRemove::EVENT_RESOURCE_PRE_REMOVE][] = ['fileCleanup'];
    return $events;
  }

  /**
   * React to a distribution being orphaned.
   *
   * @param \Drupal\metastore\Events\RemoveLocalFile $event
   *   The event object containing the resource object.
   */
  public function fileCleanup(RemoveLocalFile $event) {
    print 'made it >>';
    $object = $event->getObject();
    var_dump($object);
    // Use the resourceLocalizer to remove the file.
    $service = \Drupal::service('dkan.datastore.service.resource_localizer');
    // $resource = json_decode($service->get('id', 'version', perspective));
    // $resourceMapper = \Drupal::service('dkan.metastore.resource_mapper');
    // $id = $resource->data->{'%Ref:downloadURL'}[0]->data->identifier;
    // $version = $resource->data->{'%Ref:downloadURL'}[0]->data->version;
    // try {
    //   $resource = $resourceMapper->get($id, 'source', $version);
    //   $resourceMapper->remove($resource);
    //   $this->log('datastore', 'Removing resource source mapping for @uuid', ['@uuid' => $uuid]);
    // }
    // catch (\Exception $e) {
    //   $this->log('datastore', 'Failed to remove resource source mapping for @uuid. @message',
    //     [
    //       '@uuid' => $uuid,
    //       '@message' => $e->getMessage(),
    //     ]);
    // }
  }

}
