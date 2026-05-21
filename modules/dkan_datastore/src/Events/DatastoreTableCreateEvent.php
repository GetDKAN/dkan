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

  /**
   * Constructor.
   *
   * @param array $schema
   *   A Drupal Schema API array to describe the table schema.
   * @param DataResource $dataResource
   *   DataResource object for the table we're creating.
   */
  public function __construct(
    protected array $schema,
    DataResource $dataResource,
  ) {
    parent::__construct($dataResource);
  }

  /**
   * Set the schema for this event.
   *
   * @param $schema
   *   Drupal Schema API schema array.
   */
  public function setSchema($schema): void {
    $this->schema = $schema;
  }

  /**
   * Get the schema we're changing.
   *
   * @return array
   *   Drupal Schema API schema array.
   */
  public function getSchema(): array {
    return $this->schema;
  }

}
