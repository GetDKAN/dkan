<?php

/**
 * @file
 * Mapper that exposes a node's taxonomy vocabularies as mapping targets.
 */

/**
 * Implements hook_feeds_parser_sources_alter().
 *
 * @todo: Upgrade to 7.
 */
function taxonomy_feeds_parser_sources_alter(&$sources, $content_type) {
  if (!empty($content_type)) {
    foreach (taxonomy_get_vocabularies($content_type) as $vocabulary) {
      $sources['parent:taxonomy:' . $vocabulary->machine_name] = array(
        'name' => t('Feed node: Taxonomy: @vocabulary', array('@vocabulary' => $vocabulary->name)),
        'description' => t('Taxonomy terms from feed node in given vocabulary.'),
        'callback' => 'taxonomy_feeds_get_source',
      );
    }
  }
}

/**
 * Callback, returns taxonomy from feed node.
 */
function taxonomy_feeds_get_source(FeedsSource $source, FeedsParserResult $result, $key) {
  if ($node = node_load($source->feed_nid)) {
    $terms = taxonomy_feeds_node_get_terms($node);
    $vocabularies = taxonomy_vocabulary_load_multiple(array(), array('machine_name' => str_replace('parent:taxonomy:', '', $key)));
    $vocabulary = array_shift($vocabularies);
    $result = array();
    foreach ($terms as $tid => $term) {
      if ($term->vid == $vocabulary->vid) {
        $result[] = new FeedsTermElement($term);
      }
    }
    return $result;
  }
}

/**
 * Implements hook_feeds_processor_targets_alter().
 */
function taxonomy_feeds_processor_targets_alter(&$targets, $entity_type, $bundle_name) {
  foreach (field_info_instances($entity_type, $bundle_name) as $name => $instance) {
    $info = field_info_field($name);
    if ($info['type'] == 'taxonomy_term_reference') {
      $targets[$name] = array(
        'name' => check_plain($instance['label']),
        'callback' => 'taxonomy_feeds_set_target',
        'description' => t('The @label field of the node.', array('@label' => $instance['label'])),
      );
    }
  }
}

/**
 * Callback for mapping. Here is where the actual mapping happens.
 *
 * @todo Do not create new terms for non-autotag fields.
 */
function taxonomy_feeds_set_target($source, $entity, $target, $terms) {
  if (empty($terms)) {
    return;
  }

  // Handle non-multiple values.
  if (!is_array($terms)) {
    $terms = array($terms);
  }

  $info = field_info_field($target);

  // See http://drupal.org/node/881530
  if (isset($info['settings']['allowed_values'][0]['vocabulary'])) {
    $vocabulary = taxonomy_vocabulary_machine_name_load($info['settings']['allowed_values'][0]['vocabulary']);
  }
  else {
    $vocabulary = taxonomy_vocabulary_load($info['settings']['allowed_values'][0]['vid']);
  }

  $i = 0;
  $entity->$target = isset($entity->$target) ? $entity->$target : array();
  foreach ($terms as $term) {
    $tid = 0;
    if ($term instanceof FeedsTermElement) {
      $tid = $term->tid;
    }
    elseif (is_numeric($term)) {
      $tid = $term;
    }
    elseif (is_string($term)) {
      $tid = taxonomy_term_check_term($term, $vocabulary->vid);
    }
    if ($tid) {
      $entity->{$target}['und'][$i]['tid'] = $tid;
    }

    if ($info['cardinality'] == 1) {
      break;
    }
    $i++;
  }
}

/**
 * Find all terms associated with the given node, within one vocabulary.
 */
function taxonomy_feeds_node_get_terms($node, $key = 'tid') {
  $terms = &drupal_static(__FUNCTION__);

  if (!isset($terms[$node->nid][$key])) {
    // Get tids from all taxonomy_term_reference fields.
    $tids = array();
    $fields = field_info_fields();
    foreach ($fields as $field_name => $field) {
      if ($field['type'] == 'taxonomy_term_reference' && field_info_instance('node', $field_name, $node->type)) {
        if (($items = field_get_items('node', $node, $field_name)) && is_array($items)) {
          $tids = array_merge($tids, array_map('_taxonomy_extract_tid', $items));
        }
      }
    }

    // Load terms and cache them in static var.
    $curr_terms = taxonomy_term_load_multiple($tids);
    $terms[$node->nid][$key] = array();
    foreach ($curr_terms as $term) {
      $terms[$node->nid][$key][$term->$key] = $term;
    }
  }
  return $terms[$node->nid][$key];
}

/**
 * Helper function used in taxonomy_feeds_node_get_terms(). Extracts
 * tid from array item returned by field_get_items().
 *
 * @param $item tid information in a form of single element array (key == 'tid', value == tid we're looking for)
 *
 * @return tid extracted from $item.
 *
 * @see taxonomy_feeds_node_get_terms()
 * @see field_get_items()
 */
function _taxonomy_extract_tid($item) {
  return $item['tid'];
}

/**
 * Checks whether a term identified by name and vocabulary exists. Creates a
 * new term if it does not exist.
 *
 * @param $name
 *   A term name.
 * @param $vid
 *   A vocabulary id.
 *
 * @return
 *   A term id.
 */
function taxonomy_term_check_term($name, $vid) {
  $name = trim($name);
  $term = taxonomy_term_lookup_term($name, $vid);
  if (empty($term)) {
    $term = new stdClass();
    $term->name = $name;
    $term->vid = $vid;
    taxonomy_term_save($term);
    return $term->tid;
  }
  return $term->tid;
}

/**
 * Looks up a term, assumes SQL storage backend.
 */
function taxonomy_term_lookup_term($name, $vid) {
  return db_select('taxonomy_term_data', 'td')
    ->fields('td', array('tid', 'name'))
    ->condition('name', $name)
    ->condition('vid', $vid)
    ->execute()
    ->fetchObject();
}