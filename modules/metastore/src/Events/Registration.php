<?php

namespace Drupal\metastore\Events;

use Drupal\common\Resource;
use Symfony\Component\EventDispatcher\Event;

/**
 * Registration.
 */
class Registration extends Event {
  private $resource;

  /**
   * Constructor.
   */
  public function __construct(Resource $resource) {
    $this->resource = $resource;
  }

  /**
   * Getter.
   */
  public function getResource(): Resource {
    return $this->resource;
  }

}
