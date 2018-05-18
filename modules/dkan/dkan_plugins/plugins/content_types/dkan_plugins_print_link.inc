<?php

/**
 * @file
 * Link to printer-friendly version of a node.
 */

$plugin = array(
  'title' => t('Print link'),
  'description' => t('Link to printer-friendly version of a node.'),
  'icon' => '',
  'single' => TRUE,
  'edit form' => 'dkan_plugins_print_link_content_type_edit_form',
  'render callback' => 'dkan_plugins_print_link_content_type_render',
  'category' => array(t('Custom'), -1),
  'all contexts' => TRUE,
);

/**
 * An edit form for the pane's settings.
 */
function dkan_plugins_print_link_content_type_edit_form($form, &$form_state) {
  $conf = $form_state['conf'];

  $link = $conf['print_link_title'];
  $align = $conf['print_link_align'];

  hide($form['override_title']);
  hide($form['override_title_text']);
  hide($form['override_title_heading']);
  hide($form['override_title_markup']);

  $form['print_link_title'] = array(
    '#title' => 'Link Title',
    '#type' => 'textfield',
    '#field_name' => 'print_link_text',
    '#delta' => 0,
    '#required' => TRUE,
    '#default_value' => isset($link) ? $link : 'Print',
    '#prefix' => '<p>Only add this link to node content, it will not work on non-node content.</p>',
  );
  $form['print_link_align'] = array(
    '#title' => t('Link Alignment'),
    '#type' => 'select',
    '#options' => array(
      'left' => t('Left'),
      'center' => t('Center'),
      'right' => t('Right'),
    ),
    '#default_value' => isset($align) ? $align : 'text-center',
  );
  return $form;
}

/**
 * The submit form stores the data in $conf.
 */
function dkan_plugins_print_link_content_type_edit_form_submit($form, &$form_state) {
  $keys = array(
    'print_link_title',
    'print_link_align',
  );

  foreach ($keys as $key) {
    if (isset($form_state['values'][$key])) {
      $form_state['conf'][$key] = $form_state['values'][$key];
    }
  }
}

/**
 * Render callback.
 */
function dkan_plugins_print_link_content_type_render($subtype, $conf, $panel_args, $context = NULL) {

  global $base_path;
  $link   = isset($conf['print_link_title']) ? $conf['print_link_title'] : '';
  $align  = isset($conf['print_link_align']) ? $conf['print_link_align'] : '';
  $output = '';
  $output .= '<p style="text-align:' . $align . ';">';
  $output .= '<a href="' . $base_path . 'node/' . arg(1) . '/print" ';
  $output .= 'class="print-link" target="_blank">';
  $output .= $link . '</a></p>';

  $block = new stdClass();

  // Initial content is blank.
  $block->title = '';
  $block->content = '';

  // Add in the content.
  $block->content .= $output;

  return $block;
}
