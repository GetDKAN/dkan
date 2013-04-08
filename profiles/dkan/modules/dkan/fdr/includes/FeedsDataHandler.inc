<?php

/**
 * @file
 * Data handler used in FeedsDataProcessor.
 *
 * @todo Move to data module?
 */

/**
 * Simple multidimensional data handler. Treats tables that join to this
 * handler's table through FeedsDatahandler::key as a cluster. Records in this
 * cluster are regarded as belonging to one multidimensional data set joined
 * by FeedsDatahandler::key.
 *
 * Limitations:
 *
 * - Records can only be tied together by a single key. Note: tables can still
 *   join through other fields to this table, but these table's data won't be
 *   considered of the same data set.
 * - save() is not supported. update() only supports updates on
 *   FeedsDataHandler::key
 * - Note: a record in depending tables will deleted when records from the base
 *   table are deleted.
 */
class FeedsDataHandler extends DataHandler {

  // An array of tables joining to the base table.
  protected $joined_tables;
  // A single field that the base table ($this->table) and depending tables
  // join on.
  protected $key;

  /**
   * Constructor, call indirectly through DataHandler::instance();
   */
  protected function __construct($table, $key) {
    $this->table = $table;
    $this->key = $key;

    // Find tables joining to this table.
    // @todo DB cache.
    $this->joined_tables = array();
    $tables = data_get_all_tables();
    foreach ($tables as $join_table) {
      if ($join_table->get('name') == $this->table) {
        // don't bother with joins on the same table...
        continue;
      }
      $meta = $join_table->get('meta');
      if (isset($meta['join'][$this->table]) && $meta['join'][$this->table]['left_field'] == $this->key) {
        // table has a field that joins to this table on $this->key
        $this->joined_tables[$join_table->get('name')] = $join_table->get('name');
      }
    }
  }

  /**
   * Instantiate a FeedsDataHandler object. We implement our own instantiator
   * method because DataHandler::instance() does not support a $key parameter.
   *
   * @param $table
   *   The name of the table to access with this DataHandler object.
   * @param $key
   *   The key that joins other tables.
   */
  public static function instance($table, $key) {
    static $handlers;
    if (!isset($handlers[$table][$key])) {
      $class = 'FeedsDataHandler';
      // @todo This is an undocumented stop gap until Data module supports
      // handler plugins.
      if ($info = variable_get($table .'_handler', NULL)) {
        $class = $info['class'];
        include_once drupal_get_path('module', $info['module']) .'/'. $info['file'];
      }
      $handlers[$table][$key] = new $class($table, $key);
    }
    return $handlers[$table][$key];
  }

  /**
   * Inserts a multi dimensional record.
   *
   * @param $record
   *   An array of a record to store. Keys are the names of fields or names of
   *   joining tables. Names of joining tables must start with #. Joined table
   *   keys must contain an array of values to insert.
   *
   *   Example:
   *   This is an array that inserts a title and a description into the base
   *   table and a series of tags into a depending table 'feeds_data_tags'.
   *   Note how the actual serial key is missing, it will be generated and
   *   passed on to depending tables by insert().
   *
   *   array(
   *     'title' => 'Example title',
   *     'description' => 'Lorem ipsum...',
   *     '#feeds_data_tags' => array(
   *       array(
   *         'tid' => 12,
   *       ),
   *       array(
   *         'tid' => 14,
   *       ),
   *       array(
   *         'tid' => 28,
   *       ),
   *     ),
   *   );
   */
  public function insert(&$record) {
    parent::insert($record);

    foreach ($record as $key => $value) {
      if ($handler = $this->joinedTableHandler($key)) {
        foreach ($value as $v) {
          // parent::insert() has populated the key or key must have been passed
          // in.
          $v[$this->key] = $record[$this->key];
          $handler->insert($v);
        }
      }
    }
  }

  /**
   * Updates a multi-dimensional record.
   *
   * Assumes that updates occur on keys. Does not support any other form of
   * updates.
   *
   * @see insert().
   *
   * @param $record
   *   An array of the record to update. Keys are the names of fields or names
   *   of joining tables. At least one key name in $record must match
   *   $this->key.
   */
  public function update(&$record) {
    parent::update($record, $this->key);

    foreach ($record as $key => $value) {
      if ($handler = $this->joinedTableHandler($key)) {
        foreach ($value as $v) {
          $handler->update($v, $this->key);
        }
      }
    }
  }

  /**
   * Delete records from this handler's base table and all joined tables.
   *
   * @param $clause
   *   Array that is a WHERE clause for the delete statement. The keys of the
   *   array are the field of the WHERE clause. If the value for a key is a
   *   simple type, then key = value is assumed. If the value is an array, then
   *   the first value of that array is the operation, the second value is the
   *   value to compare against.
   *
   *   Example:
   *
   *   This where clause would delete all records
   *   WHERE feed_nid = 10 AND timestamp < 1255623780
   *
   *   array(
   *     'feed_nid' => 10,
   *     'timestamp' => array(
   *       '<',
   *       1255623780,
   *     ),
   *   );
   *
   * @todo Push this functionality into DataHandler.
   */
  public function delete($clause) {
    $query = new DataQuery($this->table);
    $schema = drupal_get_schema($this->table);
    $fields = $schema['fields'];
    $this_table = db_escape_table($this->table);
    foreach ($clause as $key => $value) {
      $operator = '=';
      if (is_array($value)) {
        $operator = array_shift($value);
        $value = array_shift($value);
      }
      $query->addWhere($this_table .'.'. db_escape_string($key) ." ". $operator ." ". db_type_placeholder($fields[$key]['type']), $value);
    }

    foreach ($this->joined_tables as $table) {
      $table = db_escape_table($table);
      $query->addSubject($table);
      $query->addJoin($table, "$table.{$this->key} = $this_table.{$this->key}", 'LEFT JOIN');
    }
    drupal_alter('data_delete_query', $query, $this->table);
    if (!empty($query->where)) {
      $query->execute();
    }
    return db_affected_rows();
  }

  /**
   * Helper. Returns a joined table for a given table name that starts with a #.
   *
   * @see: insert(), update().
   *
   * @param $name
   *   Potential table name. If name does not start with a #, method will always
   *   return FALSE.
   *
   * @return
   *   A DataHandler object if a joined table with this name could be found,
   *   FALSE otherwise. Returns FALSE if $name does not start with #.
   *
   * @throws Exception
   *   Throws Exception if a table name starting with # is given, but a
   *   DataHandler can't be found.
   */
  protected function joinedTableHandler($name) {
    if (strpos($name, '#') === 0) {
      $name = substr($name, 1);
      if (in_array($name, $this->joined_tables)) {
        return data_get_handler($name);
      }
      throw new Exception(t('Data handler for table !name not found.', array('!name' => $name)));
    }
    return NULL;
  }

  /**
   * @todo Support save().
   */
  public function save(&$record, $update) {
    throw new Exception(t('Not implemented.'));
  }
}
