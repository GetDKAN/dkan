<?php

/**
 * @file
 * Theme variables for metadata template.
 */

use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Decode Metadata JSON blob into useable theme variables.
 */
function metastore_preprocess_node__data(&$variables) {
  $json = $variables['node']->get('field_json_metadata')->value;
  $rows = [];
  $metadata = json_decode($json);
  if (property_exists($metadata, 'description')) {
    $variables['dataset']['description'] = $metadata->description;
  }
  if (property_exists($metadata, 'theme')) {
    $variables['dataset']['themes'] = $metadata->theme;
  }
  if (property_exists($metadata, 'keyword')) {
    $variables['dataset']['keywords'] = $metadata->keyword;
  }
  if (property_exists($metadata, 'contactPoint')) {
    $variables['dataset']['contactPoint']["name"] = [
      '#markup' => $metadata->contactPoint->fn,
    ];
    $variables['dataset']['contactPoint']["email"] = [
      '#markup' => $metadata->contactPoint->hasEmail,
    ];
    $formatted_contact_point = metastore_format_contactpoint($metadata->contactPoint);
    $rows = [
      [
        "contactPoint",
        \Drupal::service('renderer')->render($formatted_contact_point),
      ],
    ];
  }

  if (property_exists($metadata, 'accessLevel')) {
    $variables['dataset']['accessLevel'] = [
      '#markup' => $metadata->accessLevel,
    ];
  }
  if (property_exists($metadata, 'identifier')) {
    $variables['dataset']['identifier'] = [
      '#markup' => $metadata->identifier,
    ];
  }
  if (property_exists($metadata, 'issued')) {
    $variables['dataset']['issued'] = $metadata->issued;
  }
  if (property_exists($metadata, 'modified')) {
    $variables['dataset']['modified'] = $metadata->modified;
  }
  if (property_exists($metadata, 'license')) {
    $variables['dataset']['license'] = [
      '#markup' => $metadata->license,
    ];
  }
  if (property_exists($metadata, 'spatial')) {
    $variables['dataset']['spatial'] = [
      '#markup' => $metadata->spatial,
    ];
  }
  if (property_exists($metadata, 'publisher')) {
    $rows[] = ["publisher", $metadata->publisher->name];
    $variables['dataset']['publisher'] = [
      '#markup' => $metadata->publisher->name,
    ];
  }

  foreach ($metadata as $property => $value) {
    if (metastore_use_in_table($property, $value)) {
      $rows[] = [$property, $value];
    }

  }

  $variables['dataset']['metadata_table'] = [
    '#type' => 'table',
    '#caption' => t('Additional metadata'),
    '#header' => [t("Key"), t("Value")],
    '#rows' => $rows,
  ];

  if (!empty($metadata->distribution)) {
    foreach ($metadata->distribution as $d) {
      $rows = [];
      foreach ($d as $property => $value) {
        if (ctype_alnum(substr($property, 0, 1))) {
          $rows[$property] = [
            '#markup' => $value,
          ];
        }
      }
      // Fallback to display file path for link title.
      if ($rows['downloadURL'] && !property_exists($d, 'title')) {
        $rows['title'] = $rows['downloadURL'];
      }
      $variables['dataset']['distributions'][] = $rows;
    }
  }
}

/**
 * Format email into a valid Url object.
 */
function metastore_create_mailto_link($email) {
  $lower_case_email = strtolower($email);
  if (!str_starts_with($lower_case_email, 'mailto:')) {
    $lower_case_email = 'mailto:' . strtolower($email);
  }
  if (Url::fromUri($lower_case_email)) {
    return Url::fromUri($lower_case_email);
  }

  return $lower_case_email;
}

/**
 * Format contact information name and link.
 */
function metastore_format_contactpoint($contactpoint) {
  $str = $contactpoint->fn;
  if ($contactpoint->hasEmail) {
    $mailto_link = metastore_create_mailto_link($contactpoint->hasEmail);
    $str .= ' (' . Link::fromTextAndUrl(t('email'), $mailto_link)->toString() . ')';
  }
  return ['#markup' => $str];
}

/**
 * Build table using only the string data, skipping description and title.
 */
function metastore_use_in_table($property, $value) {
  if (in_array($property, ["description", "title"])) {
    return FALSE;
  }
  if (!ctype_alnum(substr($property, 0, 1))) {
    return FALSE;
  }
  if (is_string($value)) {
    return TRUE;
  }
}
