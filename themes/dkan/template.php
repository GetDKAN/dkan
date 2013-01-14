<?php

/**
 * @file
 * Customizations for DKAN.
 */

/**
 * Implements theme_breadcrumb().
 */
function dkan_breadcrumb($variables) {
  if (drupal_is_front_page()) {
    return;
  }
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
    $theme = alpha_get_theme();
    $tabs = dkan_theme_process_tabs($theme->page['tabs']);
    $vars['tabs'] = drupal_render($tabs);
  }
}

function dkan_theme($existing, $type, $theme, $path) {
  return array(
    'dkan_tabs_local_task' => array(
      'render element' => 'element',
    ),
  );
}

function dkan_theme_process_tabs($tabs) {
  if ($tabs['#primary']) {
    // Remove active tab.
    foreach ($tabs['#primary'] as $row_num => $items) {
      $tabs['#primary'][$row_num]['#theme'] = 'dkan_tabs_local_task';
    }
  }
  if ($tabs['#secondary']) {
    // Remove active tab.
    foreach ($tabs['#secondary'] as $row_num => $items) {
      $tabs['#secondary'][$row_num]['#theme'] = 'dkan_tabs_local_task';
    }
  }
  return $tabs;
}

function dkan_dkan_tabs_local_task($variables) {
  $link = $variables['element']['#link'];
  $icon_type = 'wrench';
  if ($link['page_callback'] == 'devel_load_object') {
    $icon_type = 'cogs';
  }
  elseif ($link['page_callback'] == 'node_page_edit') {
    $icon_type = 'edit';
  }
  elseif ($link['page_callback'] == 'node_page_view') {
    $icon_type = 'eye-open';
  }
  elseif ($link['page_callback'] == 'dkan_dataset_add_resource') {
    $icon_type = 'plus';
  }
  elseif ($link['page_callback'] == 'dkan_dataset_back') {
    $icon_type = 'caret-left';
  }
  elseif ($link['page_callback'] == 'dkan_dataset_download') {
    $icon_type = 'download';
    $link['localized_options']['attributes']['class'][] = 'btn-primary';
  }
  dpm($link['page_callback']);
  $icon = '<i class="icon-large icon-' . $icon_type . '"></i> ';
  $link_text = $icon . $link['title'];
  $link['localized_options']['html'] = TRUE;
  $link['localized_options']['attributes']['class'][] = 'btn';

  return "<li>" . l($link_text, $link['href'], $link['localized_options']) . "</li>\n";
}

/**
 * Implements template_preprocess_node.
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
