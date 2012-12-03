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
    foreach ($breadcrumb as $num => $item) {
      if ($item == '<a href="/">Home</a>') {
        $breadcrumb[$num] = '<a href="/"><i class="icon-large icon-home"></i><span> Home</span></a>';
      }
      if ($item == '[all items]') {
        $breadcrumb[$num] = t('Search Datasets');
      }
    }

    $output = '<h2 class="element-invisible">' . t('You are here') . '</h2>';

    $crumbs = '<ul class="breadcrumb">';

    // Remove null values.
    $breadcrumb = array_filter($breadcrumb);;
    $i = 1;
    foreach($breadcrumb as $value) {
      if ($i == count($breadcrumb)) {
        $crumbs .= '<li class="active-trail">' . $value . '</li>';
      }
      else {
        $crumbs .= '<li>' . $value . '</li>';
      }
      $i++;
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
        $target_id = isset($node->field_dataset_ref[$node->language][0]['target_id']) ? $node->field_dataset_ref[$node->language][0]['target_id'] : $node->field_dataset_ref[0]['target_id'];
        $action_items['items'][] = l('<i class="icon-large icon-caret-left"></i> Back to dataset', 'node/' . $target_id, array('html' => TRUE, 'attributes' => array('class' => array('btn'))));
      }
      if (node_access('update', $node->nid)) {
        $action_items['items'][] = l('<i class="icon-large icon-wrench"></i> Edit', 'node/' . $node->nid . '/edit', array('html' => TRUE, 'attributes' => array('class' => array('btn'))));
        if (isset($node) && $node->type == 'dataset') {
          $action_items['items'][] = l('<i class="icon-large icon-plus"></i> Add Resource', 'node/add/resource', array('html' => TRUE, 'attributes' => array('class' => array('btn')), 'query' => array('dataset' => $node->nid)));
        }
      }
      if ($node->type == 'resource' && isset($node->field_upload) && $node->field_upload) {
        $uri = isset($node->field_upload[$node->language][0]['uri']) ? $node->field_upload[$node->language][0]['uri'] : $node->field_upload[0]['uri'];
        $url = file_create_url($uri);
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
  $profile_path = drupal_get_path('profile', 'dkan');
  // Add font-awesome. This is not a GPL library so has to be downloaded separately.
  if (file_exists($profile_path . '/libraries/font_awesome/css/font-awesome.css')) {
    drupal_add_css($profile_path . '/libraries/font_awesome/css/font-awesome.css');
  }
  if ($vars['is_front']) {
    drupal_add_js($profile_path . '/themes/dkan/js/front.js');
  }
  // TODO: create site admin role that can deal also see tabs.
  if ($vars['user']->uid != 1 && isset($vars['node'])) {
    $vars['tabs'] = '';
  }
  // Custom breadcrumb elements for specific contexts.
  if (module_exists('context')) {
    $contexts = context_active_contexts();
  }
  // Remove title on dataset edit and creation pages.
  $vars['title'] = '';
}

/**
 * Implments template_preprocess_block().
 */
function dkan_preprocess_block(&$vars) {

  $vars['title'] = '';
  if ($vars['block_html_id'] ==  'block-system-main') {
    $vars['title'] = drupal_get_title();

    if (module_exists('context')) {
      $contexts = context_active_contexts();
    }
    if (isset($contexts['dataset-create'])) {
      $stages = dkan_create_stages('dataset-create');
      $vars['content'] = $stages . $vars['content'];
      $vars['title'] = '';
    }
    if (isset($contexts['resource-create'])) {
      $query = drupal_get_query_parameters();
      $stages = dkan_create_stages('resource-create', $query['dataset']);
      $vars['content'] = $stages . $vars['content'];
      $vars['title'] = '';
    }
    if (isset($contexts['resource-edit'])) {
      $stages = dkan_create_stages('resource-edit', $vars['elements']['field_dataset_ref']['und']['#value'][0], $vars['elements']['#node']->nid);
      $vars['content'] = $stages . $vars['content'];
      $vars['title'] = '';
    }
    if (isset($contexts['dataset-edit'])) {
      if ($query = drupal_get_query_parameters()) {
        if (!isset($query['additional'])) {
          $stages = dkan_create_stages('dataset-edit', $vars['elements']['#node']->nid);
          $vars['content'] = $stages . $vars['content'];
        }
        else {
          $stages = dkan_create_stages('dataset-additional', $vars['elements']['#node']->nid);
          $vars['content'] = $stages . $vars['content'];
        }
      }
      $vars['title'] = '';
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
          <button class="highlight" name="save" value="go-dataset" type="submit">' . l('Add dataset', 'node/add/resource', array('query' => array('dataset' =>  $dataset_nid))) . '</button>
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

/**
 * Implements theme_facetapi_link_active().
 */
function dkan_facetapi_link_active($variables) {

  // Sanitizes the link text if necessary.
  $sanitize = empty($variables['options']['html']);
  $link_text = ($sanitize) ? check_plain($variables['text']) : $variables['text'];

  // Theme function variables fro accessible markup.
  // @see http://drupal.org/node/1316580
  $accessible_vars = array(
    'text' => $variables['text'],
    'active' => TRUE,
  );

  // Builds link, passes through t() which gives us the ability to change the
  // position of the widget on a per-language basis.
  $replacements = array(
    '!facetapi_deactivate_widget' => theme('facetapi_deactivate_widget', $variables),
    '!facetapi_accessible_markup' => theme('facetapi_accessible_markup', $accessible_vars),
  );
  $variables['text'] = t('!facetapi_deactivate_widget !facetapi_accessible_markup', $replacements);
  $variables['options']['html'] = TRUE;
  $alter = array(
    'max_length' => 30,
    'ellipsis' => TRUE,
    'word_boundary' => TRUE,
    'trim' => TRUE,
  );
  $link_text = views_trim_text($alter, $link_text);
  $variables['text'] = $link_text;
  return theme_link($variables);
}

/**
 * Implements theme_facetapi_link_active().
 */
function dkan_facetapi_link_inactive($variables) {
  // Builds accessible markup.
  // @see http://drupal.org/node/1316580
  $alter = array(
    'max_length' => 30,
    'ellipsis' => TRUE,
    'word_boundary' => TRUE,
    'trim' => TRUE,
  );
  $variables['text'] = views_trim_text($alter, $variables['text']);
  $accessible_vars = array(
    'text' => $variables['text'],
    'active' => FALSE,
  );
  $accessible_markup = theme('facetapi_accessible_markup', $accessible_vars);

  // Sanitizes the link text if necessary.
  $sanitize = empty($variables['options']['html']);
  $variables['text'] = ($sanitize) ? check_plain($variables['text']) : $variables['text'];

  // Adds count to link if one was passed.
  if (isset($variables['count'])) {
    $variables['text'] .= ' ' . theme('facetapi_count', $variables);
  }

  // Resets link text, sets to options to HTML since we already sanitized the
  // link text and are providing additional markup for accessibility.
  $variables['text'] .= $accessible_markup;
  $variables['options']['html'] = TRUE;
  return theme_link($variables);
}
