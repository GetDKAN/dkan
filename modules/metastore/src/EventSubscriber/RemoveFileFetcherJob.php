<?php

namespace Drupal\metastore\EventSubscriber;

use Drupal\metastore\Events\ResourceCleanup;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\common\Resource;

/**
 * Class MapperCleanup.
 */
class RemoveFileFetcherJob implements EventSubscriberInterface {

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ResourceCleanup::EVENT_JOBSTORE_CLEANUP][] = ['delete'];
    return $events;
  }

  /**
   * React to a distribution being orphaned.
   *
   * @param \Drupal\metastore\Events\ResourceCleanup $event
   *   The event object containing the resource object.
   */
  public function delete(ResourceCleanup $event) {
    /** @var \Drupal\common\Resource $resouce */
    $resource = $event->getResource();
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
