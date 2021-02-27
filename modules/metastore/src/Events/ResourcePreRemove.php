<?php

namespace Drupal\metastore\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * Initiate database clean up when a distribution is orphaned.
 *
 * @package Drupal\metastore\Events
 */
class ResourcePreRemove extends Event {
  /**
   * Event fired when a local file is about to be deleted.
   *
   * @Event
   */
  const EVENT_RESOURCE_PRE_REMOVE = 'metastore_resource_pre_remove';
  private $object;

  /**
   * Constructor.
   */
  public function __construct(Object $object) {
    $this->object = $object;
  }

  /**
   * Getter.
   */
  public function getObject(): Object {
    return $this->object;
  }

}
