<?php

namespace Drupal\metastore\EventSubscriber;

use Drupal\common\LoggerTrait;
use Drupal\metastore\Events\OrphaningDistribution;
use Drupal\metastore\ResourceMapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Drupal\metastore\Service;

/**
 * Class MapperCleanup.
 */
class MapperCleanup implements EventSubscriberInterface {
  use LoggerTrait;

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
    print 'subscribing to OrphaningDistribution';
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
    $resource = json_decode($service->get('distribution', $uuid));
    $resourceMapper = \Drupal::service('dkan.metastore.resource_mapper');
    $id = $resource->data->{'%Ref:downloadURL'}[0]->data->identifier;
    $version = $resource->data->{'%Ref:downloadURL'}[0]->data->version;
    try {
      $resource = $resourceMapper->get($id, 'source', $version);
      $resourceMapper->remove($resource);
      $this->log('datastore', 'Removing resource source mapping for @uuid', ['@uuid' => $uuid]);
    }
    catch (\Exception $e) {
      $this->log('datastore', 'Failed to remove resource source mapping for @uuid. @message',
        [
          '@uuid' => $uuid,
          '@message' => $e->getMessage(),
        ]);
    }
  }

}
