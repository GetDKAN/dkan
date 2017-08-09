<?php

/**
 * @file
 * dkan_dataset_content_types.license_field.inc
 */

/**
 * Retrieves a list of key | value pairs for license field.
 */
function dkan_dataset_content_types_license_allowed_values() {
  $subscribed_values = dkan_dataset_content_types_license_subscribed_values();
  $allowed_values = array();
  foreach ($subscribed_values as $key => $value) {
    $allowed_values[$key] = $value['label'];
  }
  return $allowed_values;
}

/**
 * Retrieves a list of subscribed values for license field.
 */
function dkan_dataset_content_types_license_subscribed_values() {
  $modules = module_implements('license_subscribe');
  $subscribed_values = array();
  // Get a list of licenses to display.
  foreach ($modules as $module) {
    $function = $module . '_license_subscribe';
    $values = $function();
    foreach ($values as $key => $value) {
      // Make sure other module didn't provide the same license.
      if (!isset($subscribed_values[$key]) && isset($value['label'])) {
        $subscribed_values[$key] = $value;
      }
    }
  }
  // Get a list of licenses to remove from display.
  $modules = module_implements('license_unsubscribe');
  foreach ($modules as $module) {
    $function = $module . '_license_unsubscribe';
    $values = $function();
    foreach ($values as $key) {
      // Unsubscribe item if present in subscribed values.
      if (isset($subscribed_values[$key])) {
        unset($subscribed_values[$key]);
      }
    }
  }

  // Remove licenses with empty URIs if strict POD validation is enabled.
  foreach ($subscribed_values as $key => $value) {
    // Skip licenses with empty URIs if strict POD validation is enabled.
    if (variable_get('dkan_dataset_form_pod_validation') && !isset($value['uri'])) {
      unset($subscribed_values[$key]);
    }
  }

  return $subscribed_values;
}

/**
 * Implements hook_license_subscribe().
 */
function dkan_dataset_content_types_license_subscribe() {
  return array(
    "cc-by" => array(
      "label" => "Creative Commons Attribution",
      "uri" => "https://creativecommons.org/licenses/by/4.0/",
    ),
    "cc-by-sa" => array(
      "label" => "Creative Commons Attribution Share-Alike",
      "uri" => "https://creativecommons.org/licenses/by-sa/4.0/",
    ),
    "cc-zero" => array(
      "label" => "Creative Commons CCZero",
      "uri" => "https://creativecommons.org/publicdomain/zero/1.0/",
    ),
    "cc-nc"  => array(
      "label" => "Creative Commons Non-Commercial (2.5)",
      "uri" => "https://creativecommons.org/licenses/by-nc/2.5/",
    ),
    "cc-by-nc-nd" => array(
      "label" => "Attribution NonCommercial NoDerivatives 4.0 International",
      "uri" => "https://creativecommons.org/licenses/by-nc-nd/4.0/",
    ),
    "gfdl" => array(
      "label" => "GNU Free Documentation License",
      "uri" => "https://www.gnu.org/licenses/fdl.html",
    ),
    "odc-by" => array(
      "label" => "Open Data Commons Attribution License",
      "uri" => "https://opendatacommons.org/licenses/by/1.0/",
    ),
    "odc-odbl" => array(
      "label" => "Open Data Commons Open Database License (ODbL)",
      "uri" => "https://opendatacommons.org/licenses/odbl/1.0/",
    ),
    "odc-pddl" => array(
      "label" => "Open Data Commons Public Domain Dedication and Licence (PDDL)",
      "uri" => "http://opendefinition.org/licenses/odc-pddl/",
    ),
    "uk-ogl" => array(
      "label" => "UK Open Government Licence (OGL)",
      "url" => 'https://www.nationalarchives.gov.uk/doc/open-government-licence/version/2/',
    ),
    "other-at" => array(
      "label" => "Other (Attribution)",
    ),
    "other-nc" => array(
      "label" => "Other (Non-Commercial)",
    ),
    "other-closed" => array(
      "label" => "Other (Not Open)",
    ),
    "other-open" => array(
      "label" => "Other (Open)",
    ),
    "other-pd" => array(
      "label" => "Other (Public Domain)",
    ),
    "notspecified" => array(
      "label" => "License Not Specified",
    ),
  );
}
