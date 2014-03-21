<?php
/**
 * @file
 * Admin UI functions.
 */

/**
 * Form builder for the entity type settings.
 *
 * @param $table
 *  The machine name of a table.
 */
function data_entity_admin_entity_type_form($form, &$form_state, $table) {
  $table = data_get_table($table);

  drupal_set_title($table->get('title'));

  data_entity_meta_add_defaults($table->meta);
  $meta = $table->get('meta');
  $schema = $table->get('table_schema');

  $id_field_options = array();
  foreach ($schema['fields'] as $field_name => $field) {
    if (in_array($field['type'], array('serial','int'))) {
      $id_field_options[$field_name] = $field_name;
    }
  }

  if (empty($id_field_options)) {
    drupal_set_message(t("The table may not be declared as an entity type because it has no suitable schema field to use as an ID."), 'error');
    return $form;
  }

  $form['#table'] = $table;

  $form['is_entity_type'] = array(
    '#type' => 'checkbox',
    '#title' => t('Define table as an entity type'),
    '#description' => t('Declare an entity type based on this table, where each row will be an entity. Requires a serial or unique integer field on the table.'),
    '#default_value' => $meta['is_entity_type'],
  );

  $form['entity_id'] = array(
    '#type' => 'radios',
    '#title' => t('Entity ID field'),
    '#description' => t('Select a serial or unique integer field on the table to use as the entity ID. This may not be changed once the entity type has fields.'),
    '#options' => $id_field_options,
    '#required' => TRUE,
    '#default_value' => $meta['entity_id'],
  );

  // Get field instances. They will be on the bundle which has the same name
  // as the entity type.
  $entity_type = 'data_' . $table->name;
  $instances = field_info_instances($entity_type);
  if (!empty($instances[$entity_type])) {
    $form['is_entity_type']['#disabled'] = TRUE;
    $form['entity_id']['#disabled'] = TRUE;
    drupal_set_message(t("The entity type settings may not be changed because this entity type has field instances."));
  }

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );

  return $form;
}

/**
 * Submit handler for the entity type settings.
 */
function data_entity_admin_entity_type_form_submit($form, &$form_state) {
  $table = $form['#table'];

  // Update meta data.
  $meta = $table->get('meta');

  $meta['is_entity_type'] = $form_state['values']['is_entity_type'];
  $meta['entity_id']      = $form_state['values']['entity_id'];
  $table->update(array('meta' => $meta));

  // Clear all caches in case entity info has changed.
  entity_info_cache_clear();
  menu_rebuild();

  drupal_set_message(t('Saved changes.'));
}

/**
 * Form builder for table entity options.
 */
function data_entity_admin_entity_form($form, &$form_state, $table) {
  $table = data_get_table($table);

  $form = array();

  drupal_set_title($table->get('title'));

  $schema = $table->get('table_schema');
  // Add in our defaults to the table meta data.
  data_entity_meta_add_defaults($table->meta);
  $meta = $table->get('meta');

  if (empty($meta['is_entity_type'])) {
    drupal_set_message(t("This table has not been declared as an entity type."));
    return $form;
  }

  // Keep table.
  $form['table'] = array(
    '#type' => 'value',
    '#value' => $table,
  );

  $form['fields'] = array('#tree' => TRUE);
  if (isset($schema['fields'])) {
    foreach ($schema['fields'] as $field_name => $field) {
      $form['fields'][$field_name] = array();
      $form['fields'][$field_name]['name'] = array('#markup' => $field_name);
      $form['fields'][$field_name]['locked'] = array(
        '#type' => 'checkbox',
        '#default_value' => $meta['fields'][$field_name]['locked'],
      );
      $form['fields'][$field_name]['required'] = array(
        '#type' => 'checkbox',
        '#default_value' => $meta['fields'][$field_name]['required'],
      );
      $form['fields'][$field_name]['label_field'] = array(
        '#type' => 'radio',
        '#parents' => array('label_field'),
        '#return_value' => $field_name,
        '#default_value' => ($field_name == $meta['label_field']) ? $field_name : '',
      );
    }
  }

  // The id field has these set automatically; it's just there for show.
  $id_field = $meta['entity_id'];
  $form['fields'][$id_field]['locked']['#default_value']    = TRUE;
  $form['fields'][$id_field]['locked']['#disabled']         = TRUE;
  $form['fields'][$id_field]['required']['#default_value']  = TRUE;
  $form['fields'][$id_field]['required']['#disabled']       = TRUE;

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );

  return $form;
}

/**
 * Theme data_ui_edit_form.
 */
function theme_data_entity_admin_entity_form($variables) {
  $form = $variables['form'];

  if (empty($form['table'])) {
    // If there's nothing in the form, there's nothing to do.
    return;
  }

  // Format existing fields.
  $rows = array();
  foreach (element_children($form['fields']) as $e) {
    $row = array();
    foreach (element_children($form['fields'][$e]) as $f) {
      $row[] = drupal_render($form['fields'][$e][$f]);
    }
    $row[] = '&nbsp;';
    $rows[] = $row;
  }

  $header = array(t('Name'), t('Locked'), t('Required'), t('Label'));
  $output = theme('table', array('header' => $header, 'rows' => $rows));
  $output .= drupal_render_children($form);
  return $output;
}

function data_entity_admin_entity_form_submit($form, &$form_state) {
  //dsm($form_state['values']);
  $table = $form_state['values']['table'];

  // Update meta data.
  $meta = $table->get('meta');
  foreach ($form_state['values']['fields'] as $field_name => $values) {
    foreach ($values as $key => $value) {
      $meta['fields'][$field_name][$key] = $value;
    }
  }

  if (!empty($form_state['values']['label_field'])) {
    $meta['label_field'] = $form_state['values']['label_field'];
  }

  $table->update(array('meta' => $meta));
  drupal_set_message(t('Saved changes'));
}
