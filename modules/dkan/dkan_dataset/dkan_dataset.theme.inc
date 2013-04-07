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
  $nodes = dkan_dataset_get_resource_nodes($vars['node']->nid);
  $links = array();
  $output = '<div property="dcat:distribution">';

  if (isset($nodes)) {
    foreach ($nodes as $node) {
      if (isset($node->body) && $node->body && isset($node->body[$node->language])) {
        $desc = '<p class="description">' . dkan_dataset_text_trim($node->body[$node->language][0]['safe_value'], 80) . '</p>';
      }
      else {
        $desc = '<p class="description"></p>';
      }
      if (isset($node->field_format) && $node->field_format) {
        $term = taxonomy_term_load($node->field_format[$node->language][0]['tid']);
        $term = '<span class="format-label" property="dc:format" data-format="' . $term->name  . '">' . $term->name  .'</span>';
      }
      else {
        $term = '<span class="format-label" property="dc:format" data-format="data">Data</span>';
      }
      $explore_link = l('Explore Data', 'node/' . $node->nid, array('attributes' => array('class' => array('btn', 'btn-primary'))));
      $dcat = '<div property="dcat:Distribution">';
      $links[] = $dcat. l($node->title . $term , 'node/' . $node->nid, array(
        'html' => TRUE,
        'attributes' => array(
          'class' => array('heading'),
          'title' => $node->title,
          'property' => 'dcat:accessURL'
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
    $nodes = dkan_dataset_get_resource_nodes($vars['node']->nid);
  }
  else {
    $nodes = array($vars['node']);
  }
  $links = array();
  if (isset($nodes)) {
    foreach ($nodes as $node) {
      $term = '';
      if (isset($node->field_format) && $node->field_format) {
        $term = taxonomy_term_load($node->field_format[$node->language][0]['tid']);
        $links[] = l($term->name , 'node/' . $node->nid, array('attributes' => array('class' => array('label'), 'title' => $node->title, 'data-format' => $term->name)));
      }
    }

    $output = theme('item_list', array('items' => $links, 'attributes' => array('class' => array('resource-list'))));
  }
  return $output;
}

