<?php

namespace Drupal\dkan_datastore\Events;

use Drupal\dkan_common\DataResource;
use Drupal\dkan_common\Storage\AbstractDatabaseTable;
use Drupal\dkan_datastore\Storage\DatabaseTable;

/**
 * Event for when we're about to create a datastore table.
 *
 * @see DatabaseTable::EVENT_DATABASE_TABLE_CREATE
 * @see AbstractDatabaseTable::EVENT_TABLE_CREATE
 */
class DatastoreTableCreateEvent extends DatastoreEventBase {

  public function __construct(
    protected array $schema,
    DataResource $dataResource,
  ) {
    parent::__construct($dataResource);
  }

  public function setSchema($schema) {
    $this->schema = $schema;
  }

  public function getSchema() {
    return $this->schema;
  }

}
