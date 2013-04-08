<?php

/**
 * Implements hook_views_data().
 */
function search_api_views_views_data() {
  try {
    $data = array();
    $entity_types = entity_get_info();
    foreach (search_api_index_load_multiple(FALSE) as $index) {
      // Fill in base data.
      $key = 'search_api_index_' . $index->machine_name;
      $table = &$data[$key];
      $type_info = search_api_get_item_type_info($index->item_type);
      $table['table']['group'] = t('Indexed @entity_type', array('@entity_type' => $type_info['name']));
      $table['table']['base'] = array(
        'field' => 'search_api_id',
        'index' => $index->machine_name,
        'title' => $index->name,
        'help' => t('Use the %name search index for filtering and retrieving data.', array('%name' => $index->name)),
        'query class' => 'search_api_views_query',
      );
      if (isset($entity_types[$index->item_type])) {
        $table['table'] += array(
          'entity type' => $index->item_type,
          'skip entity load' => TRUE,
        );
      }

      $wrapper = $index->entityWrapper(NULL, TRUE);

      // Add field handlers and relationships provided by the Entity API.
      foreach ($wrapper as $key => $property) {
        $info = $property->info();
        if ($info) {
          entity_views_field_definition($key, $info, $table);
        }
      }

      // Add handlers for all indexed fields.
      foreach ($index->getFields() as $key => $field) {
        $tmp = $wrapper;
        $group = '';
        $name = '';
        $parts = explode(':', $key);
        foreach ($parts as $i => $part) {
          if (!isset($tmp->$part)) {
            continue 2;
          }
          $tmp = $tmp->$part;
          $info = $tmp->info();
          $group = ($group ? $group . ' » ' . $name : ($name ? $name : ''));
          $name = $info['label'];
          if ($i < count($parts) - 1) {
            // Unwrap lists.
            $level = search_api_list_nesting_level($info['type']);
            for ($j = 0; $j < $level; ++$j) {
              $tmp = $tmp[0];
            }
          }
        }
        $id = _entity_views_field_identifier($key, $table);
        if ($group) {
          // @todo Entity type label instead of $group?
          $table[$id]['group'] = $group;
          $name = t('@field (indexed)', array('@field' => $name));
        }
        $table[$id]['title'] = $name;
        $table[$id]['help'] = empty($info['description']) ? t('(No information available)') : $info['description'];
        $table[$id]['type'] = $field['type'];
        if ($id != $key) {
          $table[$id]['real field'] = $key;
        }
        _search_api_views_add_handlers($key, $field, $tmp, $table);
      }

      // Special handlers
      $table['search_api_language']['filter']['handler'] = 'SearchApiViewsHandlerFilterLanguage';

      $table['search_api_id']['title'] = t('Entity ID');
      $table['search_api_id']['help'] = t("The entity's ID.");
      $table['search_api_id']['sort']['handler'] = 'SearchApiViewsHandlerSort';

      $table['search_api_relevance']['group'] = t('Search');
      $table['search_api_relevance']['title'] = t('Relevance');
      $table['search_api_relevance']['help'] = t('The relevance of this search result with respect to the query.');
      $table['search_api_relevance']['field']['type'] = 'decimal';
      $table['search_api_relevance']['field']['handler'] = 'entity_views_handler_field_numeric';
      $table['search_api_relevance']['field']['click sortable'] = TRUE;
      $table['search_api_relevance']['sort']['handler'] = 'SearchApiViewsHandlerSort';

      $table['search_api_excerpt']['group'] = t('Search');
      $table['search_api_excerpt']['title'] = t('Excerpt');
      $table['search_api_excerpt']['help'] = t('The search result excerpted to show found search terms.');
      $table['search_api_excerpt']['field']['type'] = 'text';
      $table['search_api_excerpt']['field']['handler'] = 'entity_views_handler_field_text';

      $table['search_api_views_fulltext']['group'] = t('Search');
      $table['search_api_views_fulltext']['title'] = t('Fulltext search');
      $table['search_api_views_fulltext']['help'] = t('Search several or all fulltext fields at once.');
      $table['search_api_views_fulltext']['filter']['handler'] = 'SearchApiViewsHandlerFilterFulltext';
      $table['search_api_views_fulltext']['argument']['handler'] = 'SearchApiViewsHandlerArgumentFulltext';

      $table['search_api_views_more_like_this']['group'] = t('Search');
      $table['search_api_views_more_like_this']['title'] = t('More like this');
      $table['search_api_views_more_like_this']['help'] = t('Find similar content.');
      $table['search_api_views_more_like_this']['argument']['handler'] = 'SearchApiViewsHandlerArgumentMoreLikeThis';
    }
    return $data;
  }
  catch (Exception $e) {
    watchdog_exception('search_api_views', $e);
  }
}

/**
 * Helper function that returns an array of handler definitions to add to a
 * views field definition.
 */
function _search_api_views_add_handlers($id, array $field, EntityMetadataWrapper $wrapper, array &$table) {
  $type = $field['type'];
  $inner_type = search_api_extract_inner_type($type);

  if (strpos($id, ':')) {
    entity_views_field_definition($id, $wrapper->info(), $table);
  }
  $id = _entity_views_field_identifier($id, $table);
  $table += array($id => array());

  if ($inner_type == 'text') {
    $table[$id] += array(
      'argument' => array(
        'handler' => 'SearchApiViewsHandlerArgumentText',
      ),
      'filter' => array(
        'handler' => 'SearchApiViewsHandlerFilterText',
      ),
    );
    return;
  }

  if ($options = $wrapper->optionsList('view')) {
    $table[$id]['filter']['handler'] = 'SearchApiViewsHandlerFilterOptions';
    $table[$id]['filter']['options'] = $options;
  }
  elseif ($inner_type == 'boolean') {
    $table[$id]['filter']['handler'] = 'SearchApiViewsHandlerFilterBoolean';
  }
  elseif ($inner_type == 'date') {
    $table[$id]['filter']['handler'] = 'SearchApiViewsHandlerFilterDate';
  }
  else {
    $table[$id]['filter']['handler'] = 'SearchApiViewsHandlerFilter';
  }

  $table[$id]['argument']['handler'] = 'SearchApiViewsHandlerArgument';

  // We can only sort according to single-valued fields.
  if ($type == $inner_type) {
    $table[$id]['sort']['handler'] = 'SearchApiViewsHandlerSort';
    if (isset($table[$id]['field'])) {
      $table[$id]['field']['click sortable'] = TRUE;
    }
  }
}

/**
 * Implements hook_views_plugins().
 */
function search_api_views_views_plugins() {
  $ret = array(
    'query' => array(
      'search_api_views_query' => array(
        'title' => t('Search API Query'),
        'help' => t('Query will be generated and run using the Search API.'),
        'handler' => 'SearchApiViewsQuery'
      ),
    ),
  );

  if (module_exists('search_api_facetapi')) {
    $ret['display']['search_api_views_facets_block'] = array(
      'title' => t('Facets block'),
      'help' => t('Display facets for this search as a block anywhere on the site.'),
      'handler' => 'SearchApiViewsFacetsBlockDisplay',
      'uses hook block' => TRUE,
      'use ajax' => FALSE,
      'use pager' => FALSE,
      'use more' => TRUE,
      'accept attachments' => TRUE,
      'admin' => t('Facets block'),
    );
  }

  return $ret;
}
