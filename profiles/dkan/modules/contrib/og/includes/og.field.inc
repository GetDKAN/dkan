<?php

/**
 * @file
 * Field widget related code for Organic groups.
 */


/**
 * Implements hook_field_widget_info().
 */
function og_field_widget_info() {
  $widgets['og_complex'] = array(
    'label' => t('OG reference'),
    'description' => t('Complex widget to reference groups.'),
    'field types' => array('entityreference'),
  );

  return $widgets;
}


/**
 * Implements hook_field_widget_form().
 */
function og_field_widget_form(&$form, &$form_state, $field, $instance, $langcode, $items, $delta, $element) {
  $entity_type = $instance['entity_type'];
  $entity = isset($element['#entity']) ? $element['#entity'] : NULL;

  if (!$entity) {
    return;
  }

  if ($field['settings']['handler'] != 'og' && strpos($field['settings']['handler'], 'og_') !== 0) {
    $params = array('%label' => $instance['label']);
    form_error($form, t('Field %label is a group-audience but its Entity selection mode is not defined as "Organic groups" in the field settings page.', $params));
    return;
  }

  // Cache the processed entity, to make sure we call the widget only once.
  $cache = &drupal_static(__FUNCTION__, array());
  list($id,, $bundle) = entity_extract_ids($entity_type, $entity);
  $field_name = $field['field_name'];

  $identifier = $field_name . ':' . $entity_type . ':' . $bundle . ':' . $id;
  if (isset($cache[$identifier])) {
    return array();
  }
  $cache[$identifier] = TRUE;

  ctools_include('fields');

  $field_modes = array('default');
  $has_admin = FALSE;

  // The group IDs that might not be accessible by the user, but we need
  // to keep even after saving.
  $element['#other_groups_ids'] = array();
  $element['#element_validate'][] = 'og_complex_widget_element_validate';

  if (user_access('administer group')) {
    $has_admin = TRUE;
    $field_modes[] = 'admin';
  }

  // Build an array of entity IDs. Field's $items are loaded
  // in OgBehaviorHandler::load().
  $entity_gids = array();
  foreach ($items as $item) {
    $entity_gids[] = $item['target_id'];
  }

  $target_type = $field['settings']['target_type'];
  $user_gids = og_get_entity_groups();
  $user_gids = !empty($user_gids[$target_type]) ? $user_gids[$target_type] : array();

  // Get the "Other group" group IDs.
  $other_groups_ids = array_diff($entity_gids, $user_gids);

  foreach ($field_modes as $field_mode) {
    $mocked_instance = og_get_mocked_instance($instance, $field_mode);
    $dummy_entity = clone $entity;

    if ($has_admin) {
      $mocked_instance['required'] = FALSE;
      if ($field_mode == 'default') {
        $mocked_instance['label'] = t('Your groups');
        if ($entity_type == 'user') {
          $mocked_instance['description'] = t('Associate this user with groups you belong to.');
        }
        else {
          $mocked_instance['description'] = t('Associate this content with groups you belong to.');
        }
      }
      else {
        $mocked_instance['label'] = t('Other groups');
        if ($entity_type == 'user') {
          $mocked_instance['description'] = t('As groups administrator, associate this user with groups you do <em>not</em> belong to.');
        }
        else {
          $mocked_instance['description'] = t('As groups administrator, associate this content with groups you do <em>not</em> belong to.');
        }
      }

      if ($id) {
        // The field might be required, and it will throw an exception
        // when we try to set an empty value, so change the wrapper's
        // info.
        $wrapper = entity_metadata_wrapper($entity_type, $dummy_entity, array('property info alter' => 'og_property_info_alter', 'field name' => $field_name));
        if ($field_mode == 'admin') {
          // Keep only the hidden group IDs on the entity, so they won't
          // appear again on the "admin" field, for example on an autocomplete
          // widget type.
          $valid_ids = $other_groups_ids ? entityreference_get_selection_handler($field, $mocked_instance, $entity_type, $dummy_entity)->validateReferencableEntities($other_groups_ids) : array();
          $valid_ids = $field['cardinality'] == 1 ? reset($valid_ids) : $valid_ids;
          $wrapper->{$field_name}->set($valid_ids ? $valid_ids : NULL);
        }
        else {
          // Keep only the groups that belong to the user and to the entity.
          $my_group_ids = array_values(array_intersect($user_gids, $entity_gids));
          $valid_ids = $my_group_ids ? entityreference_get_selection_handler($field, $mocked_instance, $entity_type, $dummy_entity)->validateReferencableEntities($my_group_ids) : array();

          $valid_ids = $field['cardinality'] == 1 ? reset($valid_ids) : $valid_ids;
          $wrapper->{$field_name}->set($valid_ids ? $valid_ids : NULL);
        }
      }
    }
    elseif ($other_groups_ids) {
      foreach ($other_groups_ids as $id) {
        $element['#other_groups_ids'][] = array(
          'target_id' => $id,
          'field_mode' => 'admin',
        );
      }

      if (!empty($dummy_entity->{$field_name}[$langcode])) {
        // Non-admin user.
        $ids = array();
        foreach ($dummy_entity->{$field_name}[$langcode] as $delta => $value) {
          $id = $value['target_id'];
          if (!in_array($id, $other_groups_ids)) {
            $ids[] = $id;
          }
        }

        // Rekey the field items.
        $dummy_entity->{$field_name}[$langcode] = array();
        foreach ($ids as $id) {
          $dummy_entity->{$field_name}[$langcode][] = array('target_id' => $id);
        }
      }
    }

    $dummy_form_state = $form_state;
    if (empty($form_state['rebuild'])) {
      // Form is "fresh" (i.e. not call from field_add_more_submit()), so
      // re-set the items-count, to show the correct amount for the mocked
      // instance.
      $dummy_form_state['field'][$field_name][$langcode]['items_count'] = !empty($dummy_entity->{$field_name}[$langcode]) ? count($dummy_entity->{$field_name}[$langcode]) : 0;
    }

    $new_element = ctools_field_invoke_field($mocked_instance, 'form', $entity_type, $dummy_entity, $form, $dummy_form_state, array('default' => TRUE));
    $element[$field_mode] = $new_element[$field_name][LANGUAGE_NONE];
    if (in_array($mocked_instance['widget']['type'], array('entityreference_autocomplete', 'entityreference_autocomplete_tags'))) {
      // Change the "Add more" button name so it adds only the needed
      // element.
      if (!empty($element[$field_mode]['add_more']['#name'])) {
        $element[$field_mode]['add_more']['#name'] .= '__' . $field_mode;
      }

      if ($mocked_instance['widget']['type'] == 'entityreference_autocomplete') {
        foreach (array_keys($element[$field_mode]) as $delta) {
          if (!is_numeric($delta)) {
            continue;
          }

          $sub_element = &$element[$field_mode][$delta]['target_id'];
          _og_field_widget_replace_autocomplete_path($sub_element, $field_mode);

        }
      }
      else {
        // Tags widget, there's no delta, we can pass the element itself.
        _og_field_widget_replace_autocomplete_path($element[$field_mode], $field_mode);
      }
    }
  }

  $form['#after_build']['og'] = 'og_complex_widget_after_build';
  $form['#validate'][] = 'og_validate_widgets';

  return $element;
}

/**
 * Replace Entity-reference's autocomplete path with our own.
 *
 * @param $element
 *   The form element, passed by reference.
 * @param $field_mode
 *   The field mode.
 *
 */
function _og_field_widget_replace_autocomplete_path(&$element, $field_mode) {
  // Rebuild the autocomplete path.
  $path = explode('/', $element['#autocomplete_path']);
  $element['#autocomplete_path'] = 'og/autocomplete';

  // Add autocomplete type
  $element['#autocomplete_path'] .= "/$path[2]/$path[3]/$path[4]/$path[5]";

  // Add field mode.
  $element['#autocomplete_path'] .= "/$field_mode";

  // Add the entity ID.
  $element['#autocomplete_path'] .= "/$path[6]";
  if (!empty($path[7])) {
    // Add the text.
    $element['#autocomplete_path'] .= "/$path[7]";
  }
}


/**
 * Register group audience field related form errors.
 *
 * @param $field_name
 *   The group audience field
 * @param $errors
 *   Array with errors.
 *
 * @return
 *   Return the cached values.
 *
 * @see og_validate_widgets()
 * @see OgBehaviorHandler::validate()
 */
function og_field_widget_register_errors($field_name = NULL, $errors = NULL) {
  $cache = &drupal_static(__FUNCTION__, array());

  if (!empty($field_name)) {
    $cache[$field_name] = $errors;
  }

  return $cache;
}

/**
 * Property info alter; Change mocked field to be non-required.
 */
function og_property_info_alter($wrapper, $info) {
  $property_info = $wrapper->info();
  $field_name = $property_info['field name'];
  $info['properties'][$field_name]['required'] = FALSE;
  return $info;
}

/**
 * Helper function; Get the mocked instance.
 */
function og_get_mocked_instance($instance, $field_mode) {
  $mocked_instance = $instance;
  $widget_type = $instance['settings']['behaviors']['og_widget'][$field_mode]['widget_type'];
  $mocked_instance['widget']['type'] = $widget_type;

  // Set the widget's module.
  $widget_info = field_info_widget_types($widget_type);
  $mocked_instance['widget']['module'] = $widget_info['module'];
  $mocked_instance['widget']['settings'] = drupal_array_merge_deep($mocked_instance['widget']['settings'], $widget_info['settings']);

  // See OgSelectionHandler::buildEntityFieldQuery().
  $mocked_instance['field_mode'] = $field_mode;
  return $mocked_instance;
}

/**
 * Rebuild the element's values, using the default and admin if exists.
 */
function og_complex_widget_element_validate($element, &$form_state, $form) {
  $subform = drupal_array_get_nested_value($form_state['values'], $element['#array_parents']);
  $ids = array();
  foreach (array('default', 'admin') as $field_mode) {
    if (empty($subform[$field_mode])) {
      continue;
    }

    foreach ($subform[$field_mode] as $value) {
      if (!empty($value['target_id']) && is_numeric($value['target_id'])) {
        $ids[] = array(
          'target_id' => $value['target_id'],
          // Add the field mode so we can later validate it in
          // OgBehaviorHandler::validate()
          'field_mode' => $field_mode,
        );
      }
    }
  }

  $ids = array_merge($ids, $element['#other_groups_ids']);

  // Set the form values by directly using drupal_array_set_nested_values(),
  // which allows us to control the element parents. In this case we cut off the
  // last element that contains the delta 0, as $ids is already keyed with
  // deltas.
  drupal_array_set_nested_value($form_state['values'], array_slice($element['#parents'], 0, -1), $ids, TRUE);

  // If the element is required, ensure that at least one group has been chosen.
  if ($element['#required']) {
    $subform = drupal_array_get_nested_value($form_state['values'], $element['#array_parents']);
    if (empty($subform)) {
      form_error($element, t('!name field is required.', array('!name' => $element['#title'])));
    }
  }
}

/**
 * Validate handler; Assert group audience fields reference valid groups.
 *
 * @see field_default_form_errors().
 */
function og_validate_widgets($form, &$form_state) {
  if (!$errors = og_field_widget_register_errors()) {
    return;
  }

  foreach ($errors as $field_name => $field_modes) {
    foreach ($field_modes as $field_mode => $error_items) {
      foreach ($error_items as $error_item) {
        $element = $form[$field_name][LANGUAGE_NONE][0][$field_mode];
        form_error($element, $error_item['message']);
      }
    }
  }
}

/**
 * After build; Remove the "Add more" button.
 *
 * @see field_multiple_value_form()
 * @see theme_field_multiple_value_form()
 */
function og_complex_widget_after_build($form, &$form_state) {
  foreach (og_get_group_audience_fields($form['#entity_type'], $form['#bundle']) as $field_name => $value) {
    if (empty($form[$field_name])) {
      continue;
    }
    unset($form[$field_name][LANGUAGE_NONE]['#theme']);
    unset($form[$field_name][LANGUAGE_NONE]['add_more']);
    unset($form[$field_name][LANGUAGE_NONE][0]['_weight']);

    if (!empty($form[$field_name][LANGUAGE_NONE][0]['admin'])) {
      // Wrap both elements with a fieldset.
      $form[$field_name][LANGUAGE_NONE]['#theme_wrappers'] = array('fieldset');
    }
  }

  return $form;
}

/**
 * Menu callback: autocomplete the label of an entity.
 *
 * @param $type
 *   The widget type (i.e. 'single' or 'tags').
 * @param $field_name
 *   The name of the entity-reference field.
 * @param $entity_type
 *   The entity type.
 * @param $bundle_name
 *   The bundle name.
 * @param $field_mode
 *   The field mode, "default" or "admin".
 * @param $entity_id
 *   Optional; The entity ID the entity-reference field is attached to.
 *   Defaults to ''.
 * @param $string
 *   The label of the entity to query by.
 *
 * @see entityreference_autocomplete_callback()
 */
function og_entityreference_autocomplete_callback($type, $field_name, $entity_type, $bundle_name, $field_mode, $entity_id = '', $string = '') {
  $field = field_info_field($field_name);
  $instance = field_info_instance($entity_type, $field_name, $bundle_name);
  $instance = og_get_mocked_instance($instance, $field_mode);

  if (!$field || !$instance || $field['type'] != 'entityreference' || !field_access('edit', $field, $entity_type)) {
    return MENU_ACCESS_DENIED;
  }

  return entityreference_autocomplete_callback_get_matches($type, $field, $instance, $entity_type, $entity_id, $string);
}

