<?php

namespace Drupal\metastore\EventSubscriber;

use Drupal\common\Events\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\common\Resource;
use Drupal\metastore\ResourceMapper;

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
    $events[ResourceMapper::EVENT_RESOURCE_CLEANUP][] = ['fileCleanup'];
    return $events;
  }

  /**
   * React to a distribution being orphaned.
   */
  public function fileCleanup(Event $event) {

    /** @var \Drupal\common\Resource $resource */
    $resource = $event->getData();
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
      // Remove the record from jobstore_filefetcher_filefetcher.
      $ref_uuid = "{$resource->getIdentifier()}_{$resource->getVersion()}";
      try {
        \Drupal::database()->delete('jobstore_filefetcher_filefetcher')->condition('ref_uuid', $ref_uuid, "=")->execute();
        //\Drupal::service('dkan.common.job_store')->getInstance(Import::class)->remove($ref_uuid);
      }
      catch (\Exception $e) {
        \Drupal::logger('datastore')->error('Failed to delete filefetcher job. @message', ['@message' => $e->getMessage()]);
      }
    }
  }

}

