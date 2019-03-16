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

    $result_array = $result->fetchAssoc();

    // Should not happen.
    if (empty($result_array)) {
      watchdog('dkan_harvest_dashboard', "HarvestSource machine_name not found.", array(), WATCHDOG_ERROR);
      return self::STATUS_UNKNOWN;
    }

    $machine_name = array_pop($result_array);

    $harvest_source = new HarvestSource($machine_name);

    // Check the current status of the migration.
    $query_migrate_status = db_query("SELECT status  FROM {migrate_status} WHERE machine_name = :migration_machine_name  LIMIT 1",
      array(
        ':migration_machine_name' => $harvest_source->getMigrationMachineName(),
      ));
    $result_status = $query_migrate_status->fetchAssoc();

    if (empty($result_status)) {
      watchdog('dkan_harvest_dashboard', "Failed to query migration status .", array(), WATCHDOG_ERROR);
      return self::STATUS_UNKNOWN;
    }

    $migration_status = array_pop($result_status);

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

    // Migration is idle. Get the latest migration status.
    // Get the latest mlid from the message table.
    $query_migrate_log = db_query("SELECT mlid FROM {migrate_log} WHERE machine_name = :migration_machine_name ORDER BY mlid DESC LIMIT 1",
      array(
        ':migration_machine_name' => $harvest_source->getMigrationMachineName(),
      ));

    $result_migrate_log = $query_migrate_log->fetchAssoc();

    if (empty($result_migrate_log)) {
      watchdog('dkan_harvest_dashboard', "MLID not found.", array(), WATCHDOG_ERROR);
      return self::STATUS_UNKNOWN;
    }

    // Get rid of the array.
    $mlid = array_pop($result_migrate_log);

    $harvest_source_migration = dkan_harvest_get_migration($harvest_source);
    $messageTable = $harvest_source_migration->getMap()->getMessageTable();
    $logTable = $harvest_source_migration->getMap()->getLogTable();

    // Let's see if we got errors during the last migration.
    $query_message_table = db_query("SELECT COUNT(*) FROM {$messageTable} WHERE mlid = :mlid AND level = :error_level",
      array(
        ':mlid' => $mlid,
        ':error_level' => MigrationBase::MESSAGE_ERROR,
      ));
    $result_message_table = $query_message_table->fetchAssoc();

    // Let's see if we didn't get errors during the last migration but failed.
    $query_log_table = db_query("SELECT failed FROM {$logTable} WHERE mlid = :mlid",
      array(
        ':mlid' => $mlid,
      ));
    $result_log_table = $query_log_table->fetchField();

    if (empty($result_message_table)) {
      // I'm confused. This should no happen.
      watchdog('dkan_harvest_dashboard', "Result for error message not found.", array(), WATCHDOG_ERROR);
      return self::STATUS_UNKNOWN;
    }

    $errors_count = array_pop($result_message_table);

    if ($errors_count == 0 && $result_log_table == 0) {
      return self::STATUS_FINISHED_NO_ERRORS;
    }
    elseif ($errors_count > 0 || $result_log_table > 0) {
      return self::STATUS_FINISHED_WITH_ERRORS;
    }
    else {
      // At this point we should've been out. Return UNKNOWN.
      return self::STATUS_UNKNOWN;
    }
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

      case self::STATUS_FAILED:
        return filter_xss_admin($this->options['status_failed']);

      default:
        return t('N/A');
    }
  }

}
