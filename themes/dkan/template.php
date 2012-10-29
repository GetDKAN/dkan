<?php

/**
 * @file
 * Customizations for DKAN.
 */

/**
 * Implements theme_breadcrumb().
 */
function dkan_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];
  $contexts = array();

  if (!empty($breadcrumb)) {
    // Custom breadcrumb elements for specific contexts.
    if (module_exists('context')) {
      $contexts = context_active_contexts();
    }
    if (isset($contexts['dataset']) || isset($contexts['resource'])) {
      $node = menu_get_object();
      $first = array_slice($breadcrumb, 0, 1, true);
      $count = count($breadcrumb);
      $rest = array_slice($breadcrumb, 1, count($breadcrumb) -1, true) + array($count + 1 => '<strong>' . $node->title . '</strong>');
      $datasets = array($count => '<a href="/dataset">Datasets</a>');
      $breadcrumb =  $first + $datasets + $rest;
    }
    if (isset($contexts['dataset-create'])) {
      $first = array_slice($breadcrumb, 0, 1, true);
      $count = count($breadcrumb);
      $datasets = array($count => l('Datasets', 'datasets'));
      $rest =  array($count + 1 => l('Create Dataset', 'node/add/dataset', array('class' => array('active'))));
      $breadcrumb =  $first + $datasets + $rest;
    }

    $output = '<h2 class="element-invisible">' . t('You are here') . '</h2>';

    $crumbs = '<ul class="breadcrumb">';

    foreach($breadcrumb as $value) {
      $crumbs .= '<li>'.$value.'</li>';
    }
    $crumbs .= '</ul>';
    return $crumbs;
  }
}

/**
 * Implements template_preprocess_zone().
 */
function dkan_preprocess_zone(&$vars) {
  if (module_exists('context')) {
    $contexts = context_active_contexts();
    // Create a template suggestion if we are in the dataset context.
    if ($vars['zone'] == 'content' && isset($contexts['dataset'])) {
      $vars['theme_hook_suggestions'][] = 'zone__content__dataset';
    }
    elseif ($vars['zone'] == 'content' && isset($contexts['resource'])) {
      $vars['theme_hook_suggestions'][] = 'zone__content__resource';
    }
  }
}

/**
 * Implements template_preprocess_zone().
 */
function dkan_process_zone(&$vars) {
  if ($vars['zone'] == 'content') {
    $node = menu_get_object();
    $vars['actions'] = '';
    $action_items = array();
    // This is pretty sloppy but will do for now. If other profiles use these
    // faux tabs we should build a real build function and assign weights.
    if (isset($node)) {
      if ($node->type == 'resource') {
        $action_items['items'][] = l('<i class="icon-large icon-caret-left"></i> Back to dataset', 'node/' . $node->book['bid'], array('html' => TRUE, 'attributes' => array('class' => array('btn'))));
      }
      if (node_access('update', $node->nid)) {
        $action_items['items'][] = l('<i class="icon-large icon-wrench"></i> Edit', 'node/' . $node->nid . '/edit', array('html' => TRUE, 'attributes' => array('class' => array('btn'))));
        if (isset($node) && $node->type == 'dataset') {
          $action_items['items'][] = l('<i class="icon-large icon-plus"></i> Add Resource', 'node/add/resource', array('html' => TRUE, 'attributes' => array('class' => array('btn')), 'query' => array('dataset' => $node->nid)));
        }
      }
      if ($node->type == 'resource' && isset($node->field_upload)) {
        $url = file_create_url($node->field_upload[$node->language][0]['uri']);
        $action_items['items'][] = l('<i class="icon-large icon-download"></i> Download', $url, array('html' => TRUE, 'attributes' => array('class' => array('btn btn-primary resource-url-analytics resource-type-file'))));
      }
    }
    $actions = theme('item_list', $action_items);
    $vars['actions'] = $actions;
  }
}
/**
 * Implements template_preprocess_page.
 */
function dkan_preprocess_node(&$vars) {
}
/**
 * Implements template_preprocess_page.
 */
function dkan_preprocess_page(&$vars) {
  if ($vars['user']->uid != 2 && isset($vars['node'])) {
    $vars['tabs'] = '';
  }
  // Custom breadcrumb elements for specific contexts.
  if (module_exists('context')) {
    $contexts = context_active_contexts();
  }
  if (isset($contexts['dataset-create']) || isset($contexts['resource-create']) || isset($contexts['dataset']) || isset($contexts['resource'])) {
    // Remove title on dataset edit and creation pages.
    $vars['title'] = '';
  }
}

/**
 * Implments template_preprocess_block().
 */
function dkan_preprocess_block(&$vars) {

  // Custom breadcrumb elements for specific contexts.
  if ($vars['block_html_id'] ==  'block-system-main') {
    if (module_exists('context')) {
      $contexts = context_active_contexts();
    }
    if (isset($contexts['dataset-create'])) {
      $stages = dkan_create_stages('dataset-create');
      $vars['content'] = $stages . $vars['content'];
    }
    if (isset($contexts['resource-create'])) {
      $query = drupal_get_query_parameters();
      $stages = dkan_create_stages('resource-create', $query['dataset']);
      $vars['content'] = $stages . $vars['content'];
    }
    if (isset($contexts['resource-edit'])) {
      $stages = dkan_create_stages('resource-edit', $vars['elements']['#node']->book['bid'], $vars['elements']['#node']->nid);
      $vars['content'] = $stages . $vars['content'];
    }
    if (isset($contexts['dataset-edit'])) {
      if ($query = drupal_get_query_parameters()) {
        if (!isset($query['addtional'])) {
          $stages = dkan_create_stages('dataset-edit', $vars['elements']['#node']->nid);
          $vars['content'] = $stages . $vars['content'];
        }
      }
    }
  }
}

/**
 * Creates the part on the node edit form that says what stage you are on.
 */
function dkan_create_stages($op, $dataset_nid = NULL, $resource_nid = NULL) {
  $stages = '';
  if ($op == 'resource-edit' || $op == 'resource-create') {
    $stages = '<ol class="stages stage-3">
      <li class="first complete">
          <button class="highlight" name="save" value="go-dataset" type="submit">' . l('Edit dataset', 'node/' . $dataset_nid . '/edit') . '</button>
      </li>
      <li class="middle active">
          <span class="highlight">Add data</span>
      </li>
      <li class="last complete">
          <button class="highlight" name="save" value="go-metadata" type="submit">' . l('Additional data', 'node/' . $dataset_nid . '/edit', array('query' => array('additional' => TRUE))) . '</button>
      </li>
    </ol>';
  }
  if ($op == 'dataset-additional') {
    $stages = '<ol class="stages stage-3">
      <li class="first complete">
          <button class="highlight" name="save" value="go-dataset" type="submit">' . l('Edit dataset', 'node/' . $dataset_nid . '/edit') . '</button>
      </li>
      <li class="middle complete">
          <span class="highlight">Add data</span>
      </li>
      <li class="last active">
          <button class="highlight" name="save" value="go-metadata" type="submit">' . l('Additional data', 'node/' . $dataset_nid . '/edit', array('query' => array('additional' => TRUE))) . '</button>
      </li>
    </ol>';
  }
  if ($op == 'dataset-create') {
    $stages =
        '<ol class="stages stage-1">
          <li class="first active">
            <span class="highlight">' . t('Create dataset') . '</span>
          </li>
          <li class="middle uncomplete">
            <span class="highlight">' . t('Add data') . ' </span>
          </li>
          <li class="last uncomplete">
            <span class="highlight">' . t('Additional data') . '</span>
          </li>
        </ol>';
  }
  if ($op == 'dataset-edit') {
    $stages = '<ol class="stages stage-1">
        <li class="first active">
            <span class="highlight">' . t('Create dataset') . '</span>
      </li>
      <li class="middle complete">
          <span class="highlight">' . l('Add data', 'node/add/resource', array('query' => array('dataset' => $dataset_nid))) . '</span>
      </li>
      <li class="last complete">
          <button class="highlight" name="save" value="go-metadata" type="submit">' . l('Additional data', 'node/' . $dataset_nid . '/edit', array('query' => array('additional' => TRUE))) . '</button>
      </li>
    </ol>';
  }
  if ($resource_nid && $dataset_nid) {
  }
  return $stages;
}

/**
 * Implements theme_horizontal_tabs().
 */
function dkan_horizontal_tabs($variables) {
  $element = $variables['element'];
  // Add required JavaScript and Stylesheet.
  drupal_add_library('field_group', 'horizontal-tabs');

  $output = '<label id="resource-edit-title" for="edit-resource">' . $variables['element']['#title'] . '</label>';

  $output .= '<div class="horizontal-tabs-panes">' . $element['#children'] . '</div>';

  return $output;
}
