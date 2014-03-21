<?php

/**
 * Entity controller class for Data entities.
 */
class DataEntityController extends EntityAPIController implements EntityAPIControllerInterface {

  protected $dataTable;

  /**
   * Overridden.
   * @see EntityAPIController#__construct()
   */
  public function __construct($entityType) {
    parent::__construct($entityType);

    // Set our data table. Our entity type is 'data_TABLENAME'.
    $this->dataTable = substr($entityType, 5);
  }

  function load($ids = array(), $conditions = array()) {
    $entities = parent::load($ids, $conditions);

    foreach ($entities as $id => $entity) {
      $entity->data_table   = $this->dataTable;
      // Needed until Drupal core fixes this WTF. @see http://drupal.org/node/1042822.
      $entity->entity_type  = $this->entityType;
    }

    return $entities;
  }
}
