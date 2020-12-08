<?php

namespace Drupal\metastore\Events;

use Drupal\node\NodeInterface;
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
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * Constructor.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Dataset node just published.
   */
  public function __construct(NodeInterface $node) {
    $this->node = $node;
  }

  /**
   * Getter.
   *
   * @return \Drupal\node\NodeInterface
   *   Dataset node just published.
   */
  public function getNode() {
    return $this->node;
  }

}
