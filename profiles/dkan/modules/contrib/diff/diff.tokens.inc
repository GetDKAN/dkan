<?php

/**
 * @file
 * Builds placeholder replacement tokens for diff-related data.
 */

/**
 * Implements hook_token_info().
 */
function diff_token_info() {
  $node['diff'] = array(
    'name' => t('Latest differences'),
    'description' => t('The differences between the current revision and the previous revision of this node.'),
  );
  $node['diff-markdown'] = array(
    'name' => t('Latest differences (marked down)'),
    'description' => t('The differences between the current revision and the previous revision of this node, but with a marked-down form of each revision used for comparison.'),
  );

  return array(
    'tokens' => array('node' => $node),
  );
}

/**
 * Implements hook_tokens().
 */
function diff_tokens($type, $tokens, array $data = array(), array $options = array()) {
  $sanitize = !empty($options['sanitize']);

  $replacements = array();

  if ($type == 'node' && !empty($data['node'])) {
    $node = $data['node'];
    foreach ($tokens as $name => $original) {
      switch ($name) {
        // Basic diff standard comparison information.
        case 'diff':
        case 'diff-markdown':
          $revisons = node_revision_list($node);
          if (count($revisons) == 1) {
            $replacements[$original] = t('(No previous revision available.)');
          }
          else {
            module_load_include('inc', 'diff', 'diff.pages');
            $old_vid = _diff_get_previous_vid($revisons, $node->vid);
            $state = $name == 'diff' ? 'raw' : 'raw_plain';
            $build = diff_diffs_show($node, $old_vid, $node->vid, $state);
            unset($build['diff_table']['#rows']['states']);
            unset($build['diff_table']['#rows']['navigation']);
            unset($build['diff_preview']);

            $output = drupal_render_children($build);
            $replacements[$original] = $sanitize ? check_plain($output) : $output;
          }
          break;

      }
    }
  }
  return $replacements;
}
