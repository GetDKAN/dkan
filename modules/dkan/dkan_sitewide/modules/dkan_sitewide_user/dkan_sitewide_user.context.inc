<?php
/**
 * @file
 * dkan_sitewide_user.context.inc
 */

/**
 * Implements hook_context_default_contexts().
 */
function dkan_sitewide_user_context_default_contexts() {
  $export = array();

  $context = new stdClass();
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
  $context->api_version = 3;
  $context->name = 'profile';
  $context->description = 'User profile';
  $context->tag = 'Profile';
  $context->conditions = array(
    'user_page' => array(
      'values' => array(
        'view' => 'view',
      ),
      'options' => array(
        'mode' => 'all',
      ),
    ),
  );
  $context->reactions = array(
    'block' => array(
      'blocks' => array(
        'views-user_profile_fields-block' => array(
          'module' => 'views',
          'delta' => 'user_profile_fields-block',
          'region' => 'sidebar_first',
          'weight' => '-10',
        ),
        'system-powered-by' => array(
          'module' => 'system',
          'delta' => 'powered-by',
          'region' => 'sidebar_first',
          'weight' => '-9',
        ),
      ),
    ),
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t('Profile');
  t('User profile');
  $export['profile'] = $context;

  return $export;
}
