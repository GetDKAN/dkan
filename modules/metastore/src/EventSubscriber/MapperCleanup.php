<?php

namespace Drupal\metastore\EventSubscriber;

use Drupal\metastore\Events\OrphaningDistribution;
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
    $events[OrphaningDistribution::EVENT_ORPHANING_DISTRIBUTION][] = ['cleanResourceMapperTable'];
    return $events;
  }

  /**
   * React to a distribution being orphaned.
   *
   * @param \Drupal\metastore\Events\OrphaningDistribution $event
   *   The event object containing the resource uuid.
   */
  public function cleanResourceMapperTable(OrphaningDistribution $event) {
    $uuid = $event->getUuid();
    // Use the metastore service to build a resource object.
    $service = \Drupal::service('dkan.metastore.service');
    $resource = $service->get('distribution', $uuid);
    $resource = json_decode($resource);
    $id = $resource->data->{'%Ref:downloadURL'}[0]->data->identifier;
    $version = $resource->data->{'%Ref:downloadURL'}[0]->data->version;
    // Use the metastore resourceMapper to remove the source entry.
    $resourceMapper = \Drupal::service('dkan.metastore.resource_mapper');
    try {
      $resource = $resourceMapper->get($id, 'source', $version);
      $resourceMapper->remove($resource);
      \Drupal::logger('datastore')->notice('Removing resource source mapping for @uuid', ['@uuid' => $uuid]);
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
