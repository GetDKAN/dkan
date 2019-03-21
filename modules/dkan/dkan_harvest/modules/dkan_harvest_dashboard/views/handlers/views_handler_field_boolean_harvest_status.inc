<?php

/**
 * Handler class implementation for Dkan Harvest Migration date field.
 */
class views_handler_field_boolean_harvest_status extends views_handler_field {

  const STATUS_UNKNOWN = -1;
  const STATUS_FAILED = 0;
  const STATUS_FINISHED_NO_ERRORS = 1;
  const STATUS_FINISHED_WITH_ERRORS = 2;
  const STATUS_IN_PROGRESS = 3;
  const STATUS_NEW = 4;

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query since we are not using a
    // direct database query.
  }

  /**
   * {@inheritdoc}
   */
  public function click_sort($order) {
    // Usually this will add a condition to the fields sql query. Since we
    // are dealing with a psudo field with no direct database query we just
    // store this and we process the sorting in the self::post_execute()
    // methode.
    $this->order = $order;
  }

  /**
   * {@inheritdoc}
   */
  public function option_definition() {
    $options = parent::option_definition();

    $options['status_finished_no_errors'] = array(
      'default' => 'Finished',
      'translatable' => TRUE,
    );

    $options['status_finished_with_errors'] = array(
      'default' => 'Finished with errors',
      'translatable' => TRUE,
    );

    $options['status_in_progress'] = array(
      'default' => 'In progress',
      'translatable' => TRUE,
    );

    $options['status_unknown'] = array(
      'default' => 'Unknown',
      'translatable' => TRUE,
    );

    $options['status_failed'] = array(
      'default' => 'Failed',
      'translatable' => TRUE,
    );

    $options['status_new'] = array(
      'default' => 'New',
      'translatable' => TRUE,
    );

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function options_form(&$form, &$form_state) {
    $weight = 10;
    $form['status_finished_no_errors'] = array(
      '#weight' => $weight++,
      '#type' => 'textfield',
      '#title' => t('Status when no errors found'),
      '#default_value' => $this->options['status_finished_no_errors'],
    );

    $form['status_finished_with_errors'] = array(
      '#weight' => $weight++,
      '#type' => 'textfield',
      '#title' => t('Status when one or multiple error(s) found'),
      '#default_value' => $this->options['status_finished_with_errors'],
    );

    $form['status_in_progress'] = array(
      '#weight' => $weight++,
      '#type' => 'textfield',
      '#title' => t('Status when a Harvest is in progress'),
      '#default_value' => $this->options['status_in_progress'],
    );

    $form['status_unknown'] = array(
      '#weight' => $weight++,
      '#type' => 'textfield',
      '#title' => t('Status not known'),
      '#default_value' => $this->options['status_unknown'],
    );

    $form['status_failed'] = array(
      '#weight' => $weight++,
      '#type' => 'textfield',
      '#title' => t('Status when harvest failed'),
      '#default_value' => $this->options['status_failed'],
    );

    $form['status_new'] = array(
      '#weight' => $weight++,
      '#type' => 'textfield',
      '#title' => t('Status when harvest is still new and was never executed'),
      '#default_value' => $this->options['status_new'],
    );

    parent::options_form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function get_value($values, $field = NULL) {
    // This will get executed quite often especially if there is sorting
    // involved. Instead of loading the hole node object and getting the
    // machine_name from it but use a direct database query instead.
    $result = db_query("SELECT field_dkan_harvest_machine_name_machine FROM {field_data_field_dkan_harvest_machine_name} WHERE entity_type = :entity_type AND bundle = :bundle AND entity_id = :entity_id;",
      array(
        ':entity_type' => 'node',
        ':bundle' => 'harvest_source',
        ':entity_id' => $values->nid,
      ));

    if (empty($result)) {
      watchdog('dkan_harvest_dashboard', "Failed to machine name from \"field_dkan_harvest_machine_name_machine\" table.", array(), WATCHDOG_ERROR);
      return self::STATUS_UNKNOWN;
    }

    $machine_name = $result->fetchField();

    if (empty($machine_name)) {
      // Should not have happened.
      watchdog('dkan_harvest_dashboard', "HarvestSource machine_name not found.", array(), WATCHDOG_ERROR);
      return self::STATUS_UNKNOWN;
    }

    $harvest_source = new HarvestSource($machine_name);

    // Check the current status of the migration.
    $result = db_query(
      "SELECT status FROM {migrate_status} WHERE machine_name = :migration_machine_name  LIMIT 1",
      array(':migration_machine_name' => $harvest_source->getMigrationMachineName())
    );

    if (empty($result)) {
      watchdog(
        'dkan_harvest_dashboard', "Failed to query migration status from \"@table\" table.",
        array('@table' => 'migrate_status'),
        WATCHDOG_ERROR
      );
      return self::STATUS_UNKNOWN;
    }

    $migration_status = $result->fetchField();

    switch ($migration_status) {
      case MigrationBase::STATUS_IDLE:
        break;

      case MigrationBase::STATUS_IMPORTING:
      case MigrationBase::STATUS_ROLLING_BACK:
      case MigrationBase::STATUS_IMPORTING:
        return self::STATUS_IN_PROGRESS;

      default:
        return self::STATUS_UNKNOWN;
    }

    // Confirmed the migration is idle, work from the mlid to get the error
    // messages and failed dataset counts.
    $result = db_query(
      "SELECT mlid FROM {migrate_log} WHERE machine_name = :migration_machine_name ORDER BY mlid DESC LIMIT 1",
      array(':migration_machine_name' => $harvest_source->getMigrationMachineName())
    );

    if (empty($result)) {
      watchdog('dkan_harvest_dashboard', "Failed to query migration mlid from \"migrate_log\" table.", array(), WATCHDOG_ERROR);
      return self::STATUS_UNKNOWN;
    }

    $mlid = $result->fetchField();

    if (empty($mlid)) {
      // No MLID in the log table. Assume the Harvest Migration never executed
      // and still new.
      return self::STATUS_NEW;
    }

    $harvest_source_migration = dkan_harvest_get_migration($harvest_source);
    $mapTable = $harvest_source_migration->getMap()->getMapTable();
    $messageTable = $harvest_source_migration->getMap()->getMessageTable();
    $logTable = $harvest_source_migration->getMap()->getLogTable();

    // Fetch the number of error message recorded during the latest Harvest
    // Migration.
    $result = db_query("SELECT COUNT(*) FROM {$messageTable} WHERE mlid = :mlid AND level = :error_level",
      array(
        ':mlid' => $mlid,
        ':error_level' => MigrationBase::MESSAGE_ERROR,
      ));

    if (empty($result)) {
      watchdog(
        'dkan_harvest_dashboard', "Failed to query migration messages from \"@table\" table.",
        array('@table' => $messageTable),
        WATCHDOG_ERROR
      );
      return self::STATUS_UNKNOWN;
    }

    $errors_count = $result->fetchField();

    // Fetch the number of failed datasets during the last migration.
    $result = db_query(
      "SELECT failed FROM {$logTable} WHERE mlid = :mlid",
      array(':mlid' => $mlid)
    );

    if (empty($result)) {
      watchdog(
        'dkan_harvest_dashboard', "Failed to query failed dataset count from \"@table\" table.",
        array('@table' => $logTable),
        WATCHDOG_ERROR
      );
      return self::STATUS_UNKNOWN;
    }

    $failed_count = $result->fetchField();

    $result = db_query("SELECT count(*) FROM {$mapTable}", array());

    if (empty($result)) {
      watchdog(
        'dkan_harvest_dashboard', "Failed to query tracked datasets from \"@table\" table.",
        array('@table' => $mapTable),
        WATCHDOG_ERROR
      );
      return self::STATUS_UNKNOWN;
    }

    $total_count = $result->fetchField();

    if ($failed_count >= $total_count) {
      return self::STATUS_FAILED;
    }
    elseif ($errors_count > 0 || $failed_count > 0) {
      return self::STATUS_FINISHED_WITH_ERRORS;
    }
    elseif ($errors_count == 0 && $failed_count == 0) {
      return self::STATUS_FINISHED_NO_ERRORS;
    }

    // At this point we should've been out. Return UNKNOWN.
    return self::STATUS_UNKNOWN;
  }

  /**
   * {@inheritdoc}
   */
  public function render($values) {
    $value = $this->get_value($values);

    switch ($value) {
      case self::STATUS_FINISHED_NO_ERRORS:
        return filter_xss_admin($this->options['status_finished_no_errors']);

      case self::STATUS_FINISHED_WITH_ERRORS:
        return filter_xss_admin($this->options['status_finished_with_errors']);

      case self::STATUS_IN_PROGRESS:
        return filter_xss_admin($this->options['status_in_progress']);

      case self::STATUS_FAILED:
        return filter_xss_admin($this->options['status_failed']);

      case self::STATUS_NEW:
        return filter_xss_admin($this->options['status_new']);

      case self::STATUS_UNKNOWN:
      default:
        return filter_xss_admin($this->options['status_unknown']);
    }
  }

}
