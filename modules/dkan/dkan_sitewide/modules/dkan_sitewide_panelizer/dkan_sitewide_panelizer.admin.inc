<?php

/**
 * @file
 * Hooks and functions implementations for administration pages.
 */

/**
 * Hook: hook_menu().
 */
function dkan_sitewide_panelizer_menu() {

  $items = array();

  $items['admin/config/content/dkan_panelizer'] = array(
    'title' => 'Panelizer DKAN',
    'description' => t('Configure DKAN module to embed panelizer layout form on content creation.'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('dkan_sitewide_panelizer_admin'),
    'access arguments' => array('administer dkan_sitewide_panelizer settings'),
    'type' => MENU_NORMAL_ITEM,
  );

  return $items;
}

/**
 * Generation of administration form.
 */
function dkan_sitewide_panelizer_admin() {

  $panelized_content_types = array();
  $non_panelized_content_types = array();
  $enabled_content_types = array();

  // Get Panelizer handler used to check if a content type is panelized.
  $handler = panelizer_entity_plugin_get_handler('node');
  if (!$handler) {
    return;
  }

  // Get all available content types.
  $content_types = node_type_get_types();
  foreach ($content_types as $content_type_name => $content_type_data) {

    $is_panelized = $handler->is_panelized($content_type_name . '.default');
    $has_default = $handler->has_default_panel($content_type_name . '.default');

    if ($is_panelized && $has_default) {

      $panelized_content_types[$content_type_name] = $content_type_data->name;
      if (variable_get('dkan_sitewide_panelizer_' . $content_type_name)) {
        $enabled_content_types[] = $content_type_name;
      }
    }
    else {

      $non_panelized_content_types[$content_type_name] = $content_type_data->name;
    }
  }

  // Build form.
  $form = array();

  $form['panelized_content_types'] = array(
    '#title' => t('Panelized Content Types'),
    '#type' => 'checkboxes',
    '#default_value' => $enabled_content_types,
    '#options' => $panelized_content_types,
  );

  $form['non_panelized_content_types'] = array(
    '#title' => t('Non panelized Content Types'),
    '#type' => 'checkboxes',
    '#description' => t("This content types don't have their 'default' view panelized or&nbsp;
                        they don't have a default panel for that view.&nbsp;
                        You can panelize them <a href='/admin/config/content/panelizer'>here</a>."),
    '#disabled' => TRUE,
    '#options' => $non_panelized_content_types,
  );

  $form['#submit'][] = 'dkan_sitewide_panelizer_admin_submit';

  return system_settings_form($form);
}

/**
 * Custom submit handler.
 */
function dkan_sitewide_panelizer_admin_submit($form, &$form_state) {

  $config_values = array_merge($form_state['values']['panelized_content_types'],
                              $form_state['values']['non_panelized_content_types']);

  foreach ($config_values as $content_type_name => $value) {

    if ($value) {
      variable_set('dkan_sitewide_panelizer_' . $content_type_name, TRUE);
    }
    else {
      variable_set('dkan_sitewide_panelizer_' . $content_type_name, FALSE);
    }
  }
}
