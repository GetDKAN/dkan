<?php
/**
 * @file
 * Admin UI functionality.
 */

/**
 * Form callback for relating a data table to a node.
 */
function data_node_settings_form($form, &$form_state, $table) {
  drupal_set_title($table->get('title'));

  $form = array();
  $content_types = array('' => t('None'));
  $content_types += node_type_get_names();
  $meta = $table->get('meta');
  $form['#table'] = $table;
  $form['content_type'] = array(
    '#type' => 'select',
    '#title' => t('Content type'),
    '#options' => $content_types,
    '#default_value' => $meta['data_node']['content_type'],
    '#description' => t('Select the content type this data table can be related to.'),
  );
  $schema = $table->get('table_schema');
  $fields = drupal_map_assoc(array_keys($schema['fields']));
  $form['id'] = array(
    '#type' => 'select',
    '#title' => t('Identifier'),
    '#options' => $fields,
    '#default_value' => $meta['data_node']['id'],
    '#description' => t('Select the identifier of the data table that should be related to a node.'),
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
function data_node_settings_form_submit($form, &$form_state) {
  $meta = $form['#table']->get('meta');
  $meta['data_node']['content_type'] = $form_state['values']['content_type'];
  $meta['data_node']['id'] = $form_state['values']['id'];
  $form['#table']->update(array('meta' => $meta));
  drupal_set_message(t('Settings saved.'));
}
