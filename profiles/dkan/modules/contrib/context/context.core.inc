<?php

/**
 * Implementation of hook_help().
 */
function context_help($path, $arg) {
  switch ($path) {
    case 'admin/help#context':
      $output = file_get_contents(drupal_get_path('module', 'context') . '/README.txt');
      return module_exists('markdown') ? filter_xss_admin(module_invoke('markdown', 'filter', 'process', 0, -1, $output)) : '<pre>' . check_plain($output) . '</pre>';
  }
}

/**
 * Implementation of hook_theme().
 */
function context_theme() {
  $items = array();
  if (!module_exists('block')) {
    $items['block'] = array(
      'render element' => 'elements',
      'template' => 'block',
      'path' => drupal_get_path('module', 'block'),
      'file' => 'block.module',
      'template' => 'block',
    );
  }
  $items['context_block_form'] = array(
    'render element' => 'form',
    'path' => drupal_get_path('module', 'context') . '/theme',
    'file' => 'context_reaction_block.theme.inc',
  );
  $items['context_block_regions_form'] = array(
    'render element' => 'form',
    'path' => drupal_get_path('module', 'context') . '/theme',
    'file' => 'context_reaction_block.theme.inc',
  );
  $items['context_block_editor'] = array(
    'render element' => 'form',
    'path' => drupal_get_path('module', 'context') . '/theme',
    'file' => 'context_reaction_block.theme.inc',
  );
  $items['context_block_browser'] = array(
    'variables' => array('blocks' => array(), 'context' => array()),
    'path' => drupal_get_path('module', 'context') . '/theme',
    'template' => 'context-block-browser',
    'file' => 'context_reaction_block.theme.inc',
  );
  $items['context_block_browser_item'] = array(
    'variables' => array('block' => array()),
    'path' => drupal_get_path('module', 'context') . '/theme',
    'template' => 'context-block-browser-item',
    'file' => 'context_reaction_block.theme.inc',
  );
  $items['context_block_script_placeholder'] = array(
    'variables' => array('text' => NULL),
    'path' => drupal_get_path('module', 'context') . '/theme',
    'file' => 'context_reaction_block.theme.inc',
  );
  return $items;
}

/**
 * Implementation of hook_theme_registry_alter().
 */
function context_theme_registry_alter(&$theme_registry) {
  // Push theme_page() through a context_preprocess to provide
  // context-sensitive menus and variables. Ensure that
  // context_preprocess_page() comes immediately after
  // template_preprocess_page().
  $position = array_search('context_preprocess_page', $theme_registry['page']['preprocess functions']);
  if ($position !== FALSE) {
    unset($theme_registry['page']['preprocess functions'][$position]);
  }
  // Prevent conflict with i18n_menu.
  if (module_exists('i18n_menu')) {
    $position = array_search('i18n_menu_preprocess_page', $theme_registry['page']['preprocess functions']);
  }
  else {
    $position = array_search('template_preprocess_page', $theme_registry['page']['preprocess functions']);
  }
  $position = $position ? $position + 1 : 2;
  array_splice($theme_registry['page']['preprocess functions'], $position, 0, 'context_preprocess_page');
}

/**
 * Implementation of hook_ctools_render_alter().
 * Used to detect the presence of a page manager node view or node form.
 */
function context_ctools_render_alter($info, $page, $data) {
  extract($data);
  if ($page && in_array($task['name'], array('node_view', 'node_edit'), TRUE)) {
    foreach ($contexts as $ctools_context) {
      if (in_array('node', $ctools_context->type) && !empty($ctools_context->data)) {
        context_node_condition($ctools_context->data, $task['name'] === 'node_view' ? 'view' : 'form');
        break;
      }
    }
  }
}

/**
 * Implementation of hook_entity_prepare_view().
 */
function context_entity_prepare_view($prepare, $entity_type) {
  if ($entity_type === 'taxonomy_term' && count($prepare) === 1) {
    $term = reset($prepare);
    if ($term === menu_get_object('taxonomy_term', 2) && $plugin = context_get_plugin('condition', 'taxonomy_term')) {
      $plugin->execute($term, 'view');
    }
  }
}

/**
 * Implementation of hook_node_view().
 */
function context_node_view($node, $view_mode) {
  if ($view_mode === 'full') {
    $object = menu_get_object();
    if (isset($object->nid) && $object->nid === $node->nid) {
      context_node_condition($node, 'view');
    }
  }
}

/**
 * Implementation of hook_form_alter().
 */
function context_form_alter(&$form, $form_state, $form_id) {
  // If the form is an admin for, flag it so that we can force a rebuild if needed.
  if (path_is_admin($_GET['q'])) {
    $form['#submit'][] = 'context_admin_form_submit';
  }
  // Trigger the condition in an after_build function to avoid being skipped
  // when there are validation errors.
  $form['#after_build'][] = 'context_form_alter_node_after_build';
}

/**
 * Form #after_build callback for context_form_alter().
 */
function context_form_alter_node_after_build($form, &$form_state) {
  // Prevent this from firing on admin pages... damn form driven apis...
  if (!empty($form['#node_edit_form']) && arg(0) != 'admin') {
    context_node_condition($form['#node'], 'form');
  }
  return $form;
}

/**
 * Clear out block info cache when an admin area form is submitted.
 */
function context_admin_form_submit(&$form, $form_state) {
  if ($plugin = context_get_plugin('reaction', 'block')) {
    $plugin->rebuild_needed(TRUE);
  }
}

/**
 * Centralized node condition call function for the ever increasing number of
 * ways to get at a node view / node form.
 */
function context_node_condition(&$node, $op) {
  if ($plugin = context_get_plugin('condition', 'node')) {
    $plugin->execute($node, $op);
  }
  if (module_exists('taxonomy')) {
    if ($plugin = context_get_plugin('condition', 'node_taxonomy')) {
      $plugin->execute($node, $op);
    }
  }
  if (module_exists('book')) {
    if ($plugin = context_get_plugin('condition', 'book')) {
      $plugin->execute($node, $op);
    }
    if ($plugin = context_get_plugin('condition', 'bookroot')) {
      $plugin->execute($node, $op);
    }
  }
  // Allow other plugins to easily be triggered on node-related events.
  drupal_alter('context_node_condition', $node, $op);
}

/**
 * Implementation of hook_form_alter() for system_modules_form.
 */
function context_form_system_modules_form_alter(&$form, $form_state) {
  context_invalidate_cache();
}

/**
 * Implementation of hook_form_alter() for user_profile_form.
 */
function context_form_user_profile_form_alter(&$form, $form_state) {
  if ($plugin = context_get_plugin('condition', 'user_page')) {
    $plugin->execute($form['#user'], 'form');
  }
}

/**
 * Implementation of hook_form_alter() for user_register_form.
 */
function context_form_user_register_form_alter(&$form, $form_state) {
  if ($plugin = context_get_plugin('condition', 'user_page')) {
    $plugin->execute($form['#user'], 'register');
  }
}

/**
 * Implementation of hook_form_alter() for comment_form.
 */
function context_form_comment_form_alter(&$form, $form_state) {
  if ($nid = $form['nid']['#value']) {
    $node = node_load($nid);
    context_node_condition($node, 'comment');
  }
}

/**
 * Implementation of hook_views_pre_view().
 */
function context_views_pre_view($view, $display) {
  if ($plugin = context_get_plugin('condition', 'views')) {
    $plugin->execute($view);
  }
  // Support Views overrides of specific entity paths.
  if ($view->display_handler->has_path()) {
    switch ($view->display_handler->get_option('path')) {
      case 'taxonomy/term/%':
        if (($term = taxonomy_term_load(arg(2))) && ($plugin = context_get_plugin('condition', 'taxonomy_term'))) {
          $plugin->execute($term, 'view');
        }
        break;
      case 'node/%':
        if ($node = node_load(arg(1))) {
          context_node_condition($node, 'view');
        }
        break;
      case 'user/%':
        if (($account = user_load(arg(1))) && ($plugin = context_get_plugin('condition', 'user_page'))) {
          $plugin->execute($account, 'view');
        }
        break;
    }
  }
}

/**
 * Implementation of hook_user().
 */
function context_user_view($account, $view_mode) {
  if ($view_mode === 'full' && $plugin = context_get_plugin('condition', 'user_page')) {
    $plugin->execute($account, 'view');
  }
}

/**
 * Implements hook_page_build().
 */
function context_page_build(&$page) {
  module_invoke_all('context_page_condition');
  module_invoke_all('context_page_reaction');
  if ($plugin = context_get_plugin('reaction', 'block')) {
    $plugin->execute($page);
  }

  // See block_page_build. Clear static cache b/c in overlay form submissions
  // hook_page_build can get called more than once per page load.
  drupal_static_reset('context_reaction_block_list');
}

/**
 * THEME FUNCTIONS & RELATED ==========================================
 */

/**
 * Generates an array of links (suitable for use with theme_links)
 * to the node forms of types associated with current active contexts.
 */
function context_links($reset = FALSE) {
  static $links;
  if (!$links || $reset) {
    $contexts = context_active_contexts();
    $active_types = array();
    $conditions = array('node', 'bookroot');
    foreach ($conditions as $condition) {
      foreach ($contexts as $k => $v) {
        if (!empty($v->conditions[$condition]['values'])) {
          $active_types = array_merge($active_types, array_filter($v->conditions[$condition]['values']));
        }
      }
    }

    $links = array();
    if (!empty($active_types)) {
      // Iterate over active contexts
      foreach ($active_types as $type) {
        $add_url = 'node/add/' . str_replace('_', '-', $type);
        $item = menu_get_item($add_url);
        if ($item && $item['access'] && strpos($_GET['q'], $add_url) !== 0) {
          $links[$type] = array('title' => t('Add @type', array('@type' => node_type_get_name($type))), 'href' => $add_url);
        }
      }
    }
    drupal_alter('context_links', $links);
    uasort($links, 'element_sort');
  }
  return $links;
}

/**
 * Implementation of hook_context_page_condition().
 */
function context_context_page_condition() {
  if ($plugin = context_get_plugin('condition', 'menu')) {
    $plugin->execute();
  }
  if ($plugin = context_get_plugin('condition', 'sitewide')) {
    $plugin->execute(1);
  }
  if ($plugin = context_get_plugin('condition', 'context')) {
    $plugin->execute();
  }
}

/**
 * Implementation of hook_context_page_reaction().
 */
function context_context_page_reaction() {
  if ($plugin = context_get_plugin('reaction', 'breadcrumb')) {
    $plugin->execute();
  }
  if ($plugin = context_get_plugin('reaction', 'css_injector')) {
    $plugin->execute();
  }
  if ($plugin = context_get_plugin('reaction', 'debug')) {
    $plugin->execute();
  }
}

/**
 * Implementation of hook_preprocess_page().
 */
function context_preprocess_page(&$vars) {
  if ($plugin = context_get_plugin('reaction', 'menu')) {
    $plugin->execute($vars);
  }
  if ($plugin = context_get_plugin('reaction', 'theme')) {
    $plugin->execute($vars);
  }
  /*
  if ($context_links = context_links()) {
    $vars['context_links'] = theme('links', $context_links);
  }
  else {
    $vars['context_links'] = '';
  }
  */
}

/**
 * Implementation of hook_preprocess_html().
 */
function context_preprocess_html(&$vars) {
  if ($plugin = context_get_plugin('reaction', 'theme_html')) {
    $plugin->execute($vars);
  }
}
