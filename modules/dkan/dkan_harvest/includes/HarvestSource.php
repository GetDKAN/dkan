<?php

/**
 * @file
 * File for dkan_harvest HarvestSource class. This will serve as a in code
 * documentation as well, please update the comments if you update the class!
 */

/**
 * Dkan Harvest HarvestSource Object is user to store the sources properties needed to
 * indentify a source to harvest. Those properties are:
 *
 * - 'machine_name' (Required): Unique identifier for this source.
 * - 'uri' (Required): Location of the content to harvest for this source. This can be a
 * standard URL 'http://data_json_remote' or a local file path '/home/test/source/file'.
 * - 'type' (Required): Type of the endpoint protocol that this source is pulling from.
 * - 'name' (String, Optional): User friendly name used to display this source. If
 * empty will use the 'machine_name' property.
 * - 'filters' => array('keyword' => array('health')) (Optional): Filter items
 * preseting the following values.
 * - 'excludes' => array('keyword' => array('tabacco')) (Optional): Exclude
 * items presenting the following values (Optional).
 * - 'defaults' => array('keyword' => array('harvested dataset') (Optional):
 * Provide defaults.
 * - 'overrides' => array('author' => 'Author') (Optional): Provide overrides .
 */

class HarvestSource {
  public $machine_name;
  public $uri;
  public $type;
  public $label;
  public $filters = array();
  public $excludes = array();
  public $defaults = array();
  public $overrides = array();

  /**
   * Constructor for HarvestSource class.
   *
   * @param Array source: Source array containing atleast all the required
   * source elements (As documented above) and any other optional proprety.
   */
  public function __construct($machine_name) {
    // $machine_name is really needed to construct this object.
    if (empty($machine_name) || is_null($machine_name)) {
      throw new Exception(t('machine name is required!'));
    }

    // Query the DB for a harvest_source node matching the machine name.
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'node')
      ->entityCondition('bundle', 'harvest_source')
      ->propertyCondition('status', NODE_PUBLISHED)
      ->fieldCondition('field_dkan_harvest_machine_name', 'machine', $machine_name);
    $result = $query->execute();

    if (!isset($result['node'])) {
      throw new Exception(t('Harvest Source node with machine name %s not found.', array('%s' => $machine_name)));
    }

    $harvest_source_nids = array_keys($result['node']);
    $harvest_source_node = entity_load_single('node', array_pop($harvest_source_nids));
    $harvest_source_emw = entity_metadata_wrapper('node', $harvest_source_node);

    $this->machine_name = $harvest_source_emw->field_dkan_harvest_machine_name->machine->value();

    if (!isset($harvest_source_emw->field_dkan_harvest_source_uri)) {
      throw new Exception('HarvestSource uri invalid!');
    }
    $this->uri = $harvest_source_emw->field_dkan_harvest_source_uri->value();

    if (!isset($harvest_source_emw->field_dkan_harveset_type)) {
      throw new Exception('HarvestSource type invalid!');
    }
    $type_machine_name = $harvest_source_emw->field_dkan_harveset_type->value();
    $this->type = HarvestSourceType::getSourceType($type_machine_name);


    $label = $harvest_source_emw->title->value();
    if (!isset($label) || !is_string($label)) {
      $label = $this->machine_name;
    }
    $this->label = $label;

    $optionals = array(
      'filters' => 'field_dkan_harvest_filters',
      'excludes' => 'field_dkan_harvest_excludes',
      'overrides' => 'field_dkan_harvest_overrides',
      'defaults' => 'field_dkan_harvest_defaults',
    );

    foreach ($optionals as $property => $field) {
      $property_value = array();
      $field_double = $harvest_source_emw->{$field}->value();
      foreach ($field_double as $key => $value) {
        $property_value[$value['first']] = explode(',', $value['second']);
      }
      $this->{$property} = $property_value;
    }
  }

  /**
   * Check if the source uri is a remote.
   */
  public function isRemote() {
    $remote = FALSE;
    $scheme = parse_url($this->uri, PHP_URL_SCHEME);
    if (($scheme == 'http' || $scheme == 'https')) {
      $remote = TRUE;
    }
    return $remote;
  }

/**
 * Get the cache directory for a specific source.
 *
 * @param Boolean $create: create the cache diretory if it does not exist.
 *
 * @return string
 * PHP filesteream location. Or FALSE if the cache directory does not exist.
 */
  public function getCacheDir($create_or_clear = FALSE) {
    $cache_dir_path = DKAN_HARVEST_CACHE_DIR . '/' . $this->machine_name;
    $options = FILE_MODIFY_PERMISSIONS;
    if ($create_or_clear) {
      file_unmanaged_delete_recursive($cache_dir_path);
      $options = FILE_MODIFY_PERMISSIONS | FILE_CREATE_DIRECTORY;
    }

    // Checks that the directory exists and is writable, create if
    // $create_or_clear is TRUE.
    return file_prepare_directory($cache_dir_path, $options) ?
      $cache_dir_path : FALSE;
  }

  /**
   * Generate a migration machine name from the source machine name suitable for in
   * MigrationBase::registerMigration().
   */
  public function getMigrationMachineName() {
    return self::getMigrationMachineNameFromName($this->machine_name);
  }

  /**
   * Generate a migration machine name from the source machine name suitable for in
   * MigrationBase::registerMigration().
   */
  public static function getMigrationMachineNameFromName($machine_name) {
    $migration_name = DKAN_HARVEST_MIGRATION_PREFIX . $machine_name;
    return self::getMachineNameFromName($migration_name);
  }

  /**
   * Generic function to convert a string to a Drupal machine name.
   *
   * @param String $human_name string to convert to machine name.
   *
   * TODO Not sure that this is needed anymore.
   */
  public static function getMachineNameFromName($human_name) {
    return preg_replace('@[^a-z0-9-]+@', '_', strtolower($human_name));
  }

  /**
   * Get a HarvestSource object from a harvest_source node.
   *
   * @param $harvest_source_node harvest_source node.
   *
   * @return HarvestSource object.
   *
   * @throws Exception if HarvestSource creation fail.
   */
  public static function getHarvestSourceFromNode(stdClass $harvest_source_node) {
    $harvest_source_node_emw = entity_metadata_wrapper('node', $harvest_source_node);
    return new HarvestSource($harvest_source_node_emw->field_dkan_harvest_machine_name->machine->value());
  }

  /**
   * Query the migrate_log table to get the last time the harvest source
   * migration run.
   *
   * @param string @machine_name Harvest Source machine name.
   *
   * @return Timestamp of the last Harvest Migration run. Or NULL if source not
   * found or not run yet.
   */
  public static function getMigrationTimestampFromMachineName($machine_name) {
   $migration_machine_name = HarvestSource::getMigrationMachineNameFromName($machine_name);

   // Get the last time (notice the MAX) the migration was run.
   $result = db_query("SELECT MAX(starttime) FROM {migrate_log} WHERE machine_name =
     :migration_machine_name ORDER BY starttime ASC limit 1;", array(':migration_machine_name' =>
     $migration_machine_name));

   $result_array = $result->fetchAssoc();

   if (!empty($result_array)) {
     $harvest_migrate_date = array_pop($result_array);
     // Migrate saves the timestamps with microseconds. So we drop the extra
     // info and get only the usual timestamp.
     $harvest_migrate_date = floor($harvest_migrate_date/1000);
     return $harvest_migrate_date;
    }
  }

  /**
   * Query the migrate_log table to get the time the harvest source
   * migration run.
   *
   * @param string @mlid Harvest Source Migration event ID.
   *
   * @return Timestamp of the last Harvest Migration run. Or NULL if source not
   * found or not run yet.
   */
  public static function getMigrationTimestampFromMlid($mlid) {
   // Get the time the migration was run by mlid.
   $result = db_query("SELECT MAX(starttime) FROM {migrate_log} WHERE mlid =
     :mlid ORDER BY starttime ASC limit 1;", array(':mlid' =>
     $mlid));

   $result_array = $result->fetchAssoc();

   if (!empty($result_array)) {
     $harvest_migrate_date = array_pop($result_array);
     // Migrate saves the timestamps with microseconds. So we drop the extra
     // info and get only the usual timestamp.
     $harvest_migrate_date = floor($harvest_migrate_date/1000);
     return $harvest_migrate_date;
    }
  }

  /**
   * Query the migration map table to get the last time the harvest source
   * migration run.
   *
   * @param string @machine_name Harvest Source machine name.
   *
   * @return number of datasets imported by the Harvest Source.
   */
  public static function getMigrationCountFromMachineName($machine_name) {
    // Construct the migrate map table name.
    $migration_machine_name = HarvestSource::getMigrationMachineNameFromName($machine_name);
    $migrate_map_table = 'migrate_map_' . $migration_machine_name;

    // In case the migration was not run and the table was not created yet.
    if (!db_table_exists($migrate_map_table)) {
      return 0;
    }

    // Only count for successful dataset imports.
   $result = db_query("SELECT sourceid1 FROM {" . $migrate_map_table . "} WHERE needs_update = :needs_update;",
     array(
       ':needs_update' => MigrateMap::STATUS_IMPORTED,
     )
   );

   return $result->rowCount();
  }

  /**
   *
   * @return HarvestCache object or FALSE in case of error.
   */
  public function cache($timestamp = false) {

    if (!$timestamp) {
      $timestamp = microtime();
    }

    // Make sure the cache directory is cleared.
    $this->getCacheDir(TRUE);

    try {
      // Get the cache callback for the source.
      $harvestCache = call_user_func(
        $this->type->cache_callback,
        $this,
        $timestamp
      );
    } catch (Exception $e) {
      drupal_set_message(t('Harvest demo cache failed ').$e->getMessage(), 'error', FALSE);
    }



    if (!isset($harvestCache)) {
      // Nothing to look for here.
      return FALSE;
    }

    return $harvestCache;
  }

/**
 * Run the migration for the sources.
 *
 * @param $options: Array extra options to pass to the migration.
 *
 * @return FALSE in case of a problem. Or a Migrate::RESULT_* status after
 * completion.
 */
  public function migrate($options = array()) {
    $migration = $this->getMigration();
    // Make sure the migration instantiation worked.
    if ($migration) {
      return $migration->processImport($options);
    }
    else {
      return FALSE;
    }
  }

  /**
   * Run a full harvest on this source.
   */
  public function harvest() {
    $this->cache();
    $this->migrate();
  }

  /**
   * Register and get the migration class for a harvest source.
   *
   * @return HarvestMigration object related to the source. Or FALSE if failed.
   */
  public function getMigration() {
    $harvest_migration_machine_name = $this->getMigrationMachineName();

    // Prepare $arguments to pass to the migration.
    $arguments = array(
      // Group all the harvest migration under the "dkan_harvest" group.
      // TODO better way to utilize the group feature in dkan_harvest (?).
      'group_name' => 'dkan_harvest',
      'dkan_harvest_source' => $this,
    );

    // Register the migration if it does not exist yet or update the arguments if
    // not.
    HarvestMigration::registerMigration(
      $this->type->migration_class,
      $harvest_migration_machine_name,
      $arguments
    );

    // This will make sure the Migration have the latest arguments.
    $migration = HarvestMigration::getInstance($harvest_migration_machine_name,
      $this->type->migration_class, $arguments);

    // Probably we should not trust migrations not subclassed from our
    // HarvestMigration. Altheugh this check should've have happened in the
    // HarvestType level.
    if (!isset($migration) || !is_a($migration, 'HarvestMigration')) {
      return FALSE;
    }

    return $migration;
  }

  /**
   * Remove any cached or imported content.
   *
   * @return HarvestSource::RESULT_* status code or FALSE if something is gone
   * wrong.
   */
  public function rollback($options = array()) {
    // Clear the cache dir.
    $this->getCacheDir(TRUE);

    // Rollback harvest migration.
    $migration = $this->getMigration();
    // Make sure the migration instantiation worked.
    if ($migration) {
      return $migration->processRollback($options);
    }

    // Something went south, return false.
    return FALSE;
  }

  /**
   * Deregister HarvestMigration migration associated with this source.
   */
  public function deregister() {
    HarvestMigration::deregisterMigration($this->getMigrationMachineName());
  }
}
