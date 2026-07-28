<?php

namespace Drupal\dkan_metastore\LifeCycle;

use Drupal\dkan_metastore\MetastoreItemInterface;
use Drupal\Component\EventDispatcher\Event;

/**
 * Event dispatched during Metastore LifeCycle.
 *
 * @see \Drupal\dkan_metastore\LifeCycle\LifeCycle
 * @see \Drupal\dkan_metastore\EventSubscriber\MetastoreSubscriber
 */
class LifeCycleEvent extends Event {

  /**
   * The Metastore item for the event.
   *
   * @var \Drupal\dkan_metastore\MetastoreItemInterface
   */
  protected MetastoreItemInterface $item;

  /**
   * LifeCycleEvent constructor.
   *
   * @param string $schemaId
   *   The schema ID.
   * @param string $identifier
   *   The identifier.
   */
  public function __construct(
    protected string $schemaId,
    protected string $identifier,
  ) {
  }

  /**
   * Get the schema ID.
   *
   * @return string
   *   The schema ID.
   */
  public function getSchemaId(): string {
    return $this->schemaId;
  }

  /**
   * Get the identifier.
   *
   * @return string
   *   The identifier.
   */
  public function getIdentifier(): string {
    return $this->identifier;
  }

}
