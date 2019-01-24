<?php
/**
 * @file
 * Views definitions for DKAN Comments module.
 */

/**
 * Implements hook_views_data_alter().
 */
function dkan_comments_views_data_alter(&$data) {
  $data['field_data_comment_archived']['comment_status'] = array(
    'group' => 'Comment',
    'title' => t('Comment publish status.'),
    'help' => t('Wether the comment is published, unpublished or archived.'),
    'real field' => 'comment_archived_value',
    'filter' => array(
      'handler' => 'dkan_comments_handler_filter_comment_status',
    ),
  );
}
