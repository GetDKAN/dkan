<?php

namespace Drupal\metastore\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * Initiate database clean up when a distribution is orphaned.
 *
 * @package Drupal\metastore\Events
 */
class OrphaningDistribution extends Event {

  /**
   * Event fired when a distribution is about to be orphaned.
   *
   * @Event
   */
  const EVENT_ORPHANING_DISTRIBUTION = 'metastore_orphaning_distribution';
  private $uuid;

  /**
   * Constructor.
   */
  public function __construct(String $uuid) {
    $this->uuid = $uuid;
  }

  /**
   * Getter.
   */
  public function getUuid() {
    return $this->uuid;
  }

}
