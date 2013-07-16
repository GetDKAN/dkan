<?php

/**
 * @file
 * Builds placeholder replacement tokens for uuid-related data.
 */

/**
 * Implements hook_token_info().
 */
function uuid_token_info() {
  $tokens = array();
  foreach (entity_get_info() as $entity_type => $info) {
    if (isset($info['uuid']) && $info['uuid'] == TRUE && !empty($info['entity keys']['uuid'])) {
      $token_type = !empty($info['token type']) ? $info['token type'] : $entity_type;
      $tokens['tokens'][$token_type][$info['entity keys']['uuid']] = array(
        'name' => t('@entity_type UUID', array('@entity_type' => $info['label'])),
        'description' => t('The universally unique ID of the @entity', array('@entity' => drupal_strtolower($info['label']))),
      );
      if (!empty($info['entity keys']['revision uuid'])) {
        $tokens['tokens'][$token_type][$info['entity keys']['revision uuid']] = array(
          'name' => t('@entity_type revision UUID', array('@entity_type' => $info['label'])),
          'description' => t('The universally unique revision ID of the @entity', array('@entity' => drupal_strtolower($info['label']))),
        );
      }
    }
  }
  return $tokens;
}

/**
 * Implements hook_token_info_alter()
 */
function uuid_token_info_alter(&$data) {
  foreach (entity_get_info() as $entity_type => $info) {
    if (isset($info['uuid']) && $info['uuid'] == TRUE && !empty($info['entity keys']['uuid'])) {
      $token_type = !empty($info['token type']) ? $info['token type'] : $entity_type;
      if (empty($data['types'][$token_type])) {
        unset($data['tokens'][$token_type]);
      }
    }
  }
}

/**
 * Implements hook_tokens().
 */
function uuid_tokens($token_type, $tokens, $data = array(), $options = array()) {
  $replacements = array();
  if ($token_type == 'entity') {
    $info = entity_get_info($data['entity_type']);
    if (isset($info['uuid']) && $info['uuid'] == TRUE && !empty($info['entity keys']['uuid']) && !empty($tokens[$info['entity keys']['uuid']])) {
      $replacements[$tokens[$info['entity keys']['uuid']]] = $data['entity']->{$info['entity keys']['uuid']};
      if (!empty($info['entity keys']['revision uuid']) && !empty($tokens[$info['entity keys']['revision uuid']])) {
        $replacements[$tokens[$info['entity keys']['revision uuid']]] = $data['entity']->{$info['entity keys']['revision uuid']};
      }
    }
  }
  return $replacements;
}
