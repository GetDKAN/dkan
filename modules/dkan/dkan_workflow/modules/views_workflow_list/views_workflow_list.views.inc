<?php

/**
 * Implementation of hook_views_plugins()
 */
function views_workflow_list_views_plugins() {
  $plugin = array(
    'style' => array(
      'workflow_list' => array(
        'title' => t('Workflow List'),
        'help' => t('Display the results as a tree'),
        'handler' => 'views_workflow_list_plugin_style',
        'theme' => 'views_workflow_list',
        'uses options' => TRUE,
        'uses row plugin' => TRUE,
        'uses fields' => TRUE,
        'uses grouping' => FALSE,
        'type' => 'normal',
        'parent' => 'list',
      ),
    ),
  );

  return $plugin;
}

