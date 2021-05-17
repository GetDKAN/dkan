<?php

namespace Drupal\metastore\Events;

use Drupal\metastore\MetastoreItemInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Pre-reference.
 */
class PreReference extends Event {
  /**
   * Metastore data object.
   *
   * @var Drupal\metastore\MetastoreItemInterface
   */
  private $data;

  /**
   * Constructor.
   */
  public function __construct(MetastoreItemInterface $data) {
    $this->data = $data;
  }

  /**
   * Getter.
   *
   * @return Drupal\metastore\MetastoreItemInterface
   *   A metastore item object.
   */
  public function getData() {
    return $this->data;
  }

}
