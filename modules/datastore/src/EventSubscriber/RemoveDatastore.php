<?php

namespace Drupal\datastore\EventSubscriber;

use Drupal\common\LoggerTrait;
use Drupal\common\Events\Event;
use Drupal\metastore\ResourceMapper;
// use Drupal\datastore\Service\Factory\Import;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber.
 */
class RemoveDatastore implements EventSubscriberInterface {
  use LoggerTrait;

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ResourceMapper::EVENT_DATASTORE_DROP][] = ['drop'];
    return $events;
  }

  /**
   * React to a distribution being orphaned.
   *
   * @param \Drupal\common\Events\Event $event
   *   The event object containing the resource object.
   */
  public function drop(Event $event) {
    /** @var \Drupal\common\Events\Event $event */
    $resource = $event->getData();
    $ref_uuid = $resource->getUniqueIdentifier();
    $id = md5(str_replace('source', 'local_file', $ref_uuid));
    try {
      /** @var \Drupal\datastore\Service $datastoreService */
      $datastoreService = \Drupal::service('dkan.datastore.service');
      $datastoreService->drop($resource->getIdentifier(), $resource->getVersion());

      \Drupal::logger('datastore')->notice('Dropping datastore for @id', ['@id' => $id]);
    }
    catch (\Exception $e) {
      \Drupal::logger('datastore')->error('Failed to drop datastore for @id. @message',
        [
          '@uuid' => $id,
          '@message' => $e->getMessage(),
        ]);
    }
    try {
      // ToDo: fix this to use the remove method to remove the record.
      // \Drupal::service('dkan.common.job_store')->getInstance(Import::class)->remove($id);
      \Drupal::database()->delete('jobstore_dkan_datastore_importer')->condition('ref_uuid', $id)->execute();
    }
    catch (\Exception $e) {
      \Drupal::logger('datastore')->error('Failed to remove importer job. @message', ['@message' => $e->getMessage()]);
    }
  }

}
