<?php

namespace Drupal\datastore\Events;

use Drupal\dkan\DataResource;

/**
 * Event base class for the datastore module.
 */
interface DatastoreEventInterface {

  /**
   * Get the DataResource object for the event.
   *
   * @return \Drupal\dkan\DataResource
   *   DataResource object related to the datastore in question.
   */
  public function getDataResource(): DataResource;

}
