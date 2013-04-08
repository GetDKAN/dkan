<?php
/**
 * @file
 * Admin UI functionality.
 */

/**
 * Form callback for relating a data table to a node.
 */
function data_taxonomy_settings_form($form, &$form_state, $table) {
  drupal_set_title($table->get('title'));

  $form = $vocabularies = array();
  foreach (taxonomy_get_vocabularies() as $v) {
    if (strpos($v->module, 'features_') === 0) {
      $vocabularies[filter_xss_admin($v->module)] = check_plain($v->name);
    }
    else {
      $vocabularies[$v->vid] = check_plain($v->name);
    }
  }
  $meta = $table->get('meta');
  $form['#table'] = $table;
  $form['vocabularies'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Vocabularies'),
    '#options' => $vocabularies,
    '#default_value' => isset($meta['data_taxonomy']['vocabularies']) ? $meta['data_taxonomy']['vocabularies'] : array(),
    '#description' => t('Select the vocabulary to whose terms this data table\'s entries can be related to.'),
  );
  $schema = $table->get('table_schema');
  $fields = drupal_map_assoc(array_keys($schema['fields']));
  $form['id'] = array(
    '#type' => 'select',
    '#title' => t('Identifier'),
    '#options' => $fields,
    '#default_value' => $meta['data_taxonomy']['id'],
    '#description' => t('Select the identifier of the data table that should be related to terms.'),
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
  return $form;
}

/**
 * Submit handler for data_node_settings_form().
 */
function data_taxonomy_settings_form_submit($form, &$form_state) {
  $meta = $form['#table']->get('meta');
  $meta['data_taxonomy']['vocabularies'] = array_filter($form_state['values']['vocabularies']);
  $meta['data_taxonomy']['id'] = $form_state['values']['id'];
  $form['#table']->update(array('meta' => $meta));
  drupal_set_message(t('Settings saved.'));
}
