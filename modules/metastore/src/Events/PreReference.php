<?php

namespace Drupal\metastore\Events;

use Drupal\metastore\MetastoreDataNode;
use Symfony\Component\EventDispatcher\Event;

/**
 * Pre-reference.
 */
class PreReference extends Event {
  private $data;

  /**
   * Constructor.
   */
  public function __construct(MetastoreDataNode $data) {
    $this->data = $data;
  }

  /**
   * Getter.
   */
  public function getData(): MetastoreDataNode {
    return $this->data;
  }

}
