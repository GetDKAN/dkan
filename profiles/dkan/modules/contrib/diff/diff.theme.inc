<?php

/**
 * @file
 * Themeable function callbacks for diff.module.
 */

/**
 * Theme function to display the revisions formular.
 */
function theme_diff_node_revisions($vars) {
  $form = $vars['form'];
  $output = '';

  // Overview table:
  $header = array(
    t('Revision'),
    array('data' => drupal_render($form['submit']), 'colspan' => 2),
    array('data' => t('Operations'), 'colspan' => 2),
  );
  if (isset($form['info']) && is_array($form['info'])) {
    foreach (element_children($form['info']) as $key) {
      $row = array();
      if (isset($form['operations'][$key][0])) {
        // Note: even if the commands for revert and delete are not permitted,
        // the array is not empty since we set a dummy in this case.
        $row[] = drupal_render($form['info'][$key]);
        $row[] = drupal_render($form['diff']['old'][$key]);
        $row[] = drupal_render($form['diff']['new'][$key]);
        $row[] = drupal_render($form['operations'][$key][0]);
        $row[] = drupal_render($form['operations'][$key][1]);
        $rows[] = array(
          'data' => $row,
          'class' => array('diff-revision'),
        );
      }
      else {
        // The current revision (no commands to revert or delete).
        $row[] = array(
          'data' => drupal_render($form['info'][$key]),
          'class' => array('revision-current'),
        );
        $row[] = array(
          'data' => drupal_render($form['diff']['old'][$key]),
          'class' => array('revision-current'),
        );
        $row[] = array(
          'data' => drupal_render($form['diff']['new'][$key]),
          'class' => array('revision-current'),
        );
        $row[] = array(
          'data' => t('current revision'),
          'class' => array('revision-current'),
          'colspan' => '2',
        );
        $rows[] = array(
          'data' => $row,
          'class' => array('error diff-revision'),
        );
      }
    }
  }
  $output .= theme('table__diff__revisions', array(
    'header' => $header,
    'rows' => $rows,
    'sticky' => FALSE,
    'attributes' => array('class' => 'diff-revisions'),
  ));

  $output .= drupal_render_children($form);
  return $output;
}

/**
 * Theme functions
 */

/**
 * Theme function for a header line in the diff.
 */
function theme_diff_header_line($vars) {
  return '<strong>' . t('Line @lineno', array('@lineno' => $vars['lineno'])) . '</strong>';
}

/**
 * Theme function for a content line in the diff.
 */
function theme_diff_content_line($vars) {
  return '<div>' . $vars['line'] . '</div>';
}

/**
 * Theme function for an empty line in the diff.
 */
function theme_diff_empty_line($vars) {
  return $vars['line'];
}

/**
 * Theme function for inline diff form.
 */
function theme_diff_inline_form($vars) {
  if ($theme = variable_get('diff_theme', 'default')) {
    drupal_add_css(drupal_get_path('module', 'diff') . "/css/diff.{$theme}.css");
  }
  return drupal_render_children($vars['form']);
}

/**
 * Display inline diff metadata.
 */
function theme_diff_inline_metadata($vars) {
  if ($theme = variable_get('diff_theme', 'default')) {
    drupal_add_css(drupal_get_path('module', 'diff') . "/css/diff.{$theme}.css");
  }
  $node = $vars['node'];

  $output = "<div class='diff-inline-metadata clear-block'>";
  $output .= "<div class='diff-inline-byline'>";
  $output .= t('Updated by !name on @date', array(
    '!name' => theme('username', array('account' => user_load($node->revision_uid))),
    '@date' => format_date($node->revision_timestamp, 'small'),
  ));
  $output .= "</div>";
  $output .= "<div class='diff-inline-legend clear-block'>";
  $output .= "<label>" . t('Legend') . "</label>";
  $output .= theme('diff_inline_chunk', array('text' => t('Added'), 'type' => 'add'));
  $output .= theme('diff_inline_chunk', array('text' => t('Changed'), 'type' => 'change'));
  $output .= theme('diff_inline_chunk', array('text' => t('Deleted'), 'type' => 'delete'));
  $output .= "</div>";
  $output .= "</div>";
  return $output;
}

/**
 * Theme a span of changed text in an inline diff display.
 */
function theme_diff_inline_chunk($vars) {
  switch ($vars['type']) {
    case 'add':
      return "<span class='diff-added'>{$vars['text']}</span>";
    case 'change':
      return "<span class='diff-changed'>{$vars['text']}</span>";
    case 'delete':
      return "<span class='diff-deleted'>{$vars['text']}</span>";
    default:
      return $vars['text'];
  }
}
