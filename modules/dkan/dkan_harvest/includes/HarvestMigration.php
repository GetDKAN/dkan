<?php

/**
 * @file
 * Base Harvest Migration classes.
 */

use dkanDataset\getRemoteFileInfo;

include_once drupal_get_path('module', 'dkan_dataset') . '/includes/getRemoteFileInfo.php';

// This used to exits in file.inc but was removed on Drupal 7 without a
// replacement.
define('FILE_STATUS_TEMPORARY', 0);

/**
 * Base Class for harvest migration.
 */
class HarvestMigration extends MigrateDKAN {

  /**
   * Store the harvest source we got from the Migration registration.
   *
   * @var object
   */
  protected $dkanHarvestSource;

  protected $sourceListOptions;

  protected $dkanHarvestMigrateSQLMapSourceKey;
  protected $dkanHarvestMigrateSQLMapDestinationKey;
  protected $dkanHarvestMigrateSQLMapConnectionKey;
  protected $dkanHarvestMigrateSQLMapSourceOptions;

  public $itemUrl;

  /**
   * {@inheritdoc}
   */
  public static function getInstance($machine_name, $class_name = NULL, array $arguments = array()) {
    // Reset the cache entry for this specific migration. to make sure the
    // arguments are always fresh.
    $migrations = &drupal_static(__FUNCTION__, array());
    if (isset($migrations[$machine_name])) {
      unset($migrations[$machine_name]);
    }
    return parent::getInstance($machine_name, $class_name, $arguments);
  }


  /**
   * Extra Harvest argument.
   *
   * @var bool
   */
  protected $dkanHarvestOptSkipHash = FALSE;

  /**
   * Disabled rules during import.
   *
   * @var array
   */
  protected $dkanHarvestDisabledRules = array();

  /**
   * Save migration results to be used on logs later.
   *
   * @var array
   */
  protected $migrationResults = array();

  /**
   * General initialization of a HarvestMigration object.
   */
  public function __construct(array $arguments) {
    if (!isset($arguments['dkan_harvest_source'])) {
      // Don't bother if we don't get the harvest source object. This will help
      // avoid null issues when looking at the migrations status .
      return;
    }

    parent::__construct($arguments);

    // We need to take over the logID generation happening on
    // parent::beginProcess() to accomodate the batch operation. Instead of
    // generating a new logID for each run we check if we have it already set.
    $this->logHistory = FALSE;

    $this->dkanHarvestSource = $arguments['dkan_harvest_source'];

    // SourceList options.
    $this->sourceListOptions = array(
      'track_changes' => TRUE,
    );

    // Keep MigrateMap construct arguments around to make overrides available.
    $this->dkanHarvestMigrateSQLMapSourceKey = array(
      'dkan_harvest_object_id' => array(
        'type' => 'varchar',
        'length' => 255,
        'not null' => TRUE,
        'description' => 'id',
      ),
    );
    $this->dkanHarvestMigrateSQLMapDestinationKey = MigrateDestinationNode::getKeySchema();
    $this->dkanHarvestMigrateSQLMapConnectionKey = 'default';
    $this->dkanHarvestMigrateSQLMapSourceOptions = array(
      'track_last_imported' => TRUE,
      // This is added to avoid migrate deleting old message logs. Need
      // https://www.drupal.org/files/migrate-append-map-messages-1989492-2.patch
      'append_messages' => TRUE,
    );

    $this->map = new HarvestMigrateSQLMap(
      $this->machineName,
      $this->dkanHarvestMigrateSQLMapSourceKey,
      $this->dkanHarvestMigrateSQLMapDestinationKey,
      $this->dkanHarvestMigrateSQLMapConnectionKey,
      $this->dkanHarvestMigrateSQLMapSourceOptions
    );

    $this->destination = new MigrateDestinationNode(
      'dataset',
      array('text_format' => 'html')
    );

    // Get the harvest source nid.
    $query = new EntityFieldQuery();

    $query->entityCondition('entity_type', 'node')
      ->entityCondition('bundle', 'harvest_source')
      ->propertyCondition('status', NODE_PUBLISHED)
      ->fieldCondition('field_dkan_harvest_machine_name', 'machine', $this->dkanHarvestSource->machineName, '=')
      // Run the query as user 1.
      ->addMetaData('account', user_load(1));

    $result = $query->execute();

    // This should be set when running the harvest with a harvest source node.
    // Add a check to support the phpunit tests that run from nodeless harvest
    // source object.
    if (isset($result['node'])) {
      $harvest_source_nids = array_keys($result['node']);
      $this->dkanHarvestSourceNid = array_pop($harvest_source_nids);
      $this->dkanHarvestSourceNode = node_load($this->dkanHarvestSourceNid);
    }
    else {
      $this->dkanHarvestSourceNid = FALSE;
      $this->dkanHarvestSourceNode = NULL;
      // Something is wrong.
      $message = t('Cannot look-up the harvest source Node ID');
      $this->reportMessage($message);
    }

    // Initialize results.
    $this->migrationResults = array(
      'process_type' => 0,
      'start_time' => 0,
      'end_time' => 0,
      'initial_highwater' => 0,
      'final_highwater' => 0,
      'datasets_on_source' => 0,
      'datasets_processed' => 0,
      'datasets_created' => 0,
      'datasets_updated' => 0,
      'datasets_failed' => 0,
      'datasets_orphaned' => 0,
    );

    // Add Field mappings.
    $this->setFieldMappings();

    // Define defite item URL
    $this->itemUrl = drupal_realpath($this->dkanHarvestSource->getCacheDir()) . '/:id';
  }

  /**
   * Returns migration results.
   */
  public function getResults() {
    return $this->migrationResults;
  }

  /**
   * Implementation of MigrationBase::beginProcess().
   */
  protected function beginProcess($newStatus) {
    // Begin process.
    parent::beginProcess($newStatus);

    if (!isset($this->logID)) {
      $this->logID = db_insert('migrate_log')
        ->fields(array(
          'machine_name' => $this->machineName,
          'process_type' => $newStatus,
          'starttime' => round(microtime(TRUE) * 1000),
          'initialHighwater' => $this->getHighwater(),
        ))
        ->execute();
    }

    // If an import if being run then update the results.
    if ($this->processing) {
      $this->migrationResults['process_type'] = $newStatus;
      $this->migrationResults['start_time'] = round(microtime(TRUE) * 1000);
      $this->migrationResults['initial_highwater'] = $this->getHighwater();
      $this->migrationResults['datasets_on_source'] = $this->sourceCount();
    }
  }

  /**
   * Logs a migration event.
   */
  public function logEvent($event_data) {
    // First add record on the 'migrate_log' table.
    try {
      if (!isset($this->logID)) {
        $this->logID = db_insert('migrate_log')
          ->fields(array(
            'machine_name' => $this->machineName,
            'process_type' => $event_data['process_type'],
            'starttime' => $event_data['start_time'],
            'endtime' => $event_data['end_time'],
            'initialHighwater' => $event_data['initial_highwater'],
            'finalHighwater' => $event_data['final_highwater'],
            'numprocessed' => $event_data['datasets_processed'],
          ))
          ->execute();
      }
    }
    catch (PDOException $e) {
      Migration::displayMessage(t('Could not log event for migration !name',
        array('!name' => $this->machineName)));
    }

    // Then add a record on the log table that's specific for this migration.
    // Use the same migration log ID that was used on the 'migrate_log' table.
    try {
      db_merge($this->map->getLogTable())
        ->key(array('mlid' => $this->logID))
        ->fields(array(
          'created' => $event_data['datasets_created'],
          'updated' => $event_data['datasets_updated'],
          'unchanged' => $event_data['datasets_unchanged'],
          'failed' => $event_data['datasets_failed'],
          'orphaned' => $event_data['datasets_orphaned'],
        ))
        ->execute();
    }
    catch (PDOException $e) {
      Migration::displayMessage(t('Could not log event for migration !name',
        array('!name' => $this->machineName)));
    }
  }

  /**
   * Implementation of MigrationBase::endProcess().
   */
  public function endProcess() {
    parent::endProcess();

    // Clear the message Queue.
    $this->saveQueuedMessages();

    // Complete the log record.
    if ($this->logID) {
      try {
        db_merge('migrate_log')
          ->key(array('mlid' => $this->logID))
          ->fields(array(
            'endtime' => round(microtime(TRUE) * 1000),
            'finalhighwater' => $this->getHighwater(),
            'numprocessed' => $this->total_processed,
          ))
          ->execute();
      }
      catch (PDOException $e) {
        Migration::displayMessage(t('Could not log operation on migration !name - possibly MigrationBase::beginProcess() was not called',
        array('!name' => $this->machineName)));
      }
    }
  }

  /**
   * Runs before an import starts.
   *
   * Used to disable any rules which could cause problems during
   * the import.
   */
  public function preImport() {
    parent::preImport();

    // Skip hash checking if required.
    if (isset($this->arguments['skiphash']) && $this->arguments['skiphash']) {
      $this->dkanHarvestOptSkipHash = TRUE;
      $this->map->getConnection()->update($this->map->getMapTable())
        ->fields(array('hash' => NULL))
        ->execute();
    }

    // Disable any rules passed in the arguments array
    // Most probably in the processImport() method.
    $disable_rules = variable_get('dkan_harvest_disable_rules', array());
    if (module_exists('rules') && !empty($disable_rules)) {
      // Make sure that we only alter the status of already enabled rules.
      $rules = db_select('rules_config', 'rc')
        ->fields('rc', array('name'))
        ->condition('name', variable_get('dkan_harvest_disable_rules', array()), 'IN')
        ->condition('active', 1)
        ->execute()
        ->fetchAll();

      // Store all the enabled rules set to be disabled.
      foreach ($rules as $rule) {
        $this->dkanHarvestDisabledRules[] = $rule->name;
      }

      // Check again to ensure we didn't just eliminate everything.
      if (!empty($this->dkanHarvestDisabledRules)) {
        db_update('rules_config')
          ->fields(array('active' => 0))
          ->condition('name', $this->dkanHarvestDisabledRules, 'IN')
          ->execute();
        rules_clear_cache(TRUE);

        $message = t('The following rules are disabled: @rules_list',
          array(
            '@rules_list' => implode(', ', $this->dkanHarvestDisabledRules),
          ));
        $this->reportMessage($message, MigrationBase::MESSAGE_NOTICE);
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * Add support for harvest migration specific checks and options.
   */
  public function processImport(array $options = array()) {
    if (!$this->dkanHarvestSource->getCacheDir()) {
      $message = t('Looks like the source is missing and a cache does not exist. No updates can be made at this time.');
      self::displayMessage($message, 'error');
      $this->reportMessage($message);
      return FALSE;
    }
    // Add any extra Harvest Migration Arguments.
    $this->arguments = array_merge($this->arguments, $options);
    return parent::processImport($options);
  }

  /**
   * {@inheritdoc}
   *
   * Implements MigrateBase::prepareRow().
   * Add common DkanHarvest prepareRow steps.
   */
  public function prepareRow($row) {
    parent::prepareRow($row);

    // Check the license field.
    // Convert URI values to license ID values from hook_license_subscribe(),
    // change the value to match the expected key value.
    // Clients will need to provide a custom hook_license_subscribe() function
    // if they are using different urls than dkan: (i.e. opendatacommons.org or
    // creativecommons.org rather than opendefinition.org)
    if (isset($row->license) && $row->license != '') {
      $licenses = dkan_dataset_content_types_license_subscribe();
      foreach ($licenses as $license_id => $license_data) {
        if (isset($license_data['uri']) && ($license_data['uri'] === $row->license)) {
          $row->license = $license_id;
        }
      }
    }

    // Check the accrualPeriodicity field.
    // If it's in the frequency map as a value, set it to the associated key.
    // If it's not there, either as a key or value, set it to null.
    if (isset($row->accrualPeriodicity) && $row->accrualPeriodicity != '') {
      $frequencies = dkan_dataset_content_types_iso_frecuency_map();
      if (!isset($frequencies[$row->accrualPeriodicity])) {
        $frequencies_by_label = array_flip($frequencies);
        if (isset($frequencies_by_label[$row->accrualPeriodicity])) {
          $row->accrualPeriodicity = $frequencies_by_label[$row->accrualPeriodicity];
        }
        else {
          $row->accrualPeriodicity = NULL;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * Implements MigrateBase::prepare().
   * Add common DkanHarvest prepare steps.
   */
  public function prepare($dataset_prepare, $row) {
    migrate_instrument_start('HarvestMigration->prepare');

    // XXX copied from pod migration.
    if (isset($row->modified) && $row->modified != '') {
      $dataset_prepare->migration_changed_date = $row->modified;
    }

    // This modules lives in NuCivic/dkan_dataset_metadata_source but seems to
    // me that only USDA is using it right now.
    if (module_exists('dkan_dataset_metadata_source')) {
      $this->prepareMetadataSource($dataset_prepare, $row);
    }

    migrate_instrument_stop('HarvestMigration->prepare');
  }

  /**
   * {@inheritdoc}
   */
  public function complete($dataset, $row) {
    migrate_instrument_start('HarvestMigration->complete');
    // Create resources after the dataset was created.
    // If the creation of the dataset fails resources should not
    // be created. Also, we need the dataset to be created first
    // so the sync of groups work properly.
    $this->createResources($dataset, $row);
    $this->createExtendedMetadata($dataset, $row);

    // Add group.
    $this->prepareGroup($dataset, $row);

    // Set topics in dataset to same as Harvest node.
    if ($this->dkanHarvestSourceNode) {
      $dataset->field_topic = $this->dkanHarvestSourceNode->field_dkan_harvest_topic;
    }

    // Save dataset.
    $dataset->revision = FALSE;
    node_save($dataset);

    // Publish dataset and resources if dkan_workflow is enabled.
    $this->moderatePublish($dataset);

    migrate_instrument_stop('HarvestMigration->complete');
  }

  /**
   * Publish dataset and resources.
   *
   * Force 'published' status on the dataset and its
   * associated resources if dkan_workflow is enabled.
   *
   * @param object $dataset
   *   Entity object.
   */
  private function moderatePublish($dataset) {
    // Publish dataset.
    if (module_exists('dkan_workflow')) {
      workbench_moderation_moderate($dataset, 'published');

      // Publish resources.
      if (!empty($dataset->field_resources[LANGUAGE_NONE])) {
        foreach ($dataset->field_resources[LANGUAGE_NONE] as $data) {
          $resource = node_load($data['target_id']);
          workbench_moderation_moderate($resource, 'published');
        }
      }
    }
  }

  /**
   * Create resources in a dataset.
   *
   * If we are adding a new dataset, create and add the nids to the
   * field_resources entity reference field.
   * If we are updating an existing dataset. Remove the existing resources and
   * replace them.
   *
   * @param object $dataset
   *   Entity where resources are going to be added.
   * @param object $row
   *   Migration row.
   */
  private function createResources(&$dataset, stdClass &$row) {
    migrate_instrument_start('HarvestMigration->createResources');
    // Delete all resources from dataset if any.
    // All resources will get imported again.
    $field_resources_ids = array();
    $dataset_old_emw = entity_metadata_wrapper('node', $dataset->nid);

    // Sometimes the field_resources would not be set before the node is saved.
    if (isset($dataset_old_emw->field_resources)) {
      foreach ($dataset_old_emw->field_resources->getIterator() as $delta => $resource_emw) {
        $field_resources_ids[] = $resource_emw->getIdentifier();
      }
    }

    if (!empty($field_resources_ids)) {
      entity_delete_multiple('node', $field_resources_ids);
    }

    if (isset($row->resources) && is_array($row->resources) && !empty($row->resources)) {
      // Create all resources and assign them to the dataset.
      $field_resources_value = array();
      foreach ($row->resources as $resource_row) {

        try {
          $resource_node = $this->createResourceNode($resource_row);
          $field_resources_value[] = $resource_node->nid;
        }
        catch (EntityMetadataWrapperException $emwException) {
          $message = t(
            'Cannot create the resource. @exception_message',
            array(
              '@exception_message' => $emwException->getMessage(),
            ));
          $this->reportMessage($message);
        }
      }
      if (!empty($field_resources_value)) {
        foreach ($field_resources_value as $key => $resource_nid) {
          $dataset->field_resources[LANGUAGE_NONE][$key] = array(
            'target_id' => $resource_nid,
          );
        }
      }
    }
    migrate_instrument_stop('HarvestMigration->createResources');
  }

  /**
   * Create extended metadata paragraphs in datasets. This is used for custom
   * fields and therefore has no specific logic here in the base class.
   *
   * @param object $dataset
   *   Entity where resources are going to be added.
   * @param object $row
   *   Migration row.
   */
  protected function createExtendedMetadata(&$dataset, stdClass &$row) {
  }

  /**
   * Get the nid of the group associated with the dataset.
   *
   * Create the group if it doesn't exist.
   * Add group nid to the row data.
   *
   * @param object $dataset
   *   Entity where groups are going to be added.
   * @param object $row
   *   Migration row.
   */
  private function prepareGroup(&$dataset, stdClass &$row) {

    // If there is a group check if it already exists and create it if not.
    if (isset($row->group)) {
      $group_data = $row->group;
      $gid = $this->getGroupIdByName($group_data->name);
      if (!$gid) {
        $group_node = $this->createGroupNode($group_data);
        if ($group_node) {
          $gid = $group_node->nid;
        }
      }

      if ($gid) {
        $dataset->og_group_ref[LANGUAGE_NONE][] = array(
          'target_id' => $gid,
        );
      }
    }
  }

  /**
   * Get the id of a group based on the title.
   *
   * @param string $title
   *   Title of the group that is being searched.
   *
   * @return mixed
   *   The group id or false if the group was not found.
   */
  public function getGroupIdByName($title) {
    $result = db_query("SELECT n.nid FROM {node} n WHERE n.title = :title AND n.type = 'group'", array(":title" => $title));
    return $result->fetchField();
  }

  /**
   * Helper function.
   *
   * Prepare an Object usable for HarvestMigration::prepareMetadataSource().
   *
   * @return array
   *   The status and the error message in case of faileur or the object suitable
   *   for HarvestMigration::prepareMetadataSource() or FALSE.
   */
  public static function prepareMetadataSourceHelper($file_uri,
  $title = NULL,
  $metadata_schema = NULL,
  $metadata_view = NULL,
  $uid = 1) {

    $metadata_source = new stdClass();

    $metadata_source->title = isset($title) ? $title : 'Metadata for ' . $row->dkan_harvest_object_id;

    // TODO move this to the dkan_dataset_metadata_source module as a variable.
    $metadata_source_dir = 'public://metadata_source/';
    if (file_prepare_directory($metadata_source_dir, FILE_MODIFY_PERMISSIONS | FILE_CREATE_DIRECTORY)) {
      // Check if the actual file exists.
      if (!file_exists($file_uri)) {
        $message = t('@message_prefix. Metadata Source file @file_path not found.',
          array(
            '@message_prefix' => 'Adding Metada Source failed',
            '@file_path' => $file_uri,
          ));
        return array(FALSE, $message);
      }
      $metadata_source->file_uri = $file_uri;
    }

    // Metadata schema.
    $metadata_source->metadata_schema = $metadata_schema;

    // Title.
    $metadata_source->title = isset($title) ? $title : 'Metadata for ' . $row->dkan_harvest_object_id;

    // Metadata View.
    $metadata_source->metadata_view = $metadata_view;

    // UID.
    $metadata_source->uid = $uid;

    return array(TRUE, $metadata_source);
  }

  /**
   * Create and attach the metadata-source node related to the dataset imported.
   *
   * @param object $dataset_prepare
   *   Dataset entity from the Migration::prepare() methode.
   * @param object $row
   *   Row object from the Migration::prepare() methode.
   *
   * @return mixed
   *   The created metadata source id.
   *         Or FALSE if not created.
   */
  private function prepareMetadataSource(&$dataset_prepare, stdClass &$row) {
    // Delete exsting metadata sources if updating an existing dataset.
    if (isset($row->migrate_map_destid1)) {
      try {
        $dataset_old_emw = entity_metadata_wrapper('node', $row->migrate_map_destid1);
        // Check if the old dataset have a metadata source attached.
        $metadata_source_old_emw = $dataset_old_emw->field_metadata_sources->value();
        if (isset($metadata_source_old_emw)) {
          entity_delete('node', $metadata_source_old_emw->nid);
        }
      }
      catch (EntityMetadataWrapperException $emwException) {
        // Cannot get to the old datasets although the migration states it
        // exists. This should never happen but lets log this to help the next
        // developer debug this problem.
        $message = t(
          'Cannot clean old metadata source. @exception_message',
          array(
            '@exception_message' => $emwException->getMessage(),
          ));
        $this->reportMessage($message);
      }
    }

    if (!isset($row->metadata_source) || !$row->metadata_source) {
      // Metadata source not provided.
      return FALSE;
    }

    // TODO export the config bit out.
    // Create a metadata node that contains the source for imported USDA ISO
    // dataset.
    $metadata_source = entity_create('node', array(
      'type' => 'metadata',
      'uid' => $row->metadata_source,
    ));

    // Use the admin user as the node author.
    $metadata_source_emw = entity_metadata_wrapper('node', $metadata_source);

    $metadata_source_emw->title = $row->metadata_source->title;

    if (isset($row->migrate_map_destid1)) {
      $metadata_source_emw->field_dataset_metadata_ref->set[] = $row->migrate_map_destid1;
    }

    if (isset($row->metadata_source->metadata_schema)) {
      $vocab = 'extended_metadata_schema';
      $term = $this->createTax($row->metadata_source->metadata_schema, $vocab);
      if (!$term) {
        // Something is wrong. Report.
        $message = t(
          "Cannot get taxonomy @taxonomy (@vocabulary vocabulary).",
          array(
            '@taxonomy' => $row->metadata_source->metadata_schema,
            '@vocabulary' => $vocab,
          )
        );
        $this->reportMessage($message);
      }
      else {
        // Looks fine.
        $metadata_source_emw->field_metadata_schema->set($term->tid);
      }
    }

    // File.
    $metadata_source_content = file_get_contents($row->metadata_source->file_uri);
    // TODO move this to the dkan_dataset_metadata_source module as a variable.
    $metadata_source_dir = 'public://metadata_source/';
    $metadata_source_file_destination_uri = $metadata_source_dir . basename($row->metadata_source->file_uri);
    $file = file_save_data($metadata_source_content, $metadata_source_file_destination_uri,
      FILE_EXISTS_REPLACE);

    if (!$file) {
      $message = t('Cannot create the file for the Metadata Source.');
      $this->reportMessage($message);
      return FALSE;
    }
    else {
      $metadata_source_emw->field_metadata_file->file->set($file);
    }

    try {
      $metadata_source_emw->save();
    }
    catch (EntityMetadataWrapperException $emwException) {
      // Saving the metadata source failed. skip.
      $message = t(
        '@message_prefix. @exception_message',
        array(
          '@message_prefix' => 'Adding Metada Source failed',
          '@exception_message' => $emwException->getMessage(),
        ));
      $this->reportMessage($message);
    }

    // Attache the metadata source to the dataset.
    $dataset_prepare->field_metadata_sources[LANGUAGE_NONE][] = array(
      'target_id' => $metadata_source_emw->getIdentifier(),
    );

    return $metadata_source_emw->getIdentifier();
  }

  /**
   * Implements Migrate::postImport().
   */
  public function postImport() {
    parent::postImport();

    migrate_instrument_start('HarvestMigration->postImport');

    // Check if sourceCount is 0.
    // Show and add an error to the message table.
    if ($this->sourceCount() == 0) {
      $message = t('Items to import is 0. Looks like source is missing. No updates can be made at this time.');
      self::displayMessage($message, 'error');
      $this->reportMessage($message);
    }

    // Zombie entries are datasets that failed to import during a previous
    // harvest migration and does not have any destination node and during the
    // current harvest they are missing from the source as well.
    // Look for those zombie dataset and clean them.
    $zombies = $this->map->lookupMapTable(HarvestMigrateSQLMap::STATUS_FAILED,
      $this->getIdList(), "NOT IN", NULL, NULL);

    if (!empty($zombies)) {
      $zombies_sourceids = array();
      foreach ($zombies as $zombie) {
        array_push($zombies_sourceids, $zombie->sourceid1);
      }
      $this->displayMessage(t('Deleting previously failed imports that does not exist in the harvest source anymore.'));
      // Delete the map entry of the zombie dataset. Keep the message for
      // historical log.
      $this->getMap()->deleteBulkFromMap($zombies_sourceids);
    }

    // We need the postImportRestoredSource() call to be before the
    // postImportMissingSource() call.
    $this->postImportRestoredSource();
    $this->postImportMissingSource();

    // Restore any rule disabled in the preImport call. This is restored last
    // in the postImport call to make sure any processing done is done while
    // the rules are disabled as well.
    if (module_exists('rules') && !empty($this->dkanHarvestDisabledRules)) {
      db_update('rules_config')
        ->fields(array('active' => 1))
        ->condition('name', $this->dkanHarvestDisabledRules, 'IN')
        ->execute();
      rules_clear_cache(TRUE);

      $message = t('The following rules are re-enabled: @rules_list',
        array(
          '@rules_list' => implode(', ', $this->dkanHarvestDisabledRules),
        ));
      $this->reportMessage($message, MigrationBase::MESSAGE_NOTICE);
    }

    // If an import if being run then update the results.
    if ($this->processing) {
      $this->migrationResults['end_time'] = round(microtime(TRUE) * 1000);
      $this->migrationResults['final_highwater'] = $this->getHighwater();
      $this->migrationResults['datasets_processed'] = $this->total_processed;
      $this->migrationResults['datasets_created'] = $this->destination->getCreated();
      $this->migrationResults['datasets_updated'] = $this->destination->getUpdated();
      $this->migrationResults['datasets_failed'] = $this->errorCount();
      $this->migrationResults['datasets_orphaned'] = $this->map->orphanedCount();
    }

    migrate_instrument_stop('HarvestMigration->postImport');
  }

  /**
   * Process restored source content.
   *
   * Process content that was marked as missing source in a previous harvest
   * migrate operation but had the source restored.
   */
  private function postImportRestoredSource() {
    migrate_instrument_start('HarvestMigration->postImportRestoredSource');
    // Remove HarvestMigrateSQLMap::STATUS_IGNORED_NO_SOURCE flag and publish
    // the datasets (and their related content) that had their source disappear
    // in a previous harvest migrate.
    // Get rows marked with HarvestMigrateSQLMap::STATUS_IGNORED_NO_SOURCE
    // and part of the imported datasets imported.
    $id_list = $this->getIdList();
    if (is_array($id_list) && !empty($id_list)) {
      $query = $this->map->getConnection()->select($this->map->getMapTable(), 'map')
        ->fields('map')
        ->condition("needs_update", HarvestMigrateSQLMap::STATUS_IGNORED_NO_SOURCE)
        // This will throw an exception if $this->idListImported is empty. We
        // are doing that check before entring this code but if you are
        // refactoring this code make sure to add the relevent checks.
        ->condition('sourceid1', $this->getIdList(), 'IN');
      $result = $query->execute();

      $rowRestoredSourceList = $result->fetchAllAssoc('destid1');
      if (!empty($rowRestoredSourceList)) {
        $message = t(
          '@count item(s) datasets that were previously unpublished are restored by the source and will be republished again.',
          array('@count' => count($rowRestoredSourceList))
        );
        self::displayMessage($message, 'warning');

        foreach (array_keys($rowRestoredSourceList) as $dataset_nid) {
          // TODO cach exceptions.
          $dataset_emw = entity_metadata_wrapper('node', $dataset_nid);
          $related_count = 0;

          // Publish attached resources.
          if (isset($dataset_emw->field_resources)) {
            foreach ($dataset_emw->field_resources->getIterator() as $delta => $resource_emw) {
              $resource_emw->field_orphan->set(0);
              $resource_emw->status->set(1);
              $resource_emw->save();
              $related_count++;
            }
          }

          // Publish attached metadata source.
          if (module_exists('dkan_dataset_metadata_source')) {
            if (isset($dataset_emw->field_metadata_sources) && !is_null($dataset_emw->field_metadata_sources->value())) {
              $dataset_emw->field_metadata_sources->status->set(1);
            }
          }

          // Publish the dataset.
          $dataset_emw->field_orphan->set(0);
          $dataset_emw->status->set(1);
          $dataset_emw->save();

          // Update the map table.
          $results = $this->map->getConnection()->update($this->map->getMapTable())
            ->fields(array('needs_update' => MigrateMap::STATUS_IMPORTED))
            ->condition('destid1', $dataset_nid)
            ->execute();

          $message = t(
            'Dataset "@dataset_title"[@nid] and @related_count related content (Resources, ..) published.',
            array(
              '@nid' => $dataset_emw->getIdentifier(),
              '@dataset_title' => $dataset_emw->title->value(),
              '@related_count' => $related_count,
            )
          );
          self::displayMessage($message, 'warning');
        }
      }
    }
    migrate_instrument_stop('HarvestMigration->postImportRestoredSource');
  }

  /**
   * Process missing source content.
   *
   * Process content that was imported in previous harvest migrate operation
   * but were not processed in this run because the source is not available.
   */
  private function postImportMissingSource() {
    migrate_instrument_start('HarvestMigration->postImportMissingSource');
    // Get rows NOT marked with HarvestMigrateSQLMap::STATUS_IGNORED_NO_SOURCE
    // and NOT part of the imported datasets imported.
    $query = $this->map->getConnection()->select($this->map->getMapTable(), 'map')
      ->fields('map')
      ->condition("needs_update", HarvestMigrateSQLMap::STATUS_IGNORED_NO_SOURCE, '<>');
    $id_list = $this->getIdList();
    if (is_array($this->getIdList()) && !empty($id_list)) {
      foreach ($this->map->getSourceKeyMap() as $key_name) {
        $query = $query->condition("map.$key_name", $this->getIdList(), 'NOT IN');
      }
    }
    $result = $query->execute();

    $rowNoSourceList = $result->fetchAllAssoc('destid1');
    if (!empty($rowNoSourceList)) {
      $message = t(
        '@count item(s) that were added by this source does not exist anymore and will be unpublished.',
        array('@count' => count($rowNoSourceList))
      );
      self::displayMessage($message, 'warning');
      foreach (array_keys($rowNoSourceList) as $nid) {
        $dataset_emw = NULL;
        try {
          $dataset_emw = entity_metadata_wrapper('node', $nid);
        }
        catch (EntityMetadataWrapperException $exception) {
          $this->reportMessage($exception->getMessage());
          // Skip this nid.
          continue;
        }

        $related_count = 0;

        // Unpublish attached resources.
        if (isset($dataset_emw->field_resources)) {
          foreach ($dataset_emw->field_resources->getIterator() as $delta => $resource_emw) {
            $resource_emw->field_orphan->set(1);
            $resource_emw->status->set(0);
            $resource_emw->save();
            $related_count++;
          }
        }

        // Unpublish attached metadata source.
        if (module_exists('dkan_dataset_metadata_source')) {
          if (isset($dataset_emw->field_metadata_sources) && !is_null($dataset_emw->field_metadata_sources->value())) {
            $dataset_emw->field_metadata_sources->status->set(0);
          }
        }
        $dataset_emw->field_orphan->set(1);
        // Unpublish the dataset.
        $dataset_emw->status->set(0);
        $dataset_emw->save();

        // Update the map table.
        $results = $this->map->getConnection()->update($this->map->getMapTable())
          ->fields(array('needs_update' => HarvestMigrateSQLMap::STATUS_IGNORED_NO_SOURCE))
          ->condition('destid1', $nid)
          ->execute();

        $message = t(
          'Dataset "@dataset_title"[@nid] and @related_count related content (Resources, ..) unpublished.',
          array(
            '@nid' => $dataset_emw->getIdentifier(),
            '@dataset_title' => $dataset_emw->title->value(),
            '@related_count' => $related_count,
          )
        );
        self::displayMessage($message, 'warning');
      }
    }
    migrate_instrument_stop('HarvestMigration->postImportMissingSource');
  }

  /**
   * Implements hook.
   *
   * Pre rollback callback. Deletes all the content related to the imported
   * datasets before deleting them.
   *
   * @param array $dataset_nids
   *   An array of nids of datasets imported.
   */
  public function prepareRollback(array $dataset_nids) {
    foreach ($dataset_nids as $dataset_id) {
      $dataset_emw = entity_metadata_wrapper('node', $dataset_id);
      $delete_nids = array();

      // Get resources ids.
      foreach ($dataset_emw->field_resources as $delta => $resource_emw) {
        $delete_nids[] = $resource_emw->getIdentifier();
      }

      // Get metadata source ids if enabled.
      if (module_exists('dkan_dataset_metadata_source')) {
        if (isset($dataset_emw->field_metadata_sources)) {
          $delete_nids[] = $dataset_emw->field_metadata_sources->getIdentifier();
        }
      }

      // Delete all the things related to this dataset.
      entity_delete_multiple('node', $delete_nids);
    }
  }

  /**
   * Default dataset field mappings.
   */
  public function setFieldMappings() {

    // Set the default user as root. This will help avoid some of the issues
    // with anonymous nodes.
    $this->addFieldMapping('uid', 'uid')
      ->defaultValue(1);
    $this->addFieldMapping('revision_uid', 'revision_uid')
      ->defaultValue(1);

    // Set the default dataset status as published.
    $this->addFieldMapping('status', 'status')
      ->defaultValue(1);

    // Create a new revision if we create or update the dataset.
    $this->addFieldMapping('revision', 'revision')
      ->defaultValue(1);

    // Set the uuid indentifier field to the dkan_harvest_object_id provided by
    // the MigrateSourceList. This should match the dataset file name in the
    // cache directory.
    $this->addFieldMapping('uuid', 'dkan_harvest_object_id');

    // Set the harvest source entity reference in the dataset node.
    $this->addFieldMapping('field_harvest_source_ref', 'harvest_source_ref')
      ->defaultValue(array($this->dkanHarvestSourceNid));

    // Mark the dataset as published if dkan_workflow is there.
    if (module_exists('dkan_workflow')) {
      $this->addFieldMapping('workbench_moderation_state_new', 'workbench_moderation_state_new')
        ->defaultValue('published');
    }

    // Default Dkan Dataset fields.
    // Node property.
    $this->addFieldMapping('field_harvest_source_issued', 'created');
    $this->addFieldMapping('field_harvest_source_modified', 'changed');
    // Primary.
    $this->addFieldMapping('title', 'title');
    $this->addFieldMapping('body', 'description');
    $this->addFieldMapping('field_tags', 'tags');
    $this->addFieldMapping('field_tags:create_term')
      ->defaultValue(TRUE);
    $this->addFieldMapping('field_license', 'license')
      ->defaultValue('notspecified');

    // Dataset Information.
    $this->addFieldMapping('field_author', 'author');
    $this->addFieldMapping('field_spatial', 'spatial');
    $this->addFieldMapping('field_spatial_geographical_cover', 'spatial_geographical_cover');
    $this->addFieldMapping('field_frequency', 'frequency');
    $this->addFieldMapping('field_temporal_coverage', 'temporal_coverage_from');
    $this->addFieldMapping('field_temporal_coverage:to', 'temporal_coverage_to');
    $this->addFieldMapping('field_contact_name', 'contact_name');
    $this->addFieldMapping('field_contact_email', 'contact_email');
    // $this->addFieldMapping('og_group_ref', 'group_ids');.
    $this->addFieldMapping('field_related_content', 'related_content');
    $this->addFieldMapping('field_related_content:title', 'related_content:title');
    $this->addFieldMapping('field_related_content:url', 'related_content:url');

    // Optional fields.
    // dkan_dataset_metadata_source.
    if (module_exists('dkan_dataset_metadata_source')) {
      $this->addFieldMapping('field_metadata_sources', 'metadata_sources');
    }

    // open_data_federal_extras.
    if (module_exists('open_data_federal_extras')) {
      // Project Open Data Fields.
      $this->addFieldMapping('field_odfe_bureau_code', 'odfe_bureau_code');
      $this->addFieldMapping('field_odfe_program_code', 'odfe_program_code');
      $this->addFieldMapping('field_odfe_landing_page', 'odfe_landing_page');
      $this->addFieldMapping('field_odfe_rights', 'odfe_rights');
      $this->addFieldMapping('field_odfe_conforms_to', 'odfe_conforms_to');
      $this->addFieldMapping('field_odfe_data_quality', 'odfe_data_quality');
      $this->addFieldMapping('field_odfe_is_part_of', 'odfe_is_part_of');
      $this->addFieldMapping('field_odfe_language', 'odfe_language');
      $this->addFieldMapping('field_odfe_investment_uii', 'odfe_investment_uii');
      $this->addFieldMapping('field_odfe_system_of_records', 'odfe_system_of_records');
      $this->addFieldMapping('field_pod_theme', 'odfe_category');
    }
  }

  /**
   * Create a taxonomy term for a vocabulary.
   *
   * This is s straight copy of the MigrateDkan::createTax()
   * method but adapted to latest taxonomy_get_term_by_name()
   * function arguments.
   *
   * TODO Probably get this to the MigrateDkan class.
   *
   * @param string $name
   *   Taxonomy term name.
   * @param string $vocab_name
   *   Taxonomy vocabulary name.
   * @param string $vid
   *   The ID of the vocabulary.
   *
   * @return mixed
   *   An object term. Or null if failed.
   */
  public function createTax($name, $vocab_name, $vid = NULL) {
    // Make sure that the vocabulary exists before adding terms.
    $vocab = taxonomy_vocabulary_machine_name_load($vocab_name);
    if (!$vocab) {
      return NULL;
    }

    if ($term = taxonomy_get_term_by_name($name, $vocab_name)) {
      $term = array_pop($term);
      return $term;
    }
    else {
      $term_new = new stdClass();
      $term_new->name = $name;
      // Have the vid set for new term is required.
      $term_new->vid = $vocab->vid;
      $term_new->vocabulary_machine_name = $vocab_name;
      if (taxonomy_term_save($term_new) == SAVED_NEW) {
        // Term object is passed by reference and updated with the appropriate
        // properties.
        return $term_new;
      }

      // If we made it til here then something did not work out as expected.
      return NULL;
    }
  }

  /**
   * Helper function.
   *
   * Prepare an Object usable for
   * HarvestMigration::createResourceNode().
   *
   * @param string $url
   *   Resource remote url.
   * @param string $format
   *   Resource format. Used to get the correct taxonomy term.
   * @param string $title
   *   Optional resource title. If NULL use the url instead.
   * @param int $created
   *   Option creation time.
   * @param string $description
   *   Option body content.
   *
   * @return array
   *   array with the status and the error if failed or the the object prepared
   *   for HarvestMigration::createResourceNode().
   *         Or FALSE.
   */
  public static function prepareResourceHelper($url, $format = NULL, $title = NULL, $created = NULL, $description = NULL) {
    $resource = new stdClass();

    // Set 'http://' as default scheme if missing.
    $url_parse_scheme = parse_url($url, PHP_URL_SCHEME);
    if (!$url_parse_scheme) {
      $url = "http://" . $url;
      $url_parse_scheme = "http://";
    }

    // Validate the url.
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
      $message = t('Resource remote url (@url) not valid!',
        array(
          '@url' => $url,
        ));
      return array(FALSE, $message);
    }
    else {
      $resource->url = $url;
    }

    // Detect the url type. If file set field_link_remote_file. If not set
    // field_link_api.
    //
    // Default format for field_link_remote_file is 'data'.
    // Default format for field_link_api is 'html'.
    //
    // To Detect the field_link_remote_file format:
    // * check the file extension.
    // * If not available. Check the file mime-type.
    // url_type. Safe default to url.
    $resource->url_type = 'api';
    $format_detected = 'html';

    // Logic to determin if the URL is switable for field_link_remote_file
    // field (ie without redirects) and get a canonical format This only works
    // for http requests (right now). So for ftp and other URLs we just skip.
    if (preg_match("@^https?@i", $url_parse_scheme)) {
      $fake_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1';
      // remote_stream_wrapper does not play nice with files link behind
      // redirects. Try to get the bottom of the redircet chain if any and use
      // the last URL.
      $followRedirect = TRUE;
      $remoteFileInfo = new GetRemoteFileInfo($resource->url, $fake_agent, $followRedirect);

      $html_extensions = array('html', 'shtml', 'htm', 'rhtml', 'phtml', 'pht',
        'php', 'phps', 'php3', 'php3p', 'php4',
      );
      // The parsed remote file should be valid and should not look like a web
      // page.
      if (!is_null($remoteFileInfo->getType())
        && !in_array($remoteFileInfo->getExtension(), $html_extensions)
        && $remoteFileInfo->getType() != 'text/html') {
        // Check if this file extension is allowed for field_link_remote_file.
        // Reduce everything to lowercase to support files with uppercase
        // extensions.
        $file_extensions = array_map('strtolower', explode(' ', dkan_allowed_extensions()));

        // Reject if the extension is not allowed.
        if (!in_array($remoteFileInfo->getExtension(), $file_extensions)) {
          $message = t('Resource remote url (@url) extension (@extension) not allowed',
            array(
              '@url' => $resource->url,
              '@extension' => $remoteFileInfo->getExtension(),
            ));
          return array(FALSE, $message);
        }

        // If the URL is determined to be a remote file,
        // check that the URL is no more than 255 characters.
        // More than 255 will give the 'Data too long for column' error.
        if (strlen($resource->url) > 255 &&
          strlen($remoteFileInfo->getEffectiveUrl()) < 256) {
          // Switch to the effective url if the json url is too long.
          $resource->url = $remoteFileInfo->getEffectiveUrl();
        }
        $resource->url_type = 'file';
        $format_detected = $remoteFileInfo->getExtension();
      }
    }

    // Format
    // Default to detected format if not set.
    $resource->format = isset($format) ? strtolower($format) : $format_detected;

    // Title.
    $resource->title = isset($title) ? $title : $resource->format;

    // Created.
    $resource->created = isset($created) ? $created : time();

    // Description.
    $resource->description = isset($description) ? $description : '';

    return array(TRUE, $resource);
  }

  /**
   * Create a dkan resource node from the values provided.
   *
   * @param object $res
   *   Values for the resources node to be created.
   *        Formated with the self::prepareResourceHelper() method.
   *
   * @return object
   *   Resource node object.
   */
  public function createResourceNode(stdClass $res) {
    migrate_instrument_start('HarvestMigration->createResourceNode');
    // Creates a new resource for every linked file.
    // Linked files contain title, format, and accessURL.
    $values = array(
      'type' => 'resource',
      'changed' => $res->created,
      'is_new' => TRUE,
      "language" => LANGUAGE_NONE,
      "uid" => 1,
      "status" => NODE_PUBLISHED,
    );

    $resource_node = entity_create('node', $values);
    $resource_emw = entity_metadata_wrapper('node', $resource_node);

    $resource_emw->title = $res->title;
    $resource_emw->body->format->set('html');
    $resource_emw->body->value->set($res->description);

    if (isset($res->format)) {
      $vocab = 'format';
      $term = $this->createTax($res->format, $vocab);

      if (is_null($term)) {
        // Something went left. Report.
        $message = t(
          "Cannot get taxonomy @taxonomy (@vocabulary vocabulary).",
          array(
            '@taxonomy' => $res->format,
            '@vocabulary' => $vocab,
          )
        );
        $this->reportMessage($message);
      }
      else {
        $resource_emw->field_format = $term->tid;
      }
    }

    if ($res->url_type == 'file') {
      // Manage file type resources.
      $file = remote_stream_wrapper_file_load_by_uri($res->url);
      if (!$file) {
        $file = remote_stream_wrapper_file_create_by_uri($res->url);
        $file->status = FILE_STATUS_TEMPORARY;
      }
      file_save($file);
      $resource_emw->field_link_remote_file->set(array(
        'fid' => $file->fid,
        'display' => 1,
      ));
    }
    else {
      // Manage API type resources.
      $resource_emw->field_link_api->url->set($res->url);
    }

    $resource_emw->save();
    migrate_instrument_stop('HarvestMigration->createResourceNode');
    return $resource_emw->raw();
  }

  /**
   * {@inheritdoc}
   *
   * Change the default to not warn as overriding field mapping is common
   * practice for the HarvestMigration subclasses.
   */
  public function addFieldMapping(
      $destination_field,
      $source_field = NULL,
      $warn_on_override = FALSE
    ) {
    return parent::addFieldMapping($destination_field, $source_field, $warn_on_override);
  }

  /**
   * Add support for the $mlid column.
   */
  public function saveMessage($message, $level = MigrationBase::MESSAGE_ERROR, $mlid = FALSE) {
    $this->map->saveMessage($this->currentSourceKey(), $message, $level, $mlid);
  }

  /**
   *
   */
  public function reportMessage($message, $level = MigrationBase::MESSAGE_ERROR) {
    // Add to Queue if logID is not available.
    if (!empty($this->logID)) {
      $this->saveMessage($message, $level, $this->logID);
    }
    else {
      $this->queueMessage($message, $level);
    }
  }

  /**
   * {@inheritdoc}
   *
   * Override to make sure to use the mlid for all the log messages being saved
   * from the queue or display in case we don't have the Migration Log ID
   * (mlid).
   */
  public function saveQueuedMessages() {
    // If we have a logID, save message to DB, if not display to user.
    foreach ($this->queuedMessages as $queued_message) {
      if (!empty($this->logID)) {
        $this->saveMessage($queued_message['message'], $queued_message['level'], $this->logID);
      }
      else {
        self::displayMessage(
          $queued_message['message'],
        $this->getMessageLevelName($queued_message['level'])
        );
      }
    }
    $this->queuedMessages = array();
  }

  /**
   * Create a dkan group node from the values provided.
   *
   * @param object $group
   *   Values for the group node to be created.
   *
   * @return object
   *   Group node object.
   */
  public function createGroupNode(stdClass $group) {

    $values = array(
      "type" => 'group',
      "is_new" => TRUE,
      "language" => LANGUAGE_NONE,
      "uid" => 1,
      "status" => NODE_PUBLISHED,
      "title" => $group->name,
    );

    $group_node = entity_create('node', $values);
    node_save($group_node);

    return $group_node;
  }

  /**
   * Getter for harvest source. This is mainly used for unittests.
   */
  public function getHarvestSource() {
    return $this->dkanHarvestSource;
  }

  /**
   * Getter for the $logID variable.
   *
   * Mainly used for the HarvestMigrateSQLMap class.
   */
  public function getLogId() {
    return $this->logID;
  }

  /**
   * Setter for the $logID variable.
   *
   * Update migration log ID. Useful to log tracking when triggered from with
   * external process like batch APIs.
   */
  public function setLogId($logID) {
    $this->logID = $logID;
  }

  /**
   * Return list of source items ids.
   */
  public function getIdList() {
    return $this->source->getIdList();
  }

}
