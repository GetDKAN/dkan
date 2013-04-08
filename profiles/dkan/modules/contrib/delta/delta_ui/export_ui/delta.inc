<?php

/**
 * @file
 * Plugin definition for CTools Export UI integration.
 */

$plugin = array(
  'schema' => 'delta',
  'access' => 'administer delta',
  'menu' => array(
    'menu prefix' => 'admin/appearance',
    'menu item' => 'delta',
    'menu title' => 'Delta',
    'menu description' => 'Create additional instances of your theme settings.',
  ),  
  'title singular' => t('template'),
  'title singular proper' => t('Template'),
  'title plural' => t('templates'),
  'title plural proper' => t('Templates'),
  'handler' => array(
    'class' => 'delta_export_ui',
    'parent' => 'ctools_export_ui',
  ),  
  'form' => array(
    'settings' => 'delta_ui_form',
  ),  
  'strings' => array(
    'confirmation' => array(
      'revert' => array(
        'information' => t('This action will permanently remove any customizations made to this templates.'),
        'success' => t('The template has been reverted.'),
      ),
      'delete' => array(
        'information' => t('This action will permanently remove the template from your database.'),
        'success' => t('The template has been deleted.'),
      ),
    ),
  ),
);
