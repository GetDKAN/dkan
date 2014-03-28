<?php

/**
 * @file
 * Theme functions for datasets.
 */

/**
 * Implements hook_theme().
 */
function dkan_dataset_theme() {
  $theme = array(
    'dkan_dataset_resource_view' => array(
      'variables' => array('node' => NULL),
    ),
    'dkan_dataset_release_date_view' => array(
      'variables' => array('node' => NULL),
    ),
    'dkan_dataset_modified_date_view' => array(
      'variables' => array('node' => NULL),
    ),
    'dkan_dataset_identifier_view' => array(
      'variables' => array('node' => NULL),
    ),
    'dkan_dataset_resource_teaser_view' => array(
      'variables' => array('node' => NULL),
    ),
  );
  return $theme;
}
/**
 * Outputs the identifier content.
 */
function theme_dkan_dataset_identifier_view($vars) {
  return '<div property="dcterms:identifier" class="field-name-field-identifier">' . $vars['node']->uuid . '</div>';
}

/**
 * Outputs the modified date content.
 */
function theme_dkan_dataset_modified_date_view($vars) {
  return '<div property="dcterms:modified" class="field-name-field-modified-date">' . date('Y-m-d', $vars['node']->changed) . '</div>';
}

/**
 * Outputs the release date content.
 */
function theme_dkan_dataset_release_date_view($vars) {
  return '<div property="dcterms:issued" class="field-name-field-release-date">' . date('Y-m-d', $vars['node']->created) . '</div>';
}

/**
 * Output the resource into the node content.
 */
function theme_dkan_dataset_resource_view($vars) {
  $node = entity_metadata_wrapper('node', $vars['node']);
  $nodes = $node->field_resources->value();
  $links = array();
  $output = '<div property="dcat:distribution">';

  if (isset($nodes)) {
    foreach ($nodes as $node) {
      $node_wrapper = entity_metadata_wrapper('node', $node);
      $body = $node_wrapper->body->value();
      $term = '';
      if (isset($body['safe_value'])) {
        $desc = '<p class="description">' . dkan_dataset_text_trim($body['safe_value'], 80) . '</p>';
      }
      else {
        $desc = '<p class="description"></p>';
      }
      if (isset($node->field_format) && $node->field_format && $node_wrapper->field_format->value()) {
        $term .= '<span class="format-label" property="dc:format" data-format="' . $node_wrapper->field_format->value()->name . '">' . $node_wrapper->field_format->value()->name  . '</span>';
      }
      else {
        $term = '<span class="format-label" property="dc:format" data-format="data">Data</span>';
      }
      $explore_link = l(t('Explore Data'), 'node/' . $node->nid, array('attributes' => array('class' => array('btn', 'btn-primary'))));
      $dcat = '<div property="dcat:Distribution">';
      $links[] = $dcat . l($node->title . $term, 'node/' . $node->nid, array(
        'html' => TRUE,
        'attributes' => array(
          'class' => array('heading'),
          'title' => $node->title,
          'property' => 'dcat:accessURL',
        ))
      ) . $desc . $explore_link . '</div>';
    }

    $output = theme('item_list', array('items' => $links, 'attributes' => array('class' => array('resource-list'))));
  }
  return $output;
}

/**
 * Output the resource into the node content.
 */
function theme_dkan_dataset_resource_teaser_view($vars) {
  if ($vars['node']->type == 'dataset') {
    $node = entity_metadata_wrapper('node', $vars['node']);
    $nodes = $node->field_resources->value();
  }
  else {
    $nodes = array($vars['node']);
  }
  $links = array();
  if (isset($nodes)) {
    // Data links for generate grouping resources link.
    $links_data = array();
    foreach ($nodes as $node) {
      $node_wrapper = entity_metadata_wrapper('node', $node);
      $name = isset($node->field_format['und']) ? $node_wrapper->field_format->value()->name : '';
      $term = '';
      if (!isset($links_data[$name])) {
        $links_data[$name] = array(
          'term_name' => $name,
          'count' => 1,
          'url' => 'node/' . $vars['node']->nid,
          'link' => array(
            'attributes' => array(
              'class' => array('label'),
              'title' => t('Resources') . ': ' . $vars['node']->title,
              'data-format' => $name,
            ),
          ),
        );
      }
      else {
        $links_data[$name]['count'] = $links_data[$name]['count'] + 1;
      }
    }
    foreach ($links_data as $term_name => $link) {
      $links[] = '<span class="count-resource">' . $link['count'] . 'x</span>' . l($term_name, $link['url'], $link['link']);
    }
    $output = theme('item_list', array('items' => $links, 'attributes' => array('class' => array('resource-list'))));
  }
  return $output;
}
