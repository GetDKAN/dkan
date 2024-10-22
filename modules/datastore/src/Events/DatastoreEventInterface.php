<?php

namespace Drupal\datastore\Events;

/**
 * Event base class for the datastore module.
 */
interface DatastoreEventInterface {

  /**
   * Get the datastore identifier.
   *
   * @return string
   *   The datastore identifier.
   */
  public function getIdentifier(): string;

  /**
   * Get the datastore version.
   *
   * @return string|null
   *   The datastore version, or NULL if none was provided.
   */
  public function getVersion(): ?string;

}
