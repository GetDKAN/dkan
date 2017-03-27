<?php

/**
 * @file
 * Theme settings.
 */

/**
 * Implements theme_settings().
 */
function nuboot_radix_form_system_theme_settings_alter(&$form, &$form_state) {
  global $theme;
  // Ensure this include file is loaded when the form is rebuilt from the cache.
  $form_state['build_info']['files']['form'] = drupal_get_path('theme', 'nuboot_radix') . '/theme-settings.php';

  // Additional theme settings.
  $form['copyright'] = array(
    '#title' => t('Copyright'),
    '#type' => 'fieldset',
  );

  $copyright = theme_get_setting('copyright', 'nuboot_radix');
  $form['copyright']['copyright'] = array(
    '#title' => t('Footer text'),
    '#type' => 'text_format',
    '#format' => 'html',
    '#default_value' => isset($copyright['value']) ? $copyright['value'] : t('Powered by <a href="http://getdkan.com/">DKAN</a>, a project of <a href="http://granicus.com">Granicus</a>'),
  );

  $display_login_menu = (theme_get_setting('display_login_menu', 'nuboot_radix') === NULL) ? 1 : theme_get_setting('display_login_menu', 'nuboot_radix');

  $form['theme_settings']['display_login_menu'] = array(
    '#type' => 'checkbox',
    '#title' => t('Display login menu'),
    '#default_value' => $display_login_menu,
  );

  // Hero fieldset.
  $form['hero'] = array(
    '#type' => 'fieldset',
    '#title' => t('Hero Unit'),
    '#group' => 'general',
  );
  // Upload field.
  $hero = theme_get_setting('hero_file', 'nuboot_radix');
  $form['hero']['hero_file'] = array(
    '#type' => 'managed_file',
    '#title' => t('Upload a new photo for the hero section background'),
    '#description' => t('<p>The hero unit is the large featured area located on the front page.
      This theme supplies a default background image for this area. You may upload a different
      photo here and it will replace the default background image.</p><p>Max. file size: 2 MB
      <br>Recommended pixel size: 1920 x 400<br>Allowed extensions: .png .jpg .jpeg</p>'),
    '#required' => FALSE,
    '#upload_location' => file_default_scheme() . '://theme/',
    '#default_value' => !empty($hero) ? $hero : NULL,
    '#upload_validators' => array(
      'file_validate_extensions' => array('gif png jpg jpeg'),
    ),
  );

  // Solid color background.
  $form['hero']['background_option'] = array(
    '#type' => 'textfield',
    '#title' => t('Solid color option'),
    '#description' => t('<p>Enter a hex value here to use a solid background color rather than an image in the hero unit. Make sure the image field above is empty.'),
    '#required' => FALSE,
    '#default_value' => theme_get_setting('background_option', 'nuboot_radix'),
    '#element_validate' => array('_background_option_setting'),
  );

  // Add svg logo option.
  $form['logo']['settings']['svg_logo'] = array(
    '#type' => 'managed_file',
    '#title' => t('Upload an .svg version of your logo'),
    '#description' => t('<p>Be sure to also add a .png version of your logo with the <em>Upload logo image</em> field above for older browsers that do not support .svg files. Both files should have the same name, only the suffix should change (i.e. logo.png & logo.svg).</p>'),
    '#required' => FALSE,
    '#upload_location' => file_default_scheme() . '://theme/',
    '#default_value' => theme_get_setting('svg_logo', 'nuboot_radix'),
    '#upload_validators' => array(
      'file_validate_extensions' => array('svg'),
    ),
  );

  //Allow alter basic site information instead use admin/config/system/site-information
  //We have a lot information into page site-information we don't want to show site managers
  $form['site_information'] = array(
    '#type' => 'fieldset',
    '#title' => t('Site details'),
  );
  $form['site_information']['site_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Site name'),
    '#default_value' => variable_get('site_name', 'Drupal'),
    '#required' => TRUE
  );
  $form['site_information']['site_slogan'] = array(
    '#type' => 'textfield',
    '#title' => t('Slogan'),
    '#default_value' => variable_get('site_slogan', ''),
    '#description' => t("How this is used depends on your site's theme."),
  );
  $form['site_information']['site_mail'] = array(
    '#type' => 'textfield',
    '#title' => t('E-mail address'),
    '#default_value' => variable_get('site_mail', ini_get('sendmail_from')),
    '#description' => t("The <em>From</em> address in automated e-mails sent during registration and new password requests, and other notifications. (Use an address ending in your site's domain to help prevent this e-mail being flagged as spam.)"),
    '#required' => TRUE,
  );

  $form['#submit'][] = $theme . '_hero_system_theme_settings_form_submit';
  $form['#submit'][] = $theme . '_site_information_theme_settings_form_submit';

  // Return the additional form widgets.
  return $form;
}

/**
 * Helper function to validate background color field.
 */
function _background_option_setting($element, &$form, &$form_state) {
  if (!empty($element['#value'])) {
    $hex = $element['#value'];
    // Must be a string.
    $valid = is_string($hex);
    // Hash prefix is optional.
    $hex = ltrim($hex, '#');
    // Must be either RGB or RRGGBB.
    $length = strlen($hex);
    $valid = $valid && ($length === 3 || $length === 6);
    // Must be a valid hex value.
    $valid = $valid && ctype_xdigit($hex);
    if ($valid) {
      return;
    }
    else {
      form_error($element, t('Must be a valid hexadecimal CSS color value.'));
    }
  }
}

/**
 * Submit function for theme settings form information.
 */
function nuboot_radix_site_information_theme_settings_form_submit(&$form, &$form_state) {
  variable_set('site_name', $form_state['values']['site_name']);
  variable_set('site_slogan', $form_state['values']['site_slogan']);
  variable_set('site_mail', $form_state['values']['site_mail']);
}

/**
 * Submit function for theme settings form.
 */
function nuboot_radix_hero_system_theme_settings_form_submit(&$form, &$form_state) {
  if ($form_state['values']['hero_file']) {
    $fid = $form_state['values']['hero_file'];
    _nuboot_radix_file_set_permanent($fid);
  }
  if ($form_state['values']['svg_logo']) {
    $fid = $form_state['values']['svg_logo'];
    _nuboot_radix_file_set_permanent($fid);
  }
}

/**
 * Sets file to FILE_STATUS_PERMANENT so it won't be erased by cron.
 */
function _nuboot_radix_file_set_permanent($fid) {
  cache_clear_all('nuboot_radix_hero_file_uri', 'cache');
  $file = file_load($fid);
  $file->status = FILE_STATUS_PERMANENT;
  file_save($file);
  file_usage_add($file, 'theme', 'file', $fid);
  nuboot_file_insert($file);
}

/**
 * Implements hook_file_insert().
 */
function nuboot_file_insert($file) {
  $file->filename = str_replace(' ', '-', $file->filename);
  $file->filename = preg_replace("/[^\-.a-zA-Z0-9]/", "", $file->filename);
  $name = 'public://' . $file->filename;
  file_move($file, $name, 'FILE_EXIST_REPLACE');
}
