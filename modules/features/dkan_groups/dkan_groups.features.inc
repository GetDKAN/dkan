<?php
/**
 * @file
 * dkan_groups.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_groups_ctools_plugin_api() {
  list($module, $api) = func_get_args();
  if ($module == "context" && $api == "context") {
    return array("version" => "3");
  }
  list($module, $api) = func_get_args();
  if ($module == "field_group" && $api == "field_group") {
    return array("version" => "1");
  }
  list($module, $api) = func_get_args();
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function dkan_groups_views_api() {
  return array("version" => "3.0");
}

/**
 * Implements hook_node_info().
 */
function dkan_groups_node_info() {
  $items = array(
    'group' => array(
      'name' => t('Group'),
      'base' => 'node_content',
      'description' => t('Groups allow you to group together datasets under a organisation (for example, the Department of Health) or topic (e.g. Transport, Health) so make it easier for users to browse datasets by theme.

Groups also enable you to assign roles and authorisation to members of the group - i.e. individuals can be given the right to publish datasets from a particular organisation.'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  return $items;
}
