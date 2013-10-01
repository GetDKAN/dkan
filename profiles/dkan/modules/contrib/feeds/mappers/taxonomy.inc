<?php

/**
 * @file
 * On behalf implementation of Feeds mapping API for taxonomy.module.
 */

/**
 * Search by term name.
 */
define('FEEDS_TAXONOMY_SEARCH_TERM_NAME', 0);

/**
 * Search by term id.
 */
define('FEEDS_TAXONOMY_SEARCH_TERM_ID', 1);

/**
 * Search by GUID.
 */
define('FEEDS_TAXONOMY_SEARCH_TERM_GUID', 2);

/**
 * Implements hook_feeds_parser_sources_alter().
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
        'description' => t('The @label field of the entity.', array('@label' => $instance['label'])),
        'summary_callback' => 'taxonomy_feeds_summary_callback',
        'form_callback' => 'taxonomy_feeds_form_callback',
      );
    }
  }
}

/**
 * Callback for mapping. Here is where the actual mapping happens.
 *
 * @todo Do not create new terms for non-autotag fields.
 */
function taxonomy_feeds_set_target($source, $entity, $target, $terms, $mapping = array()) {

  // Allow mapping the string '0' to a term name.
  if (empty($terms) && $terms != 0) {
    return;
  }

  // Handle non-multiple values.
  if (!is_array($terms)) {
    $terms = array($terms);
  }

  // Add in default values.
  $mapping += array(
    'term_search' => FEEDS_TAXONOMY_SEARCH_TERM_NAME,
    'autocreate' => FALSE,
  );

  $info = field_info_field($target);

  $cache = &drupal_static(__FUNCTION__);
  if (!isset($cache['allowed_values'][$target])) {
    $cache['allowed_values'][$target] = taxonomy_allowed_values($info);
  }

  if (!isset($cache['allowed_vocabularies'][$target])) {
    foreach ($info['settings']['allowed_values'] as $tree) {
      if ($vocabulary = taxonomy_vocabulary_machine_name_load($tree['vocabulary'])) {
        $cache['allowed_vocabularies'][$target][$vocabulary->vid] = $vocabulary->machine_name;
      }
    }
  }

  $query = new EntityFieldQuery();
  $query->entityCondition('entity_type', 'taxonomy_term')
    ->entityCondition('bundle', $cache['allowed_vocabularies'][$target])
    ->range(0, 1);


  $field = isset($entity->$target) ? $entity->$target : array('und' => array());

  // Allow for multiple mappings to the same target.
  $delta = count($field['und']);

  // Iterate over all values.
  foreach ($terms as $term) {

    if ($info['cardinality'] == $delta) {
      break;
    }

    $tid = FALSE;

    // FeedsTermElement already is a term.
    if ($term instanceof FeedsTermElement) {
      $tid = $term->tid;
    }
    else {
      switch ($mapping['term_search']) {

        // Lookup by name.
        case FEEDS_TAXONOMY_SEARCH_TERM_NAME:
          $name_query = clone $query;
          if ($tids = $name_query->propertyCondition('name', $term)->execute()) {
            $tid = key($tids['taxonomy_term']);
          }
          elseif ($mapping['autocreate']) {
            $term = (object) array(
              'name' => $term,
              'vid' => key($cache['allowed_vocabularies'][$target]),
              'vocabulary_machine_name' => reset($cache['allowed_vocabularies'][$target]),
            );
            taxonomy_term_save($term);
            $tid = $term->tid;
            // Add to the list of allowed values.
            $cache['allowed_values'][$target][$tid] = $term->name;
          }
          break;

        // Lookup by tid.
        case FEEDS_TAXONOMY_SEARCH_TERM_ID:
          if (is_numeric($term)) {
            $tid = $term;
          }
          break;

        // Lookup by GUID.
        case FEEDS_TAXONOMY_SEARCH_TERM_GUID:
          $tid = taxonomy_feeds_term_lookup_term_by_guid($term);
          break;
      }
    }

    if ($tid && isset($cache['allowed_values'][$target][$tid])) {
      $field['und'][$delta]['tid'] = $tid;
      $delta++;
    }
  }

  $entity->$target = $field;
}

/**
 * Finds all terms associated with the given node, within one vocabulary.
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
          $tids = array_merge($tids, array_map('_taxonomy_feeds_extract_tid', $items));
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
 * Extracts tid from array item returned by field_get_items().
 *
 * @param array $item
 *   Tid information in the form of a single element array
 *   (key == 'tid', value == tid we're looking for)
 *
 * @return int
 *   Term id extracted from $item.
 *
 * @see taxonomy_feeds_node_get_terms()
 * @see field_get_items()
 */
function _taxonomy_feeds_extract_tid($item) {
  return $item['tid'];
}

/**
 * Looks up a term by GUID, assumes SQL storage backend.
 *
 * @param string $guid
 *   The Feeds GUID to compare against.
 *
 * @return int|FALSE
 *   The term id, or FALSE if one was not found.
 */
function taxonomy_feeds_term_lookup_term_by_guid($guid) {
  return db_select('feeds_item')
    ->fields('feeds_item', array('entity_id'))
    ->condition('entity_type', 'taxonomy_term')
    ->condition('guid', $guid)
    ->execute()
    ->fetchField();
}

/**
 * Mapping configuration summary for taxonomy.module.
 *
 * @param array $mapping
 *   Associative array of the mapping settings.
 * @param array $target
 *   Array of target settings, as defined by the processor or
 *   hook_feeds_processor_targets_alter().
 * @param array $form
 *   The whole mapping form.
 * @param array $form_state
 *   The form state of the mapping form.
 *
 * @return string
 *   Returns, as a string that may contain HTML, the summary to display while
 *   the full form isn't visible.
 *   If the return value is empty, no summary and no option to view the form
 *   will be displayed.
 */
function taxonomy_feeds_summary_callback($mapping, $target, $form, $form_state) {
  $options = _taxonomy_feeds_form_callback_options();
  if (empty($mapping['term_search'])) {
    return t('Search taxonomy terms by: <strong>@search</strong>', array('@search' => $options[FEEDS_TAXONOMY_SEARCH_TERM_NAME]));
  }
  return t('Search taxonomy terms by: <strong>@search</strong>', array('@search' => $options[$mapping['term_search']]));
}

/**
 * Settings form callback.
 *
 * @return array
 *   The per mapping configuration form. Once the form is saved, $mapping will
 *   be populated with the form values.
 */
function taxonomy_feeds_form_callback($mapping, $target, $form, $form_state) {
  return array(
    'term_search' => array(
      '#type' => 'select',
      '#title' => t('Search taxonomy terms by'),
      '#options' => _taxonomy_feeds_form_callback_options(),
      '#default_value' => !empty($mapping['term_search']) ? $mapping['term_search'] : FEEDS_TAXONOMY_SEARCH_TERM_NAME,
    ),
    'autocreate' => array(
      '#type' => 'checkbox',
      '#title' => t('Auto create'),
      '#description' => t("Create the term if it doesn't exist."),
      '#default_value' => !empty($mapping['autocreate']) ? $mapping['autocreate'] : 0,
      '#states' => array(
        'visible' => array(
          ':input[name$="[settings][term_search]"]' => array('value' => FEEDS_TAXONOMY_SEARCH_TERM_NAME),
        ),
      ),
    ),
  );
}

/**
 * Returns the list of available term search methods.
 *
 * @return array
 *   An array of taxonomy search option titles.
 */
function _taxonomy_feeds_form_callback_options() {
  return array(
    FEEDS_TAXONOMY_SEARCH_TERM_NAME => 'Term name',
    FEEDS_TAXONOMY_SEARCH_TERM_ID => 'Term ID',
    FEEDS_TAXONOMY_SEARCH_TERM_GUID => 'GUID',
  );
}
