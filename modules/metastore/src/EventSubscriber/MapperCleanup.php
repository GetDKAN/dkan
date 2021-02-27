<?php

namespace Drupal\metastore\EventSubscriber;

use Drupal\metastore\Events\OrphaningDistribution;
use Drupal\metastore\ResourceMapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Drupal\metastore\Service;

/**
 * Class MapperCleanup.
 */
class MapperCleanup implements EventSubscriberInterface {

  /**
   * The metastore service.
   *
   * @var \Drupal\metastore\Service
   */
  protected $metastoreService;

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
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

    // Use the resourceMapper to build a resource object.
    $service = \Drupal::service('dkan.metastore.service');
    $resource = $service->get('distribution', $uuid);
    $resource = json_decode($resource);
    $resourceMapper = \Drupal::service('dkan.metastore.resource_mapper');
    $id = $resource->data->{'%Ref:downloadURL'}[0]->data->identifier;
    $version = $resource->data->{'%Ref:downloadURL'}[0]->data->version;
    try {
      print 'removing source >> ';
      $resource = $resourceMapper->get($id, 'source', $version);
      $resourceMapper->remove($resource);
      $this->log('datastore', 'Removing resource source mapping for @uuid', ['@uuid' => $uuid]);
    }
    catch (\Exception $e) {
      print 'nope';
      $this->log('datastore', 'Failed to remove resource source mapping for @uuid. @message',
        [
          '@uuid' => $uuid,
          '@message' => $e->getMessage(),
        ]);
    }
  }

}
