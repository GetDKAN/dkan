<?php
/**
 * @file
 * Contains class definition for DataTable.
 */

/**
 * Manages data access and manipulation for a single data table.
 * Use data_create_table() or data_get_table() to instantiate an object from this class.
 *
 * @see data_create_table().
 * @see data_get_table().
 *
 * Usage:
 *
 * Get an existing table.
 *
 * $table = data_get_table('my_table');
 *
 * If the table does not exist, create one.
 * if (!$table) {
 *  $table = data_create_table('my_table', $schema);
 * }
 *
 * Save some data to it.
 * $handler = data_get_handler($table->get('name'));
 * $handler->save($data);
 *
 * Remove the data from the table.
 * $handler->truncate();
 *
 * Remove the table, but not the meta information about the table.
 * $table->drop();
 *
 */
class DataTable {

  // Class variables.
  // @todo: change $table_schema to $schema.
  // @todo: change $name to $id.
  // Unfortunately drupal_write_record does not escape field names. $table_schema instead of $schema it is.
  public $name, $title, $table_schema, $meta, $export_type;

  /**
   * Instiate a DataTable object. Use this function instead of new DataTable.
   */
  public static function instance($name) {
    static $tables;
    if (!isset($tables[$name])) {
      $tables[$name] = new DataTable($name);
    }
    return $tables[$name];
  }

  /**
   * Constructor. Do not call directly, but use DataTable::instance($name) instead.
   */
  protected function __construct($name) {
    // Set our name after sanitizing it.
    $this->name = db_escape_table($name);

    // Try to load table information.
    if ($table = _data_load_table($name)) {
      foreach (array('title', 'name', 'table_schema', 'meta', 'export_type') as $key) {
        if (isset($table->$key)) {
          $this->$key = $table->$key;
        }
      }
    }
  }

  /**
   * Create a table.
   *
   * Do not call directly but use data_create_table() instead.
   */
  public function create($table_schema) {

    // Only create the table if it is not defined as data table AND it does not
    // physically exist.
    if (!_data_load_table($this->name, TRUE) && !db_table_exists($this->name)) {

      // Create table.
      try {
        db_create_table($this->name, $table_schema);
      }
      catch (DatabaseSchemaObjectExistsException $e) {
        drupal_set_message(t('Error creating table.'), 'error');
        return FALSE;
      }

      // If schema module is enabled, inspect and read back to make
      // sure our schema information is up to date.
      // @todo: this is slow, maybe we need to make this an explicit method
      // on DataTable.
      if (module_exists('schema')) {
        $schema = schema_dbobject()->inspect();
        if (isset($schema[$this->name])) {
          $table_schema = $schema[$this->name];
        }
      }

      // Set table_schema and export_type.
      // @todo: rather user _data_table_load() ?
      $this->table_schema = $table_schema;
      $this->export_type = EXPORT_IN_DATABASE;

      // Save table information.
      // Set export_type - needs to be defined so that schema information is being passed on
      // to Drupal by data_schema_alter().
      // @todo: refactor ->update() to ->save() and use ->save().
      $table = array(
        'name' => $this->name,
        'table_schema' => $this->table_schema,
      );
      drupal_write_record('data_tables', $table);

      // Clear caches.
      drupal_get_schema($this->name, TRUE);

      // Have views read new views information about table.
      if (module_exists('views')) {
        views_invalidate_cache();
      }

      // data ui exposes path to a new default view.
      if (module_exists('data_ui')) {
        menu_rebuild();
      }

      return TRUE;
    }
    return FALSE;
  }

  /**
   * Let Data manage a table that already exists in the database.
   *
   * Uses the $name property of the object to determine which database table to
   * adopt.
   *
   * @return
   *   TRUE if the table was successfully adopted; FALSE if the table was
   *   already known to Data, if the query failed, or if Schema isn't available.
   */
  public function adopt() {
    if ($this->defined() || !module_exists('schema')) {
      return FALSE;
    }

    $schema = schema_dbobject()->inspect(variable_get('schema_database_connection', 'default'), $this->name);
    if (isset($schema[$this->name])) {
      $table = array(
        'name' => $this->name,
        'title' => data_natural_name($this->name),
        'table_schema' => $schema[$this->name],
        // Add in an empty meta array with the field names so other modules can rely on it.
        'meta' => array(
          'fields' => array_fill_keys(array_keys($schema[$this->name]['fields']), array()),
        ),
      );
      if (drupal_write_record('data_tables', $table)) {
        return TRUE;
      }
    }

    // Clear caches.
    drupal_get_schema($this->name, TRUE);

    // Have views read new views information about table.
    // @todo: this doesn't seem to quite cut it.
    if (module_exists('views')) {
      views_invalidate_cache();
    }

    // data ui exposes path to a new default view.
    if (module_exists('data_ui')) {
      menu_rebuild();
    }

    return FALSE;
  }

  /**
   * Remove a table from Data module's management, ie unadopt it.
   *
   * Uses the $name property of the object to determine which database table to
   * adopt.
   *
   * @return
   *   TRUE if the table was successfully disowned; FALSE if the query failed,
   *   or if Schema isn't available.
   */
  public function disown() {
    if (!module_exists('schema')) {
      return FALSE;
    }

    $num_deleted = db_delete('data_tables')
      ->condition('name', $this->name)
      ->execute();

    return ($num_deleted == 1);
  }

  /**
   * Determine whether a table is defined.
   *
   * @return
   *   TRUE if the table is defined, FALSE otherwise.
   *   Note: If a table is defined it does not mean that it actually exists in the
   *   database.
   */
  public function defined() {
    return _data_load_table($this->name) ? TRUE : FALSE;
  }

  /**
   * Get a property of the DataTable object.
   *
   * @todo: use __get()
   *
   * @param $property
   *   One of 'name', 'title', 'table_schema', 'meta'.
   * @return
   *   The unserialized value of the property.
   */
  public function get($property) {
    if (in_array($property, array('name', 'title', 'table_schema', 'meta', 'export_type'))) {
      return $this->$property;
    }
  }

  /**
   * Update table properties.
   *
   * @todo: make conditional, rename to save().
   *
   * @param $properties
   *   Array where the key designates a property (one of 'name', 'title', 'table_schema', 'meta')
   *   and the value is the unserialized value that this property should attain.
   */
  public function update($properties) {
    _data_override($this->name);
    $properties['name'] = $this->name;
    if (drupal_write_record('data_tables', $properties, 'name')) {
      foreach ($properties as $key => $value) {
        $this->$key = $value;
      }
    }
  }

  /**
   * Compare this table's schema to schema of table in DB.
   * Requires schema module.
   *
   * @return
   *
   */
  public function compareSchema() {
    if (module_exists('schema')) {
      $this->table_schema['name'] = $this->name;
      return schema_compare_table($this->table_schema);
    }
  }

  /**
   * Add a field.
   *
   * @throws DataException
   *
   * @todo: Check wether field name is available, otherwise change.
   */
  public function addField($field, $spec) {
    try {
      db_add_field($this->name, $field, $spec);

      $schema = $this->table_schema;
      $schema['fields'][$field] = $spec;
      $this->update(array('table_schema' => $schema));

      // @todo: use clearCaches().
      drupal_get_schema($this->name, TRUE);
      // Invalidate views caches to use new field immediately.
      if (function_exists('views_invalidate_cache')) {
        views_invalidate_cache();
      }
      return $field;
    }
    catch (DatabaseSchemaObjectExistsException $e) {
      throw new DataException(t('Error adding field.'));
    }

    throw new DataException(t('Error adding field.'));
  }

  /**
   * Add an index to table.
   *
   * @todo: support more than one field.
   */
  public function addIndex($field) {
    $schema = $this->table_schema;
    if ($schema['fields'][$field]) {
      $index = data_get_index_definition($field, $schema['fields'][$field]);
      try {
        db_add_index($this->name, $field, $index);
      }
      catch (DatabaseSchemaObjectExistsException $e) {
        throw new DataException(t('Error adding index.'));
      }

      $schema['indexes'][$field] = $index;
      $this->update(array('table_schema' => $schema));
      drupal_get_schema($this->name, TRUE);
    }
  }

  /**
   * Drop an index from a table.
   *
   * @throws DataException
   */
  public function dropIndex($field) {
    $success = db_drop_index($this->name, $field);
    if ($success) {
      $schema = $this->table_schema;
      unset($schema['indexes'][$field]);
      $this->update(array('table_schema' => $schema));
      drupal_get_schema($this->name, TRUE);
      return;
    }
    throw new DataException(t('Error dropping index.'));
  }

  /**
   * Add a unique key to a field.
   *
   * @throws DataException
   */
  public function addUniqueKey($field) {
    $schema = $this->table_schema;
    if ($schema['fields'][$field]) {
      $index = data_get_index_definition($field, $schema['fields'][$field]);
      try {
        $success = db_add_unique_key($this->name, $field, $index);
      }
      catch (DatabaseSchemaObjectExistsException $e) {
        throw new DataException(t('Error adding unique key.'));
      }
      if ($success) {
        $schema['unique keys'][$field] = array($field);
        $this->update(array('table_schema' => $schema));
        drupal_get_schema($this->name, TRUE);
        return;
      }
    }
  }

  /**
   * Drop a unique key from a table.
   *
   * @throws DataException
   */
  public function dropUniqueKey($field) {
    $success = db_drop_unique_key($this->name, $field);
    if ($success) {
      $schema = $this->table_schema;
      unset($schema['unique keys'][$field]);
      $this->update(array('table_schema' => $schema));
      drupal_get_schema($this->name, TRUE);
      return;
    }
    throw new DataException(t('Error dropping unique key.'));
  }

  /**
   * Change indexes of a table.
   *
   * @throws DataException
   */
  public function changeIndex($fields) {
    $schema = $this->table_schema;

    // @TODO: This array_keys() reduces indexes to single field indexes.
    // Will need adjustment when multi-field indexes are implemented.
    $indexes = isset($schema['indexes']) ? array_keys($schema['indexes']) : array();

    $add = array_diff($fields, $indexes);
    $drop = array_diff($indexes, $fields);

    foreach ($add as $field) {
      $this->addIndex($field);
    }
    foreach ($drop as $field) {
      $this->dropIndex($field);
    }
  }

  /**
   * Add a primary key to table.
   *
   * @throws DataException
   */
  public function addPrimaryKey($fields) {
    $schema = $this->table_schema;
    foreach ($fields as $field) {
      if ($schema['fields'][$field]['type'] == 'text') {
        throw new DataException(t('A text field cannot be made a primary key.'));
      }
    }

    try {
      db_add_primary_key($this->name, $fields);
    }
    catch (DatabaseSchemaObjectExistsException $e) {
      throw new DataException(t('Error creating primary key.'));
    }

    $schema['primary key'] = $fields;
    $this->update(array('table_schema' => $schema));
    drupal_get_schema($this->name, TRUE);
  }

  /**
   * Drop all primary keys from a table.
   *
   * @throws DataException
   */
  public function dropPrimaryKey() {
    db_drop_primary_key($this->name);

    $schema = $this->table_schema;
    $schema['primary key'] = array();
    $this->update(array('table_schema' => $schema));
    drupal_get_schema($this->name, TRUE);
    return;
  }

  /**
   * Change the primary keys of a table.
   *
   * @throws DataException
   */
  public function changePrimaryKey($fields) {
    $schema = $this->table_schema;
    if (!empty($schema['primary key'])) {
      $this->dropPrimaryKey();
    }
    if (!empty($fields)) {
      $this->addPrimaryKey($fields);
    }
  }

  /**
   * Change a field.
   *
   * @throws DataException
   */
  public function changeField($field, $spec) {
    // If new type is text, check for PK and index restrictions.
    if ($spec['type'] == 'text') {
      if (in_array($field, $this->table_schema['primary key'])) {
        throw new DataException(t('Cannot make a primary key field a text field.'));
      }
      foreach ($this->table_schema['indexes'] as $index_name => $index) {
        foreach ($index as $index_field) {
          if (is_array($index_field)) {
            $index_field = array_shift($index_field);
          }
          if ($field == $index_field) {
            $this->dropIndex($index_field);
          }
        }
      }
    }
    try {
      db_change_field($this->name, $field, $field, $spec);
    }
    catch (DatabaseSchemaObjectDoesNotExistException $e) {
      throw new DataException(t('Cannot change field.'));
    }

    $schema = $this->table_schema;
    $schema['fields'][$field] = $spec;
    $this->update(array('table_schema' => $schema));
    drupal_get_schema($this->name, TRUE);
  }

  /**
   * Delete a field.
   *
   * @throws DataException
   */
  public function dropField($field) {
    $success = db_drop_field($this->name, $field);

    if ($success) {
      $schema = $this->table_schema;
      unset($schema['fields'][$field]);
      $meta = $this->meta;
      unset($meta['fields'][$field]);
      $this->update(array('table_schema' => $schema), array('meta' => $meta));
      drupal_get_schema($this->name, TRUE);
      return;
    }
    throw new DataException(t('Cannot drop field.'));
  }

  /**
   * Drop a table. Does not drop a table if its defined in code.
   *
   * @return
   *   TRUE if the table was dropped, FALSE otherwise.
   */
  public function drop() {
    if ($this->export_type == EXPORT_IN_DATABASE) {
      if (db_table_exists($this->name)) {
        db_drop_table($this->name);
      }
      $this->update(array('table_schema' => array()));
      drupal_get_schema($this->name, TRUE);

      db_delete('data_tables')
        ->condition('name', $this->name)
        ->execute();
      $this->title = '';
      $this->table_schema = $this->meta = array();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Revert a table to its definition in code.
   *
   * Does not revert a table if it is not defined in code.
   *
   * @return
   *   TRUE if the table was reverted, FALSE otherwise.
   */
  public function revert() {
    if ($this->export_type & EXPORT_IN_CODE) {
      db_delete('data_tables')
        ->condition('name', $this->name)
        ->execute();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Link this table to another table. Linking a table to another one is
   * to define how data in these tables should be joined to each other.
   *
   * There can be more than one link to the left of a table. However,
   * for views integration, only the first join created will be used.
   *
   * @todo: Get rid of link() language, use setJoin()/removeJoin() instead.
   */
  public function link($left_table, $left_field, $field = NULL, $inner_join = TRUE) {
    if ($field == NULL) {
      $field = $left_table;
    }
    $this->meta['join'][$left_table] = array(
      'left_field' => $left_field,
      'field' => $field,
      'inner_join' => $inner_join,
    );
    $this->update(array('meta' => $this->meta));
  }

  /**
   * Unlink this table from another table.
   */
  public function unlink($left_table) {
    unset($this->meta['join'][$left_table]);
    $this->update(array('meta' => $this->meta));
  }

  /**
   * Convenience method.
   */
  public function handler() {
    return data_get_handler($this->name);
  }

  /**
   * Clear relevant caches. Call after operations that create, delete or modify
   * tables.
   */
  public static function clearCaches() {
    // Clear the schema cache.
    drupal_get_schema(NULL, TRUE);
    // Have views read new views information about table.
    if (module_exists('views')) {
      views_invalidate_cache();
    }
    // data ui exposes path to a new default view.
    if (module_exists('data_ui')) {
      menu_rebuild();
    }
  }
}
