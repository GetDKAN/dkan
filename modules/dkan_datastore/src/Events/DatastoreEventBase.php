<?php

namespace Drupal\dkan_datastore\Events;

use Drupal\dkan_common\DataResource;
use Drupal\Component\EventDispatcher\Event;

/**
 * Event base class for the datastore module.
 */
class DatastoreEventBase extends Event implements DatastoreEventInterface {

  /**
   * The DataResource object for the event.
   */
  protected DataResource $dataResource;

  /**
   * Constructor.
   *
   * @param \Drupal\dkan_common\DataResource $data_resource
   *   The DataResource object for the event.
   */
  public function __construct(DataResource $data_resource) {
    $this->dataResource = $data_resource;
  }

  /**
   * {@inheritDoc}
   */
  public function getDataResource(): DataResource {
    return $this->dataResource;
  }

}
