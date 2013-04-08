<?php

/**
 * @file
 *  Features hooks for language.
 */

/**
 * Implements of hook_features_api().
 */
function locale_features_api() {
  return array(
    'language' => array(
      'name' => t('Languages'),
      'default_hook' => 'locale_default_languages',
      'feature_source' => TRUE,
      'default_file' => FEATURES_DEFAULTS_INCLUDED,
    ),
  );
}

/**
 * Implements hook_features_export_options().
 */
function language_features_export_options() {
  return locale_language_list('native', TRUE);
}

/**
 * Implements hook_features_export().
 */
function language_features_export($data, &$export, $module_name = '') {
  $export['dependencies']['features'] = 'features';
  $export['dependencies']['locale'] = 'locale';

  $language_list = locale_language_list('native', TRUE);

  foreach ($data as $name) {
    // Only export existing languages.
    if (!empty($language_list[$name])) {
      // Add language to exports.
      $export['features']['language'][$name] = $name;
    }
  }

  // No pipe to return.
  return array();
}

/**
 * Implements hook_features_export_render().
 */
function language_features_export_render($module, $data, $export = NULL) {
  $code = array();
  $code[] = '  $languages = array();';
  $code[] = '';

  $language_list = language_list();

  foreach ($data as $name) {
    // Only render existing languages.
    if (!empty($language_list[$name])) {

      $var = (array) $language_list[$name];
      // Unset javascript hash
      unset($var['javascript']);

      $lang_export = features_var_export($var, '  ');
      $lang_identifier = features_var_export($name);
      $code[] = "  // Exported language: $name.";
      $code[] = "  \$languages[{$lang_identifier}] = {$lang_export};";
    }
  }

  $code[] = '  return $languages;';
  $code = implode("\n", $code);
  return array('locale_default_languages' => $code);
}

/**
 * Implements hook_features_revert().
 */
function language_features_revert($module) {
  return language_features_rebuild($module);
}

/**
 * Implements hook_features_rebuild().
 */
function language_features_rebuild($module) {
  if ($defaults = features_get_default('language', $module)) {
    foreach ($defaults as $key => $language) {
      _features_language_save((object) $language);
    }

    // Set correct language count.
    $enabled_languages = db_select('languages')
      ->condition('enabled', 1)
      ->fields('languages')
      ->execute()
      ->rowCount();
    variable_set('language_count', $enabled_languages);
  }
}

/**
 * Helper function to save the language to database.
 *
 * @see locale_languages_edit_form_submit()
 */
function _features_language_save($language) {

  $current_language = db_select('languages')
    ->condition('language', $language->language)
    ->fields('languages')
    ->execute()
    ->fetchAssoc();

  // Set the default language when needed.
  $default = language_default();

  // Insert new language via api function.
  if (empty($current_language)) {
    locale_add_language($language->language,
                        $language->name,
                        $language->native,
                        $language->direction,
                        $language->domain,
                        $language->prefix,
                        $language->enabled,
                        ($language->language == $default->language));
    // Additional params, locale_add_language does not implement.
    db_update('languages')
      ->fields(array(
        'plurals' => empty($language->plurals) ? 0 : $language->plurals,
        'formula' => empty($language->formula) ? '' : $language->formula,
      ))
      ->condition('language', $language->language)
      ->execute();
  }
  // Update Existing language.
  else {
    // @TODO: get properties from schema.
    $properties = array('language', 'name', 'native', 'direction', 'enabled', 'plurals', 'formula', 'domain', 'prefix', 'weight');

    $fields = array_intersect_key((array) $language, array_flip($properties));
    db_update('languages')
      ->fields($fields)
      ->condition('language', $language->language)
      ->execute();

    // Set the default language when needed.
    $default = language_default();
    if ($default->language == $language->language) {
      variable_set('language_default', (object) $fields);
    }
  }
}
