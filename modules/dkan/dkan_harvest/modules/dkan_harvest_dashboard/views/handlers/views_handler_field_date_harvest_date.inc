<?php
// @codingStandardsIgnoreFile
/**
 * Handler class implementation for Dkan Harvest Migration date field.
 */
class views_handler_field_date_harvest_date extends views_handler_field_date {

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

    if (!empty($result_array)) {
      $machine_name = array_pop($result_array);
      return HarvestSource::getMigrationTimestampFromMachineName($machine_name);
    }
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
  function post_execute(&$values) {
    // Make sure to sort the proccessed views rows if this field is set to be
    // sortable.
    if (property_exists($this, 'order')) {
      foreach ($values as &$value) {
        $value->dkan_harvest_harvest_date = $this->get_value($value);
      }

      usort($values, function ($a, $b) {
        if (is_null($a->dkan_harvest_harvest_date) && is_null($b->dkan_harvest_harvest_date)) {
          return 0;
        }
        elseif (is_null($a->dkan_harvest_harvest_date) && !is_null($b->dkan_harvest_harvest_date)) {
          return 1;
        }
        elseif (!is_null($a->dkan_harvest_harvest_date) && is_null($b->dkan_harvest_harvest_date)) {
          return -1;
        }
        elseif ($a->dkan_harvest_harvest_date == $b->dkan_harvest_harvest_date) {
          return 0;
        }
        else {
          return ($a->dkan_harvest_harvest_date > $b->dkan_harvest_harvest_date) ? 1 : -1;
        }
      });

      if ($this->order == 'desc') {
        $values = array_reverse($values);
      }
    }
  }

}
