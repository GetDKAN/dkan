<?php

namespace Drupal\metastore\Events;

use Drupal\metastore\NodeWrapper\Data;
use Symfony\Component\EventDispatcher\Event;

/**
 * Pre-reference.
 */
class PreReference extends Event {
  private $data;

  /**
   * Constructor.
   */
  public function __construct(Data $data) {
    $this->data = $data;
  }

  /**
   * Getter.
   */
  public function getData(): Data {
    return $this->data;
  }

}
