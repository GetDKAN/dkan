<?php

/**
 * @file
 * Contains the SearchApiEntityDataSourceController class.
 */

/**
 * Data source for all entities known to the Entity API.
 */
class SearchApiEntityDataSourceController extends SearchApiAbstractDataSourceController {

  /**
   * Return information on the ID field for this controller's type.
   *
   * @return array
   *   An associative array containing the following keys:
   *   - key: The property key for the ID field, as used in the item wrapper.
   *   - type: The type of the ID field. Has to be one of the types from
   *     search_api_field_types(). List types ("list<*>") are not allowed.
   */
  public function getIdFieldInfo() {
    $info = entity_get_info($this->entityType);
    $properties = entity_get_property_info($this->entityType);
    if (empty($info['entity keys']['id'])) {
      throw new SearchApiDataSourceException(t("Entity type @type doesn't specify an ID key.", array('@type' => $info['label'])));
    }
    $field = $info['entity keys']['id'];
    if (empty($properties['properties'][$field]['type'])) {
      throw new SearchApiDataSourceException(t("Entity type @type doesn't specify a type for the @prop property.", array('@type' => $info['label'], '@prop' => $field)));
    }
    $type = $properties['properties'][$field]['type'];
    if (search_api_is_list_type($type)) {
      throw new SearchApiDataSourceException(t("Entity type @type uses list field @prop as its ID.", array('@type' => $info['label'], '@prop' => $field)));
    }
    if ($type == 'token') {
      $type = 'string';
    }
    return array(
      'key' => $field,
      'type' => $type,
    );
  }

  /**
   * Load items of the type of this data source controller.
   *
   * @param array $ids
   *   The IDs of the items to laod.
   *
   * @return array
   *   The loaded items, keyed by ID.
   */
  public function loadItems(array $ids) {
    $items = entity_load($this->entityType, $ids);
    // If some items couldn't be loaded, remove them from tracking.
    if (count($items) != count($ids)) {
      $ids = array_flip($ids);
      $unknown = array_keys(array_diff_key($ids, $items));
      if ($unknown) {
        search_api_track_item_delete($this->type, $unknown);
      }
    }
    return $items;
  }

  /**
   * Get a metadata wrapper for the item type of this data source controller.
   *
   * @param $item
   *   Unless NULL, an item of the item type for this controller to be wrapped.
   * @param array $info
   *   Optionally, additional information that should be used for creating the
   *   wrapper. Uses the same format as entity_metadata_wrapper().
   *
   * @return EntityMetadataWrapper
   *   A wrapper for the item type of this data source controller, according to
   *   the info array, and optionally loaded with the given data.
   *
   * @see entity_metadata_wrapper()
   */
  public function getMetadataWrapper($item = NULL, array $info = array()) {
    return entity_metadata_wrapper($this->entityType, $item, $info);
  }

  /**
   * Get the unique ID of an item.
   *
   * @param $item
   *   An item of this controller's type.
   *
   * @return
   *   Either the unique ID of the item, or NULL if none is available.
   */
  public function getItemId($item) {
    $id = entity_id($this->entityType, $item);
    return $id ? $id : NULL;
  }

  /**
   * Get a human-readable label for an item.
   *
   * @param $item
   *   An item of this controller's type.
   *
   * @return
   *   Either a human-readable label for the item, or NULL if none is available.
   */
  public function getItemLabel($item) {
    $label = entity_label($this->entityType, $item);
    return $label ? $label : NULL;
  }

  /**
   * Get a URL at which the item can be viewed on the web.
   *
   * @param $item
   *   An item of this controller's type.
   *
   * @return
   *   Either an array containing the 'path' and 'options' keys used to build
   *   the URL of the item, and matching the signature of url(), or NULL if the
   *   item has no URL of its own.
   */
  public function getItemUrl($item) {
    if ($this->entityType == 'file') {
      return array(
        'path' => file_create_url($item->uri),
        'options' => array(
          'entity_type' => 'file',
          'entity' => $item,
        ),
      );
    }
    $url = entity_uri($this->entityType, $item);
    return $url ? $url : NULL;
  }

  /**
   * Initialize tracking of the index status of items for the given indexes.
   *
   * All currently known items of this data source's type should be inserted
   * into the tracking table for the given indexes, with status "changed". If
   * items were already present, these should also be set to "changed" and not
   * be inserted again.
   *
   * @param array $indexes
   *   The SearchApiIndex objects for which item tracking should be initialized.
   *
   * @throws SearchApiDataSourceException
   *   If any of the indexes doesn't use the same item type as this controller.
   */
  public function startTracking(array $indexes) {
    if (!$this->table) {
      return;
    }
    // We first clear the tracking table for all indexes, so we can just insert
    // all items again without any key conflicts.
    $this->stopTracking($indexes);

    $entity_info = entity_get_info($this->entityType);

    if (!empty($entity_info['base table'])) {
      // Use a subselect, which will probably be much faster than entity_load().

      // Assumes that all entities use the "base table" property and the
      // "entity keys[id]" in the same way as the default controller.
      $id_field = $entity_info['entity keys']['id'];
      $table = $entity_info['base table'];

      // We could also use a single insert (with a JOIN in the nested query),
      // but this method will be mostly called with a single index, anyways.
      foreach ($indexes as $index) {
        // Select all entity ids.
        $query = db_select($table, 't');
        $query->addField('t', $id_field, 'item_id');
        $query->addExpression(':index_id', 'index_id', array(':index_id' => $index->id));
        $query->addExpression('1', 'changed');

        // INSERT ... SELECT ...
        db_insert($this->table)
          ->from($query)
          ->execute();
      }
    }
    else {
      // In the absence of a 'base table', use the slow entity_load().
      parent::startTracking($indexes);
    }
  }

  /**
   * Helper method that can be used by subclasses instead of implementing startTracking().
   *
   * Returns the IDs of all items that are known for this controller's type.
   *
   * Will be used when the entity type doesn't specify a "base table".
   *
   * @return array
   *   An array containing all item IDs for this type.
   */
  protected function getAllItemIds() {
    return array_keys(entity_load($this->entityType));
  }

}
