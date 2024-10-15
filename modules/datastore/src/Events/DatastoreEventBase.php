<?php

namespace Drupal\datastore\Events;

use Drupal\Component\EventDispatcher\Event;

/**
 * Event base class for the datastore module.
 */
class DatastoreEventBase extends Event {

  /**
   * The datastore identifier.
   *
   * @var string
   */
  private string $identifier;

  /**
   * The datastore version.
   *
   * @var string|null
   */
  private ?string $version;

  /**
   * Constructor.
   *
   * @param string $identifier
   *   The datastore identifier.
   * @param string|null $version
   *   (Optional) The datastore version.
   */
  public function __construct(string $identifier, ?string $version) {
    $this->identifier = $identifier;
    $this->version = $version;
  }

  /**
   * Get the datastore identifier.
   *
   * @return string
   *   The datastore identifier.
   */
  public function getIdentifier(): string {
    return $this->identifier;
  }

  /**
   * Get the datastore version.
   *
   * @return string|null
   *   The datastore version, or NULL if none was provided.
   */
  public function getVersion(): ?string {
    return $this->version;
  }

}
