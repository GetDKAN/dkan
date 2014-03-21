<?php

/**
 * @file
 * Contains menu item registration for the content tool.
 *
 * The menu items registered are AJAX callbacks for the things like
 * autocomplete and other tools needed by the content types.
 */

function ctools_content_menu(&$items) {
  $base = array(
    'access arguments' => array('access content'),
    'type' => MENU_CALLBACK,
    'file' => 'includes/content.menu.inc',
  );
  $items['ctools/autocomplete/%'] = array(
    'page callback' => 'ctools_content_autocomplete_entity',
    'page arguments' => array(2),
  ) + $base;
}

/**
 * Helper function for autocompletion of entity titles.
 */
function ctools_content_autocomplete_entity($type, $string = '') {
  if ($string != '') {
    global $user;
    $entity_info = entity_get_info($type);

    // We must query all ids, because if every one of the 10 don't have access
    // the user may never be able to autocomplete a node title.
    $preg_matches = array();
    $matches = array();
    $match = preg_match('/\[id: (\d+)\]/', $string, $preg_matches);
    if (!$match) {
      $match = preg_match('/^id: (\d+)/', $string, $preg_matches);
    }
    // If an ID match was found, use that ID rather than the whole string.
    if ($match) {
      $entity_id = $preg_matches[1];
      $entity = entity_load($type, array($entity_id));

      // Format results in an array so later we could add attributes to the
      // autocomplete text that is returned.
      $results = array($entity_id => array(
        'label' => $entity[$entity_id]->$entity_info['entity keys']['label'],
      ));
    }
    else {
      $results = _ctools_getReferencableEntities($type, $entity_info, $string, 'LIKE', 10);
    }
    foreach($results as $entity_id => $result) {
      if (!$entity_info['entity keys']['label']) {
         $matches["[id: $entity_id]"] = '<span class="autocomplete_title">' . $entity_id . '</span>';
      }
      else {
        $matches[$result['label'] . " [id: $entity_id]"] = '<span class="autocomplete_title">' . check_plain($result['label']) . '</span>';
      }
    }

    drupal_json_output($matches);
  }
}

/*
 * Use well known/tested entity reference code to build our search query
 * From EntityReference_SelectionHandler_Generic class
 */
function _ctools_buildQuery($entity_type, $entity_info, $match = NULL, $match_operator = 'CONTAINS') {
  $base_table = $entity_info['base table'];
    $query = db_select($base_table)
      ->fields($base_table, array($entity_info['entity keys']['id']));

    if (isset($match)) {
      if (isset($entity_info['entity keys']['label'])) {
        $query->condition($base_table .'.'. $entity_info['entity keys']['label'], '%' . $match . '%' , $match_operator);
      }
    }

    // Add a label to the query, if the label exists
    if (isset($entity_info['entity keys']['label'])) {
      $query->fields($base_table, array($entity_info['entity keys']['label']));
    }

    // Add a generic entity access tag to the query.
    $query->addTag('ctools');

    if($entity_type == 'comment') {
      // Adding the 'comment_access' tag is sadly insufficient for comments: core
      // requires us to also know about the concept of 'published' and
      // 'unpublished'.
      if (!user_access('administer comments')) {
        $query->condition('comment.status', COMMENT_PUBLISHED);
      }
      // Join to a node if the user does not have node access bypass permissions
      // to obey node published permissions
      if (!user_access('bypass node access') && !count(module_implements('node_grants'))) {
        $node_alias = $query->innerJoin('node', 'n', '%alias.nid = comment.nid');
        $query->condition($node_alias . '.status', NODE_PUBLISHED);
      }
      $query->addTag('node_access');
    }
    else {
      $query->addTag($entity_type . '_access');
    }

    // Add the sort option.
    if(isset($entity_info['entity keys']['label'])) {
      $query->orderBy($base_table .'.'. $entity_info['entity keys']['label'], 'ASC');
    }

    return $query;
}

/**
 * Private function to get referencable entities. Based on code from the
 * Entity Reference module.
 */
function _ctools_getReferencableEntities($entity_type, $entity_info, $match = NULL, $match_operator = 'LIKE', $limit = 0) {
  $options = array();

  $query = _ctools_buildQuery($entity_type, $entity_info, $match, $match_operator);
  if ($limit > 0) {
    $query->range(0, $limit);
  }

  $results = $query->execute();

  if (!empty($results)) {
    foreach ($results as $record) {
      $options[$record->{$entity_info['entity keys']['id']}] = array(
        'label' => isset($entity_info['entity keys']['label']) ? check_plain($record->{$entity_info['entity keys']['label']}) : $record->{$entity_info['entity keys']['id']},
      );
    }
  }

  return $options;
}