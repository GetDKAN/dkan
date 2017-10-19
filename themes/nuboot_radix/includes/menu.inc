<?php

/**
 * @file
 * Theme and preprocess functions for menus.
 */

/**
 * Overrides theme_menu_local_tasks().
 */
function nuboot_radix_menu_local_tasks(&$variables) {
  $output = '';

  if (!empty($variables['primary'])) {
    $variables['primary']['#prefix'] = '<h2 class="element-invisible">' . t('Primary tabs') . '</h2>';
    $variables['primary']['#prefix'] .= '<ul class="tabs--primary nav nav-pills">';
    $variables['primary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['primary']);
  }

  if (!empty($variables['secondary'])) {
    $variables['secondary']['#prefix'] = '<h2 class="element-invisible">' . t('Secondary tabs') . '</h2>';
    $variables['secondary']['#prefix'] .= '<ul class="tabs--secondary pagination pagination-sm">';
    $variables['secondary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['secondary']);
  }

  return $output;
}

/**
 * Overrides theme_menu_local_task().
 */
function nuboot_radix_menu_local_task($variables) {
  $link = $variables['element']['#link'];
  $link_text = $link['title'];
  $icon_type = '';
  if (isset($link['path'])) {
    switch ($link['path']) {
      case 'node/%/edit':
        $icon_type = 'edit';
        break;

      case 'node/%/view':
        $icon_type = 'eye';
        break;

      case 'node/%/resource':
        $icon_type = 'plus';
        break;

      case 'node/%/datastore':
        $icon_type = 'cogs';
        break;

      case 'node/%/datastore/import':
        $icon_type = 'refresh';
        break;

      case 'node/%/datastore/drop':
        $icon_type = 'trash-o';
        break;

      case 'node/%/datastore/unlock':
        $icon_type = 'unlock';
        break;

      case 'node/%/download':
        $icon_type = 'download';
        break;

      case 'node/%/dataset':
        $icon_type = 'caret-left';
        break;

      case 'node/%/api':
        $icon_type = 'flask';
        break;

      case 'node/%/group':
        $icon_type = 'users';
        break;

      case 'node/%/members':
        $icon_type = 'user';
        break;

      case 'node/%/revisions':
      case 'node/%/moderation':
        $icon_type = 'folder-open-o';
        break;

      case 'node/%/datastore/delete-items':
        $icon_type = 'eraser';
        break;

      case 'node/%/datasets':
        $icon_type = 'cubes';
        break;

      case 'node/%/harvest-preview':
        $icon_type = 'check-circle';
        break;

      case 'node/%/harvest-events':
        $icon_type = 'calendar';
        break;

      case 'node/%/harvest-errors':
        $icon_type = 'exclamation-circle';
        break;

      default:
        $icon_type = '';
        break;
    }
  }
  $icon = '<i class="fa fa-lg fa-' . $icon_type . '"></i> ';
  $link_text = $icon . $link_text;
  $link['localized_options']['html'] = TRUE;

  if (!empty($variables['element']['#active'])) {
    // Add text to indicate active tab for non-visual users.
    $active = '<span class="element-invisible">' . t('(active tab)') . '</span>';

    // If the link does not contain HTML already, check_plain() it now.
    // After we set 'html'=TRUE the link will not be sanitized by l().
    if (empty($link['localized_options']['html'])) {
      $link['title'] = check_plain($link['title']);
    }
    $link['localized_options']['html'] = TRUE;
    $link_text = t('!local-task-title!active', array('!local-task-title' => $link['title'], '!active' => $active));

    $icon = '<i class="fa fa-lg fa-' . $icon_type . '"></i> ';
    $link_text = $icon . $link_text;
    // Ensure the HTML in $link_text is not escaped.
    $link['localized_options']['html'] = TRUE;
  }
  return '<li' . (!empty($variables['element']['#active']) ? ' class="active"' : '') . '>' . l($link_text, $link['href'], $link['localized_options']) . "</li>\n";
}
