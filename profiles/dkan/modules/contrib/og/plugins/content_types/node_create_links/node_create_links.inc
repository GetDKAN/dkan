<?php

if (module_exists('entityreference_prepopulate')) {
  /**
   * Plugin definition.
   */
  $plugin = array(
    'title' => t('Content create links'),
    'description' => t('Crafted links to create content (nodes) for a given group.'),
    'required context' => new ctools_context_required(t('Node'), 'node'),
    'category' => t('Organic groups'),
    'defaults' => array(
      'types' => array(),
      'field_name' => OG_AUDIENCE_FIELD,
    ),
  );
}

/**
 * Render callback.
 */
function og_node_create_links_content_type_render($subtype, $conf, $args, $context) {
  if (empty($context->data)) {
    return FALSE;
  }

  $node = $context->data;
  $links = og_node_create_links('node', $node->nid, $conf['field_name'], NULL, !empty($conf['types']) ? $conf['types'] : NULL);
  if (!$links) {
    return FALSE;
  }

  $module = 'og';
  $block = new stdClass();
  $block->module = $module;
  $block->title = t('Content create links');

  $block->content = $links;
  return $block;
}

/**
 * Edit form.
 */
function og_node_create_links_content_type_edit_form($form, &$form_state) {
  $conf = $form_state['conf'];

  $options = array();
  foreach (field_info_fields() as $field_name => $field) {
    if (!og_is_group_audience_field($field_name)) {
      continue;
    }

    if ($field['settings']['target_type'] != 'node') {
      continue;
    }
    // Use CTools to get the best matching field name.
    ctools_include('fields');
    $options[$field_name] = ctools_field_label($field_name) . " ($field_name)";
  }

  $form['field_name'] = array(
    '#title' => t('Field name'),
    '#type' => 'select',
    '#options' => $options,
    '#default_value' => $conf['field_name'],
    '#description' => t('The group audience field to prepopulate.'),
    '#required' => TRUE,
  );

  $options = array();
  foreach (node_type_get_types() as $type) {
    if (og_is_group_content_type('node', $type->type)) {
      $options[$type->type] = check_plain($type->name);
    }
  }
  $form['types'] = array(
    '#title' => t('Restrict to content types'),
    '#type' => 'checkboxes',
    '#options' => $options,
    '#default_value' => $conf['types'],
    '#description' => t('If left empty, all possible content types are shown.'),
  );
  return $form;
}

/**
 * Edit form submit callback.
 */
function og_node_create_links_content_type_edit_form_submit($form, &$form_state) {
  $form_state['conf']['field_name'] = $form_state['values']['field_name'];
  $form_state['conf']['types'] = array_filter($form_state['values']['types']);
}
