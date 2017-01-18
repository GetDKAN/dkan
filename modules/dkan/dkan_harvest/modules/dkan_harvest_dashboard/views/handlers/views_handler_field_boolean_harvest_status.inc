<?php
// @codingStandardsIgnoreFile
/**
 * Handler class implementation for Dkan Harvest Migration date field.
 */
class views_handler_field_boolean_harvest_status extends views_handler_field_boolean {

  /**
   * {@inheritdoc}
   */
  function query() {
    // Do nothing -- to override the parent query since we are not using a
    // direct database query.
  }

  /**
   * {@inheritdoc}
   */
  function click_sort($order) {
    // Usually this will add a condition to the fields sql query. Since we
    // are dealing with a psudo field with no direct database query we just
    // store this and we process the sorting in the self::post_execute()
    // methode.
    $this->order = $order;
  }

  /**
   * {@inheritdoc}
   */
  function init(&$view, &$options) {
    parent::init($view, $options);
    // Limit the offered choice to custom.
    $custom_format = array('custom' => array(t('Custom')));
    $this->formats = $custom_format;
  }

  /**
   * {@inheritdoc}
   */
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $weight = 10;
    $form['type_custom_true'] = array(
      '#weight' => $weight++,
      '#type' => 'textfield',
      '#title' => t('Status when no errors found'),
      '#default_value' => $this->options['type_custom_true'],
      '#states' => array(
        'visible' => array(
          'select[name="options[type]"]' => array('value' => 'custom'),
        ),
      ),
    );

    $form['type_custom_false'] = array(
      '#weight' => $weight++,
      '#type' => 'textfield',
      '#title' => t('Status when one or multiple error(s) found'),
      '#default_value' => $this->options['type_custom_false'],
      '#states' => array(
        'visible' => array(
          'select[name="options[type]"]' => array('value' => 'custom'),
        ),
      ),
    );

    $form['type_custom_notapplicable'] = array(
      '#weight' => $weight++,
      '#type' => 'textfield',
      '#title' => t('Status when when not applicable'),
      '#default_value' => $this->options['type_custom_notapplicable'],
      '#states' => array(
        'visible' => array(
          'select[name="options[type]"]' => array('value' => 'custom'),
        ),
      ),
    );

    $form['not'] = array(
      '#weight' => $weight++,
      '#type' => 'checkbox',
      '#title' => t('Reverse'),
      '#description' => t('If checked, true will be displayed as false.'),
      '#default_value' => $this->options['not'],
    );
  }

  /**
   * {@inheritdoc}
   */
  function get_value($values, $field = NULL) {
    // This will get executed quite often especially if there is sorting
    // involved. Instead of loading the hole node object and getting the
    // machine_name from it but use a direct database query instead.
    $result = db_query("SELECT field_dkan_harvest_machine_name_machine FROM field_data_field_dkan_harvest_machine_name WHERE entity_type = :entity_type AND bundle = :bundle AND entity_id = :entity_id;",
      array(
        ':entity_type' => 'node',
        ':bundle' => 'harvest_source',
        ':entity_id' => $values->nid,
      ));

    $result_array = $result->fetchAssoc();

    if (empty($result_array)) {
      // Should not happen.
      watchdog('dkan_harvest_dashboard', "HarvestSource machine_name not found.", array(), WATCHDOG_ERROR);
      return NULL;
    }

    $machine_name = array_pop($result_array);
    $harvest_source = new HarvestSource($machine_name);

    // Get the latest mlid from the message table.
    $query_migrate_log = db_query("SELECT mlid FROM migrate_log WHERE machine_name = :migration_machine_name ORDER BY mlid DESC LIMIT 1",
      array(
        ':migration_machine_name' => $harvest_source->getMigrationMachineName(),
      ));

    $result_migrate_log = $query_migrate_log->fetchAssoc();

    if (empty($result_migrate_log)) {
      // TODO watchdog and return default value.
      watchdog('dkan_harvest_dashboard', "MLID not found.", array(), WATCHDOG_ERROR);
      return NULL;
    }

    // Get rid of the array.
    $mlid = array_pop($result_migrate_log);

    $harvest_source_migration = dkan_harvest_get_migration($harvest_source);
    $messageTable = $harvest_source_migration->getMap()->getMessageTable();
    $logTable = $harvest_source_migration->getMap()->getLogTable();

    // Let's see if we got errors during the last migration.
    $query_message_table = db_query("SELECT COUNT(*) FROM " . $messageTable . " WHERE mlid = :mlid AND level = :error_level",
      array(
        ':mlid' => $mlid,
        ':error_level' => MigrationBase::MESSAGE_ERROR,
      ));
    $result_message_table = $query_message_table->fetchAssoc();

    // Let's see if we didn't get errors during the last migration but failed.
    $query_log_table = db_query("SELECT failed FROM " . $logTable . " WHERE mlid = :mlid",
      array(
        ':mlid' => $mlid,
      ));
    $result_log_table = $query_log_table->fetchField();

    if (empty($result_message_table)) {
      // i'm confused. This should no happen.
      watchdog('dkan_harvest_dashboard', "Result for error message not found.", array(), WATCHDOG_ERROR);
      return NULL;
    }

    $errors_count = array_pop($result_message_table);
    return (($errors_count > 0) || $result_log_table) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  function render($values) {
    $value = $this->get_value($values);

    // Add support for the undefined (NULL) value.
    if (is_null($value)) {
      if ($this->options['type'] == 'custom'
        && isset($this->options['type_custom_notapplicable'])
        && !empty(filter_xss_admin($this->options['type_custom_notapplicable']))) {
        return filter_xss_admin($this->options['type_custom_notapplicable']);
      }
      return 'N/A';
    }

    return parent::render($values);
  }

}
