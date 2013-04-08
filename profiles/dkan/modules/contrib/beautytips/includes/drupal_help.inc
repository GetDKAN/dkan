<?php

function beautytips_drupal_help_admin_info() {
  $form['beautytips_help'] = array(
    '#type' => 'fieldset',
    '#title' => 'Help Link Tooltips',
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['beautytips_help']['beautytips_drupal_help'] = array(
    '#type' => 'checkbox',
    '#title' => 'Display Help link popups',
    '#default_value' => variable_get('beautytips_drupal_help', FALSE),
  );
  if (module_exists('advanced_help')) {
    $form['beautytips_help']['beautytips_advanced_help'] = array(
      '#type' => 'checkbox',
      '#title' => 'Display Advanced Help link popups',
      '#default_value' => variable_get('beautytips_advanced_help', FALSE),
    );
  }
  return $form;
}

/**
 * Implementation of hook_menu_alter()
 */
function beautytips_drupal_help_menu_change(&$items) {
  if (variable_get('beautytips_drupal_help', FALSE)) {
    $items['admin/help']['page callback'] = 'beautytips_drupal_help_main';
    $items['admin/help']['file path'] = drupal_get_path('module', 'beautytips') . '/includes';
    $items['admin/help']['file'] = 'drupal_help.inc';
  }
}

/**
 * Menu callback; prints a page listing a glossary of Drupal terminology.
 *  Beautytips added for the help links
 */
function beautytips_drupal_help_main() {
  require_once drupal_get_path('module', 'help') . '/help.admin.inc';
  //  Add in beautytips on page - admin/help
  $options['bt_drupal_help_page'] = array(
    'cssSelect' => '.help-items li a',
    'ajaxPath' => array("$(this).attr('href')", '.content'),
    'trigger' => array('mouseover', 'click'),
    'width' => 350,
  );
  beautytips_add_beautytips($options);
  return help_main();
}

/**
 * Implementation of hook_theme_registry_alter
 */
function beautytips_drupal_help_theme_change(&$theme_registry) {
  if (module_exists('help') && variable_get('beautytips_drupal_help', FALSE)) {
    // Override theme_more_help_link for more-help stuff
    $theme_registry['more_help_link']['function'] = 'theme_beautytips_drupal_help_more_help_link';
    $theme_registry['more_help_link']['file'] = drupal_get_path('module', 'beautytips') . '/includes/drupal_help.inc';
  }
  if (module_exists('advanced_help') && variable_get('beautytips_advanced_help', FALSE)) {
    // Override theme_advanced_help_topic for advanced help
    $theme_registry['advanced_help_topic']['function'] = 'theme_beautytips_advanced_help_topic';
    $theme_registry['advanced_help_topic']['file'] = drupal_get_path('module', 'beautytips') . '/includes/drupal_help.inc';
  }
}

/**
 * Returns code that emits the 'more help'-link.
 * Overrides theme_more_help_link
 */
function theme_beautytips_drupal_help_more_help_link($variables) {
  $options = array();
  $options['bt_drupal_help'] = array(
    'cssSelect' => '.more-help-link a',
    'ajaxPath' => array("$(this).attr('href')", '#content'),
    'trigger' => array(0 => 'mouseover', 1 => 'click'),
    'width' => 600,
  );
  beautytips_add_beautytips($options);
  return theme_more_help_link($variables);
}

/**
 * Display a help icon with a link to view the topic in a popup.
 *
 * @param $module
 *   The module that owns this help topic.
 * @param $topic
 *   The identifier for the topic
 * @param $type
 *   - 'icon' to display the question mark icon
 *   - 'title' to display the topic's title
 *   - any other text to display the text. Be sure to t() it!
 */
function theme_beautytips_advanced_help_topic(&$variables) {
  // Add bt to advanced-help links
  $options['bt_advanced_help'] = array(
    'cssSelect' => 'a.advanced-help-link',
    'ajaxPath' => array("$(this).attr('href')", '#content-content:not(#content-content.help-navigation)'),
    'trigger' => array('mouseover', 'click'),
    'width' => 380,
  );
  beautytips_add_beautytips($options);
  return theme_advanced_help_topic($variables);
}

