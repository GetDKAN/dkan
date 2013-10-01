<?php

/**
 * @file
 * RESTful web services module integration for entities.
 */

/**
 * Specifies CRUD and access methods for resources.
 */
interface RestWSResourceControllerInterface {

  /**
   * Returns the property info for the given resource.
   *
   * @return array
   *   An array structured as hook_entity_property_info() is structured for an
   *   entity type.
   */
  public function propertyInfo();

  /**
   * Returns a metadata wrapper for the resource with the given id.
   *
   * @return EntityStructureWrapper
   *   Metadata wrapper of the resource.
   */
  public function wrapper($id);

  /**
   * Create a new resource.
   *
   * @param array $values
   *   Array of values for properties of the resource, keyed by property
   *   name. At least for all required properties values have to be given.
   *
   * @return int|string
   *   The id of the newly created resource.
   */
  public function create(array $values);

  /**
   * Returns an existing resource.
   *
   * @param int|string $id
   *   The id of the resource that should be returned.
   *
   * @return
   *   The internal representation of the resource.
   */
  public function read($id);

  /**
   * Update an existing resource.
   *
   * @param int|string $id
   *   The id of the resource that should be updated.
   * @param array $values
   *   An array of values for the properties to be updated, keyed by property
   *   name.
   */
  public function update($id, array $values);

  /**
   * Delete an existing resource.
   *
   * @param int|string $id
   *   The id of the resource that should be deleted.
   */
  public function delete($id);

  /**
   * Determines access for a given operation and resource.
   *
   * @param string $op
   *   Either 'create', 'view' (= read), 'update' or 'delete'.
   * @param int|string $id
   *   The id of the resource.
   *
   * @see entity_access()
   */
  public function access($op, $id);

  /**
   * Returns the name of the resource.
   */
  public function resource();
}

/**
 * Specifies query methods for resources.
 */
interface RestWSQueryResourceControllerInterface extends RestWSResourceControllerInterface {
  /**
   * Query for a list of resources.
   *
   * @param array $filters
   *   A list of properties to query for, or an empty array if all resources
   *   should be counted.
   * @param array $meta_controls
   *   @see restws_meta_controls()
   *
   * @return array
   *   An array containing the ids of the matching resources.
   */
  public function query($filters = array(), $meta_controls = array());

  /**
   * Returns the number of resources available with the given filters.
   *
   * @param array $filters
   *   A list of properties to query for, or an empty array if all resources
   *   should be returned.
   *
   * @return int
   *   The number of resources available.
   */
  public function count($filters = array());

  /**
   * Returns the limit for the current query.
   *
   * @param int $client_limit
   *   The limit specified in the meta controls or NULL if not set.
   *
   * @return int
   *   The limit of the current limit.
   */
  public function limit($client_limit = NULL);
}

/**
 * Controller for entity-bases resources.
 */
class RestWSEntityResourceController implements RestWSQueryResourceControllerInterface {

  protected $entityType, $entityInfo;

  public function __construct($name, $info) {
    $this->entityType = $name;
    $this->entityInfo = entity_get_info($name);
  }

  public function propertyInfo() {
    return entity_get_all_property_info($this->entityType);
  }

  public function wrapper($id) {
    return entity_metadata_wrapper($this->entityType, $id);
  }

  public function read($id) {
    return $this->wrapper($id)->value();
  }

  public function create(array $values) {
    // Make sure that bundle information is present on entities that have
    // bundles.
    $entity_info = entity_get_info($this->entityType);
    if (isset($entity_info['bundle keys'])) {
      foreach ($entity_info['bundle keys'] as $bundle_key) {
        if (!array_key_exists($bundle_key, $values)) {
          throw new RestWSException('Missing bundle: ' . $bundle_key, 406);
        }
      }
    }

    try {
      $wrapper = entity_property_values_create_entity($this->entityType, $values);
      // Get the ID and bundle property names.
      $entity_keys = array_intersect_key($entity_info['entity keys'], array('id' => 1, 'bundle' => 1));

      foreach (array_keys($values) as $name) {
        // Don't check access on entity keys for new entities. Otherwise,
        // property access checks will fail for, e.g., node type, which
        // requires the 'administer nodes' permission to set.
        // @see entity_metadata_node_entity_property_info().
        if (!in_array($name, $entity_keys)) {
          if (!$this->checkPropertyAccess($wrapper, $name, $wrapper->{$name})) {
            throw new RestWSException(t('Not authorized to set property @p', array('@p' => $name)), 403);
          }
        }
      }
    }
    catch (EntityMetadataWrapperException $e) {
      throw new RestWSException($e->getMessage(), 406);
    }

    $properties = $wrapper->getPropertyInfo();
    $diff = array_diff_key($values, $properties);
    if (!empty($diff)) {
      throw new RestWSException('Unknown data properties: ' . implode(' ', array_keys($diff)) . '.', 406);
    }
    $wrapper->save();
    return $wrapper->getIdentifier();
  }

  public function update($id, array $values) {
    $wrapper = $this->wrapper($id);
    $entity_info = $wrapper->entityInfo();
    // Get the ID and bundle property names.
    $entity_keys = array_intersect_key($entity_info['entity keys'], array('id' => 1, 'bundle' => 1));
    try {
      foreach ($values as $name => $value) {
        if (in_array($name, $entity_keys)) {
          // We don't allow changing the entity ID or bundle.
          if ($wrapper->{$name}->value() != $value) {
            throw new RestWSException('Unable to change ' . $name, 422);
          }
        }
        else {
          $wrapper->{$name}->set($value);
          if (!$this->checkPropertyAccess($wrapper, $name, $wrapper->{$name})) {
            throw new RestWSException(t('Not authorized to set property @p', array('@p' => $name)), 403);
          }
        }
      }
    }
    catch (EntityMetadataWrapperException $e) {
      throw new RestWSException($e->getMessage(), 406);
    }
    $wrapper->save();
  }

  public function delete($id) {
    entity_delete($this->entityType, $id);
  }

  /**
   * Implements RestWSQueryResourceControllerInterface::query().
   */
  public function query($filters = array(), $meta_controls = array()) {
    $limit = variable_get('restws_query_max_limit', 100);
    $offset = 0;

    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', $this->resource());

    foreach ($filters as $filter => $value) {
      $this->propertyQueryOperation($query, 'Condition', $filter, $value);
    }

    $rest_controls = restws_meta_controls();
    foreach ($meta_controls as $control_name => $value) {
      switch ($control_name) {
        case $rest_controls['sort']:
          if (isset($meta_controls[$rest_controls['direction']]) && strtolower($meta_controls[$rest_controls['direction']]) == 'desc') {
            $direction = 'DESC';
          }
          else {
            $direction = 'ASC';
          }
          $this->propertyQueryOperation($query, 'OrderBy', $value, $direction);
          break;

        case $rest_controls['limit']:
          $limit = $this->limit($value);
          break;

        case $rest_controls['page']:
          $offset = $value > 0 ? $value : $offset;
          break;
      }
    }

    // Calculate the offset.
    $offset *= $limit;
    $query->range($offset, $limit);

    $this->nodeAccess($query);

    // Catch any errors, like wrong keywords or properties.
    try {
      $query_result = $query->execute();
    }
    catch (PDOException $exception) {
      throw new RestWSException('Query failed.', 400);
    }
    $query_result = isset($query_result[$this->resource()]) ? $query_result[$this->resource()] : array();

    $result = array_keys($query_result);

    return $result;
  }


  /**
   * Implements RestWSQueryResourceControllerInterface::count().
   */
  public function count($filters = array()) {
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', $this->resource());

    foreach ($filters as $filter => $value) {
      $this->propertyQueryOperation($query, 'Condition', $filter, $value);
    }
    $query->count();
    $this->nodeAccess($query);

    return $query->execute();
  }

  /**
   * Helper function to respect node permissions while querying.
   *
   * @param EntityFieldQuery $query
   *   The query object.
   */
  protected function nodeAccess(EntityFieldQuery $query) {
    // Respect node access and filter out unpublished nodes if user lacks
    // the right permission.
    if ($this->resource() == 'node') {
      $query->addTag('node_access');
      if (!user_access('bypass node access')) {
        $this->propertyQueryOperation($query, 'Condition', 'status', 1);
      }
    }
  }

  /**
   * Implements RestWSQueryResourceControllerInterface::limit().
   */
  public function limit($client_limit = NULL) {
    $limit = variable_get('restws_query_max_limit', 100);
    // Only allow user provided limits smaller than the system hard limit.
    if (!empty($client_limit) && $client_limit < $limit) {
      return $client_limit;
    }
    else {
      return $limit;
    }
  }

  public function access($op, $id) {
    return entity_access($op, $this->entityType, isset($id) ? $this->wrapper($id)->value() : NULL);
  }

  public function resource() {
    return $this->entityType;
  }

  /**
   * Helper function which takes care of distinguishing between fields and
   * entity properties and executes the right EntityFieldQuery function for it.
   *
   * @param EntityFieldQuery $query
   *   The EntityFieldQuery pointer which should be used.
   *
   * @param string $operation
   *   The general function name, without the words 'property' or 'field'.
   *
   * @param string $property
   *   The property or field which should be used.
   *
   * @param string|array $value
   *   The value for the function.
   */
  protected function propertyQueryOperation(EntityFieldQuery $query, $operation, $property, $value) {
    $properties = $this->propertyInfo();

    // If field is not set, then the filter is a property and we can extract
    // the schema field from the property array.
    if (empty($properties[$property]['field'])) {
      $column = $properties[$property]['schema field'];
      $operation = 'property' . $operation;
      $query->$operation($column, $value);
    }
    else {
      // For fields we need the field info to get the right column for the
      // query.
      $field_info = field_info_field($property);
      $operation = 'field' . $operation;
      if (is_array($value)) {
        // Specific column filters are given, so add a query condition for each
        // one of them.
        foreach ($value as $column => $val) {
          $query->$operation($field_info, $column, $val);
        }
      }
      else {
        // Just pick the first field column for the operation.
        $columns = array_keys($field_info['columns']);
        $column = $columns[0];
        $query->$operation($field_info, $column, $value);
      }
    }
  }

  /**
   * Helper method to check access on a property.
   *
   * @todo Remove this once Entity API properly handles text format access.
   *
   * @param EntityMetadataWrapper $entity
   *   The parent entity.
   * @param string $property_name
   *   The property name on the entity.
   * @param EntityMetadataWrapper $property
   *   The property whose access is to be checked.
   *
   * @return bool
   *   TRUE if the current user has access to set the property, FALSE otherwise.
   */
  protected function checkPropertyAccess($entity, $property_name, $property) {
    global $user;
    // Special case node author: we allow access if set to the current user.
    if ($entity->type() == 'node' && $property_name == 'author' && $property->raw() == $GLOBALS['user']->uid) {
      return TRUE;
    }
    // @todo Hack to check format access for text fields. Should be removed once
    // this is handled properly on the Entity API level.
    elseif ($property->type() == 'text_formatted' && $property->format->value()) {
      $format = (object) array('format' => $property->format->value());
      if (!filter_access($format)) {
        return FALSE;
      }
    }
    return $property->access('edit');
  }
}
