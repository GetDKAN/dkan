<?php
/**
 * @file
 * Definition of DataHandler class.
 */

/**
 * Simple access methods to table data. Can be used on any table, not just Data
 * managed tables.
 */
class DataHandler {

  // Holds the name of the table that this handler is responsible for.
  protected $table;

  /**
   * Constructor, call indirectly through DataHandler::instance();
   */
  protected function __construct($table) {
    $this->table = $table;
  }

  /**
   * Instantiate a DataHandler object.
   *
   * @param $table
   *   The name of the table to access with this DataHandler object.
   */
  public static function instance($table) {
    static $handlers;
    if (!isset($handlers[$table])) {
      $handlers[$table] = new DataHandler($table);
    }
    return $handlers[$table];
  }

  /**
   * Getter.
   */
  public function __get($name) {
    return $this->$name;
  }

  /**
   * Load a record.
   */
  public function load($keys) {
    $schema = drupal_get_schema($this->table);
    $fields = $schema['fields'];
    $query = db_select(db_escape_table($this->table))->fields(db_escape_table($this->table));
    foreach ($keys as $key => $value) {
      if (!isset($fields[$key]['type'])) {
        return FALSE;
      }
      $query->condition($key, $value, is_array($value) ? 'IN' : '=');
    }
    if ($query->getArguments()) {
      $results = $query->execute()->fetchAll(PDO::FETCH_ASSOC);
      return empty($results) ? FALSE : $results;
    }
    return FALSE;
  }

  /**
   * Insert a record.
   *
   * @see drupal_write_record().
   *
   * @param $record
   *   An array that is the record to save to this handler's table.
   */
  public function insert($record) {
    $result = drupal_write_record($this->table, $record);
    module_invoke_all('data_insert', $record, $this->table);
    return $result;
  }

  /**
   * Update a record.
   *
   * @see drupal_write_record().
   *
   * @param $record
   *   An array that is the record to save to this handler's table.
   * @param $update
   *   A string or an array of strings that defines the keys to use for
   *   this update.
   */
  public function update($record, $update) {
    $result = drupal_write_record($this->table, $record, $update);
    module_invoke_all('data_update', $record, $this->table);
    return $result;
  }

  /**
   * Save one or more records to the table.
   *
   * If $update is given, method will try to update before.
   *
   * This method is more comfortable, but slower than using insert() or
   * update().
   *
   * @param $record
   *   An array that is the record to save to this handler's table.
   * @param
   *   A string or an array of strings that defines the keys to use for
   *   this update.
   */
  public function save($record, $update = array()) {
    if (is_string($update)) {
      $update = array($update);
    }
    if (is_array($update)) {
      $keys = array();
      foreach ($update as $key) {
        $keys[$key] = $record[$key];
      }
      if (count($keys)) {
        if ($this->load($keys)) {
          return $this->update($record, $update);
        }
      }
    }
    return $this->insert($record);
  }

  /**
   * Delete one or more records from the table.
   *
   * @param $clause
   *   An array where the keys are columns in the data table and the values are
   *   the values to filter on. This array will be turned into the where clause
   *   of the delete query.
   *
   *   Example:
   *
   *   array(
   *     'feed_nid' => 10,
   *     'timestamp' => array(
   *       '<',
   *       1255623780,
   *     ),
   *   );
   */
  public function delete($clause) {
    $query = db_delete($this->table);

    // Add the clauses.
    foreach ($clause as $column => $clause_data) {
      if (is_array($clause_data)) {
        list($operator, $value) = $clause_data;
      }
      else {
        $operator = '=';
        $value = $clause_data;
      }
      $query->condition($column, $value, $operator);
    }

    // Invoke hook_data_table_delete_rows().
    module_invoke_all('data_table_delete_rows', $this, $clause);

    // Return the number of deleted rows.
    return $query->execute();
  }

  /**
   * Empty data table.
   */
  public function truncate() {
    // TODO Please convert this statement to the D7 database API syntax.
    db_query('TRUNCATE TABLE {' . db_escape_table($this->table) . '}');
  }
}
