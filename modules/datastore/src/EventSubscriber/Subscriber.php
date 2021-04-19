<?php

namespace Drupal\datastore\EventSubscriber;

use Drupal\common\Resource;
use Drupal\common\LoggerTrait;
use Drupal\common\Events\Event;
use Drupal\metastore\Events\DatasetUpdate;
use Drupal\metastore\Events\Registration;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Storage\Data;
use Drupal\datastore\Service\ResourcLocalizer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber.
 */
class Subscriber implements EventSubscriberInterface {
  use LoggerTrait;

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ResourceMapper::EVENT_REGISTRATION][] = ['onRegistration'];
    $events[ResourceMapper::EVENT_DATASTORE_CLEANUP][] = ['drop'];
    $events[Data::EVENT_DATASET_UPDATE][] = ['purgeResources'];
    return $events;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function onRegistration(Event $event) {

    /** @var \Drupal\common\Events\Event $event */
    $resource = $event->getData();

    if ($resource->getPerspective() == 'source' && $this->isDataStorable($resource)) {
      try {
        /** @var \Drupal\datastore\Service $datastoreService */
        $datastoreService = \Drupal::service('dkan.datastore.service');
        $datastoreService->import($resource->getIdentifier(), TRUE, $resource->getVersion());
      }
      catch (\Exception $e) {
        $this->setLoggerFactory(\Drupal::service('logger.factory'));
        $this->log('datastore', $e->getMessage());
      }
    }

  }

  /**
   * Private.
   */
  private function isDataStorable(Resource $resource) : bool {
    return in_array($resource->getMimeType(), [
      'text/csv',
      'text/tab-separated-values',
    ]);
  }

  /**
   * Purge resources.
   *
   * @param \Drupal\common\Events\Event $event
   *   Dataset publication.
   */
  public function purgeResources(Event $event) {
    $node = $event->getData();

    /** @var \Drupal\datastore\Service\ResourcePurger $resourcePurger */
    $resourcePurger = \Drupal::service('dkan.datastore.service.resource_purger');
    $resourcePurger->schedule([$node->uuid()]);
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
      \Drupal::database()->delete('jobstore_dkan_datastore_importer')->condition('ref_uuid', $id)->execute();
      //\Drupal::service('dkan.common.job_store')->getInstance(Import::class)->remove($id);
    }
    catch (\Exception $e) {
      \Drupal::logger('datastore')->error('Failed to remove importer job. @message', ['@message' => $e->getMessage()]);
    }
  }

}
