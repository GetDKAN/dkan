<?php

namespace Drupal\metastore\Events;

use Drupal\metastore\MetastoreItemInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Dataset publication.
 *
 * @package Drupal\metastore\Events
 */
class DatasetUpdate extends Event {

  /**
   * Dataset node.
   *
   * @var \Drupal\metastore\MetastoreItemInterface
   */
  protected $item;

  /**
   * Constructor.
   *
   * @param \Drupal\metastore\MetastoreItemInterface
   *   Dataset node just published.
   */
  public function __construct(MetastoreItemInterface $item) {
    $this->item = $item;
  }

  /**
   * Getter.
   *
   * @return \Drupal\metastore\MetastoreItemInterface
   *   Dataset node just published.
   */
  public function getItem() {
    return $this->item;
  }

}
