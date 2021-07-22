<?php

use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Preprocess.
 *
 * @param ) ($variables
 *
 * @return [type]
 */
function metastore_preprocess_node__data(&$variables) {
  $json = $variables['node']->get('field_json_metadata')->value;
  $metadata = json_decode($json);
  $rows = [
    ["publisher", $metadata->publisher->name],
    ["contactPoint", render(metastore_format_contactpoint($metadata->contactPoint))],
  ];
  foreach($metadata as $property => $value) {
    if (metastore_use_in_table($property, $value)) {
      $rows[] = [$property, $value];
    }
  }
  $variables['dataset']['description'] = [
    '#markup' => $metadata->description,
  ];
  $variables['dataset']['themes'] = $metadata->theme;
  $variables['dataset']['keywords'] = $metadata->keyword;
  $variables['dataset']['metadata_table'] = [
    '#type' => 'table',
    '#caption' => t('Additional metadata'),
    '#header' => [t("Key"), t("Value")],
    '#rows' => $rows,
  ];

  if (!empty($metadata->distribution)) {
    foreach($metadata->distribution as $d) {
      $rows = [];
      foreach($d as $property => $value) {
        if (ctype_alnum(substr($property, 0, 1))) {
            $rows[] = [$property, $value];
        }
      }
      $variables['dataset']['distributions'][] = [
        '#type' => 'table',
        '#caption' => $d->title,
        '#header' => [t("Key"), t("Value")],
        '#rows' => $rows,
      ];
    }
  }
}

function metastore_format_contactpoint($contactpoint) {
    $str = $contactpoint->fn;
    if ($contactpoint->hasEmail) {
        $str .= ' (' . Link::fromTextAndUrl(t('email'), Url::fromUri($contactpoint->hasEmail))->toString() . ')';
    }
    return ['#markup' => $str];
}

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