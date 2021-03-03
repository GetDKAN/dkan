<?php

namespace Drupal\metastore\Events;

use Symfony\Component\EventDispatcher\Event;
use Drupal\common\Resource;

/**
 * Initiate database clean up when a distribution is orphaned.
 *
 * @package Drupal\metastore\Events
 */
class ResourceCleanup extends Event {
  /**
   * Dispatched when resourceMapper->remove is called.
   *
   * @Event
   */
  const EVENT_RESOURCE_CLEANUP = 'metastore_resource_cleanup';
  const EVENT_JOBSTORE_CLEANUP = 'jobstore_filefetcher_cleanup';

  private $resource;

  /**
   * Constructor.
   */
  public function __construct(Resource $object) {
    $this->resource = $object;
  }

  /**
   * Getter.
   */
  public function getResource() {
    return $this->resource;
  }

}
