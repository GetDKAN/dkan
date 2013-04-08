<?php
/**
 * @file
 *
 * Is an alter plugin for defaultcontent
 *
 * Handles the exporting and importing webform data on a node
 */

$plugin = array();

/**
 * Handles the export of webform data to export
 *
 * Also export captcha data if captcha is enabled
 * this require a captcha patch for exporting
 */
function webform_export_alter(&$node, &$export) {
  if (isset($node->webform)) {
    unset($node->webform['nid']);
    foreach ($node->webform['components'] as $key => $component) {
      unset($node->webform['components'][$key]['nid']);
      unset($node->webform['components'][$key]['cid']);
    }
    $node->webform['components'] = array_values($node->webform['components']);
    $export->webform = $node->webform;
    if (module_exists('captcha')) {
      module_load_include('inc', 'captcha');
      $form_id = 'webform_client_form_' . $node->nid;
      if ($captcha = captcha_get_form_id_setting($form_id, TRUE)) {
        unset($node->captcha['form_id']);
        $export->captcha = $captcha;
      }
    }
  }
}

/**
 * Handles the importing of webform data after the node is created
 */
function webform_post_import($node) {
  if (module_exists('captcha')) {
    module_load_include('inc', 'captcha');
    if (isset($node->nid) && isset($node->captcha)) {
      $form_id = 'webform_client_form_' . $node->nid;
      $node->captcha = is_array($node->captcha) ? (object) $node->captcha : $node->captcha;
      captcha_set_form_id_setting($form_id, $node->captcha);
    }
  }
}
