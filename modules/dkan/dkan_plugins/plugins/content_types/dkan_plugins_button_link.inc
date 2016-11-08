<?php

/**
 * @file
 * Custom button link with color styles in a pane.
 */

$plugin = array(
  'title' => t('Button link'),
  'description' => t('Custom button with color settings.'),
  'icon' => '',
  'single' => TRUE,
  'edit form' => 'dkan_plugins_button_link_content_type_edit_form',
  'render callback' => 'dkan_plugins_button_link_content_type_render',
  'category' => array(t('Custom'), -1),
  'all contexts' => TRUE,
);

/**
 * An edit form for the pane's settings.
 */
function dkan_plugins_button_link_content_type_edit_form($form, &$form_state) {
  $conf = $form_state['conf'];

  $link = $conf['button_link_title'];
  $url = $conf['button_link_url'];
  $color = $conf['button_color'];
  $align = $conf['button_align'];

  hide($form['override_title']);
  hide($form['override_title_text']);
  hide($form['override_title_heading']);
  hide($form['override_title_markup']);
  $form['#attached']['js'] = array(
    libraries_get_path('bgrins-spectrum') . '/spectrum.js',
  );
  $form['#attached']['css'] = array(
    libraries_get_path('bgrins-spectrum') . '/spectrum.css',
  );

  $form['button_link_title'] = array(
    '#title' => 'Link Title',
    '#type' => 'textfield',
    '#field_name' => 'button_link_text',
    '#delta' => 0,
    '#required' => TRUE,
    '#default_value' => isset($link) ? $link : '',
  );
  $form['button_link_url'] = array(
    '#title' => 'Link Url',
    '#type' => 'textfield',
    '#field_name' => 'button_link_url',
    '#delta' => 0,
    '#required' => TRUE,
    '#default_value' => isset($url) ? $url : '',
  );
  $form['button_color'] = array(
    '#title' => 'Button Color',
    '#type' => 'textfield',
    '#default_value' => isset($color) ? $color : '#0A77BD',
    '#attributes' => array('class' => array('spectrum-color-picker sp-replacer')),
  );
  $form['button_align'] = array(
    '#title' => t('Button Alignment'),
    '#type' => 'select',
    '#options' => array(
      'text-left' => t('Left'),
      'text-center' => t('Center'),
      'text-right' => t('Right'),
    ),
    '#default_value' => isset($align) ? $align : 'text-center',
  );
  return $form;
}

/**
 * The submit form stores the data in $conf.
 */
function dkan_plugins_button_link_content_type_edit_form_submit($form, &$form_state) {
  $keys = array(
    'button_link_title',
    'button_link_url',
    'button_color',
    'button_align',
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
function dkan_plugins_button_link_content_type_render($subtype, $conf, $panel_args, $context = NULL) {

  $link   = isset($conf['button_link_title']) ? $conf['button_link_title'] : '';
  $url    = isset($conf['button_link_url']) ? $conf['button_link_url'] : '';
  $color  = isset($conf['button_color']) ? $conf['button_color'] : '#0A77BD';
  $align  = isset($conf['button_align']) ? $conf['button_align'] : '';
  $output = '';

  $output .= '<a href="' . $url . '" ';
  $output .= 'style="margin: 1em 0; color: #ffffff; background-color:' . check_plain($color) . '; border-color:' . check_plain($color) . '" class="btn btn-default"> ';
  $output .= check_plain($link);
  $output .= '</a>';

  $block = new stdClass();

  // Initial content is blank.
  $block->title = '';
  $block->content = '';

  // Add in the content.
  $block->content .= $output;

  return $block;
}
