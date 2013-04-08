<?php
/**
 * @file
 * Admin UI functions.
 */

/**
 * Form builder for table entity options.
 */
function data_entity_admin_entity_form($form, &$form_state, $table) {
  drupal_set_title($table->get('title'));

  $schema = $table->get('table_schema');
  // Add in our defaults to the table meta data.
  data_entity_meta_add_defaults($table->meta);
  $meta = $table->get('meta');

  $form = array();
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
    }
  }

  // The id field has these set automatically; it's just there for show.
  $id_field = data_entity_get_id_field($table);
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

  $header = array(t('Name'), t('Locked'), t('Required'));
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
  $table->update(array('meta' => $meta));
  drupal_set_message(t('Saved changes'));
}
