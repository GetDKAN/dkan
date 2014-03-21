<?php
/**
 * @file
 * Contains general page callbacks and associated functions.
 */

/**
 * Form builder for viewing and editing a data entity.
 */
function data_entity_entity_edit_form($form, &$form_state, $table, $data_entity = NULL) {
  $form = array();

  // Add in our defaults to the table meta data.
  data_entity_meta_add_defaults($table->meta);

  // Get entity defaults.
  $entity_type = $data_entity->entity_type;
  $entity_info = entity_get_info($entity_type);
  list($id, ) = entity_extract_ids($entity_type, $data_entity);

  drupal_set_title(t('Edit @item item @id', array(
    '@item' => $entity_info['label'],
    '@id'   => $id,
  )));

  // Store essential data.
  $form['table'] = array(
    '#type' => 'value',
    '#value' => $table,
  );
  $form['#entity'] = $data_entity;

  $form['data'] = array(
    '#tree' => TRUE,
    '#weight' => -100, // Ensure this goes above fields.
  );
  foreach ($table->table_schema['fields'] as $field_name => $field) {
    // For some reason these are lower case as entity keys.
    $field_id_safe = strtolower($field_name);
    $label = !empty($table->meta['fields'][$field_name]['label']) ? $table->meta['fields'][$field_name]['label'] : $field_name;
    $description = '';

    $id_field = $table->meta['entity_id'];
    if ($id_field == $field_name) {
      $disabled = TRUE;
      $description .= t('The id field can not be edited.');
    }
    else {
      $disabled = $table->meta['fields'][$field_name]['locked'];
    }

    $form['data'][$field_name] = array(
      '#type' => 'textfield',
      '#title' => $label,
      '#description' => $description,
      // We need this check because after adding a field this gives errors.
      '#default_value' => isset($data_entity->$field_id_safe) ? $data_entity->$field_id_safe : NULL,
      '#disabled' => $disabled,
      '#required' => $table->meta['fields'][$field_name]['required'],
    );
  }

  $form['save'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
    '#weight' => 100, // Ensure this goes below fields.
  );

  // Has no effect: http://drupal.org/node/1343722
  //$form['#parents'] = array('fieldapi');
  field_attach_form($entity_type, $data_entity, $form, $form_state);

  return $form;
}

/**
 * Form validation handler for saving a data entity.
 */
function data_entity_entity_edit_form_validate($form, &$form_state) {
  $data_entity = $form['#entity'];
  $entity_type = $data_entity->entity_type;

  // Build a pseudo entity for FieldAPI field attach.
  $pseudo_entity = $form_state['values'];
  unset($pseudo_entity['data'], $pseudo_entity['table']);
  $pseudo_entity += $form_state['values']['data'];
  $pseudo_entity = (object) $pseudo_entity;

  field_attach_form_validate($entity_type, $pseudo_entity, $form, $form_state);
}

/**
 * Form submit handler for saving a data entity.
 */
function data_entity_entity_edit_form_submit($form, &$form_state) {
  //dsm($form_state, 'fs');

  $data_entity = $form['#entity'];
  $entity_type = $data_entity->entity_type;

  $table = $form_state['values']['table'];

  $record = $form_state['values']['data'];
  drupal_write_record($table->name, $record, $table->table_schema['primary key']);

  // Build a pseudo entity for FieldAPI field attach.
  $pseudo_entity = $form_state['values'];
  unset($pseudo_entity['data'], $pseudo_entity['table']);
  $pseudo_entity += $form_state['values']['data'];
  $pseudo_entity = (object) $pseudo_entity;

  field_attach_submit($entity_type, $pseudo_entity, $form, $form_state);

  // Save fields.
  field_attach_update($entity_type, $pseudo_entity);
}
