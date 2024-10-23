<?php

namespace Drupal\datastore\Events;

use Drupal\common\DataResource;

/**
 * Event base class for the datastore module.
 */
interface DatastoreEventInterface {

  /**
   * Get the DataResource object for the event.
   *
   * @return \Drupal\common\DataResource
   *   DataResource object related to the datastore in question.
   */
  public function getDataResource(): DataResource;

}
