<?php

namespace Drupal\metastore\EventSubscriber;

use Drupal\metastore\Events\ResourcePreRemove;
use Drupal\datastore\Service\ResourceLocalizer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Drupal\datastore\Service;

/**
 * Class RemoveLocalFile.
 */
class RemoveLocalFile implements EventSubscriberInterface {

  /**
   * The datastore service.
   *
   * @var \Drupal\datastore\Service
   */
  protected $datastoreService;

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    print 'subscribing to ResourcePreRemove';
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
    // $service = \Drupal::service('dkan.metastore.service');
    // $resource = json_decode($service->get('distribution', $uuid));
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
