<?php

/**
 * Views query class using a Search API index as the data source.
 */
class SearchApiViewsQuery extends views_plugin_query {

  /**
   * Number of results to display.
   *
   * @var int
   */
  protected $limit;

  /**
   * Offset of first displayed result.
   *
   * @var int
   */
  protected $offset;

  /**
   * The index this view accesses.
   *
   * @var SearchApiIndex
   */
  protected $index;

  /**
   * The query that will be executed.
   *
   * @var SearchApiQueryInterface
   */
  protected $query;

  /**
   * The results returned by the query, after it was executed.
   *
   * @var array
   */
  protected $search_api_results = array();

  /**
   * Array of all encountered errors.
   *
   * Each of these is fatal, meaning that a non-empty $errors property will
   * result in an empty result being returned.
   *
   * @var array
   */
  protected $errors;

  /**
   * The names of all fields whose value is required by a handler.
   *
   * The format follows the same as Search API field identifiers (parent:child).
   *
   * @var array
   */
  protected $fields;

  /**
   * The query's sub-filters representing the different Views filter groups.
   *
   * @var array
   */
  protected $filters = array();

  /**
   * The conjunction with which multiple filter groups are combined.
   *
   * @var string
   */
  public $group_operator = 'AND';

  /**
   * Create the basic query object and fill with default values.
   */
  public function init($base_table, $base_field, $options) {
    try {
      $this->errors = array();
      parent::init($base_table, $base_field, $options);
      $this->fields = array();
      if (substr($base_table, 0, 17) == 'search_api_index_') {
        $id = substr($base_table, 17);
        $this->index = search_api_index_load($id);
        $this->query = $this->index->query(array(
          'parse mode' => 'terms',
        ));
      }
    }
    catch (Exception $e) {
      $this->errors[] = $e->getMessage();
    }
  }

  /**
   * Add a field that should be retrieved from the results by this view.
   *
   * @param $field
   *   The field's identifier, as used by the Search API. E.g., "title" for a
   *   node's title, "author:name" for a node's author's name.
   *
   * @return SearchApiViewsQuery
   *   The called object.
   */
  public function addField($field) {
    $this->fields[$field] = TRUE;
    return $field;
  }

  /**
   * Add a sort to the query.
   *
   * @param $selector
   *   The field to sort on. All indexed fields of the index are valid values.
   *   In addition, the special fields 'search_api_relevance' (sort by
   *   relevance) and 'search_api_id' (sort by item id) may be used.
   * @param $order
   *   The order to sort items in - either 'ASC' or 'DESC'. Defaults to 'ASC'.
   */
  public function add_selector_orderby($selector, $order = 'ASC') {
    $this->query->sort($selector, $order);
  }

  /**
   * Defines the options used by this query plugin.
   *
   * Adds an option to bypass access checks.
   */
  public function option_definition() {
    return parent::option_definition() + array(
      'search_api_bypass_access' => array(
        'default' => FALSE,
      ),
    );
  }

  /**
   * Add settings for the UI.
   *
   * Adds an option for bypassing access checks.
   */
  public function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $form['search_api_bypass_access'] = array(
      '#type' => 'checkbox',
      '#title' => t('Bypass access checks'),
      '#description' => t('If the underlying search index has access checks enabled, this option allows to disable them for this view.'),
      '#default_value' => $this->options['search_api_bypass_access'],
    );
  }

  /**
   * Builds the necessary info to execute the query.
   */
  public function build(&$view) {
    $this->view = $view;

    // Setup the nested filter structure for this query.
    if (!empty($this->where)) {
      // If the different groups are combined with the OR operator, we have to
      // add a new OR filter to the query to which the filters for the groups
      // will be added.
      if ($this->group_operator === 'OR') {
        $base = $this->query->createFilter('OR');
        $this->query->filter($base);
      }
      else {
        $base = $this->query;
      }
      // Add a nested filter for each filter group, with its set conjunction.
      foreach ($this->where as $group_id => $group) {
        if (!empty($group['conditions']) || !empty($group['filters'])) {
          // For filters without a group, we want to always add them directly to
          // the query.
          $filter = ($group_id === '') ? $this->query : $this->query->createFilter($group['type']);
          if (!empty($group['conditions'])) {
            foreach ($group['conditions'] as $condition) {
              list($field, $value, $operator) = $condition;
              $filter->condition($field, $value, $operator);
            }
          }
          if (!empty($group['filters'])) {
            foreach ($group['filters'] as $nested_filter) {
              $filter->filter($nested_filter);
            }
          }
          // If no group was given, the filters were already set on the query.
          if ($group_id !== '') {
            $base->filter($filter);
          }
        }
      }
    }

    // Initialize the pager and let it modify the query to add limits.
    $view->init_pager();
    $this->pager->query();

    // Add the "search_api_bypass_access" option to the query, if desired.
    if (!empty($this->options['search_api_bypass_access'])) {
      $this->query->setOption('search_api_bypass_access', TRUE);
    }
  }

  /**
   * Executes the query and fills the associated view object with according
   * values.
   *
   * Values to set: $view->result, $view->total_rows, $view->execute_time,
   * $view->pager['current_page'].
   */
  public function execute(&$view) {
    if ($this->errors) {
      if (error_displayable()) {
        foreach ($this->errors as $msg) {
          drupal_set_message(check_plain($msg), 'error');
        }
      }
      $view->result = array();
      $view->total_rows = 0;
      $view->execute_time = 0;
      return;
    }

    try {
      $start = microtime(TRUE);
      // Add range and search ID (if it wasn't already set).
      $this->query->range($this->offset, $this->limit);
      if ($this->query->getOption('search id') == get_class($this->query)) {
        $this->query->setOption('search id', 'search_api_views:' . $view->name . ':' . $view->current_display);
      }

      // Execute the search.
      $results = $this->query->execute();
      $this->search_api_results = $results;

      // Store the results.
      $this->pager->total_items = $view->total_rows = $results['result count'];
      if (!empty($this->pager->options['offset'])) {
        $this->pager->total_items -= $this->pager->options['offset'];
      }
      $this->pager->update_page_info();
      $view->result = array();
      if (!empty($results['results'])) {
        $this->addResults($results['results'], $view);
      }
      // We shouldn't use $results['performance']['complete'] here, since
      // extracting the results probably takes considerable time as well.
      $view->execute_time = microtime(TRUE) - $start;
    }
    catch (Exception $e) {
      $this->errors[] = $e->getMessage();
      // Recursion to get the same error behaviour as above.
      return $this->execute($view);
    }
  }

  /**
   * Helper function for adding results to a view in the format expected by the
   * view.
   */
  protected function addResults(array $results, $view) {
    $rows = array();
    $missing = array();
    $items = array();

    // First off, we try to gather as much field values as possible without
    // loading any items.
    foreach ($results as $id => $result) {
      $row = array();

      // Include the loaded item for this result row, if present, or the item
      // ID.
      if (!empty($result['entity'])) {
        $row['entity'] = $result['entity'];
      }
      else {
        $row['entity'] = $id;
      }

      $row['_entity_properties']['search_api_relevance'] = $result['score'];
      $row['_entity_properties']['search_api_excerpt'] = empty($result['excerpt']) ? '' : $result['excerpt'];

      // Gather any fields from the search results.
      if (!empty($result['fields'])) {
        $row['_entity_properties'] += $result['fields'];
      }

      // Check whether we need to extract any properties from the result item.
      $missing_fields = array_diff_key($this->fields, $row);
      if ($missing_fields) {
        $missing[$id] = $missing_fields;
        if (is_object($row['entity'])) {
          $items[$id] = $row['entity'];
        }
        else {
          $ids[] = $id;
        }
      }

      // Save the row values for adding them to the Views result afterwards.
      $rows[$id] = (object) $row;
    }

    // Load items of those rows which haven't got all field values, yet.
    if (!empty($ids)) {
      $items += $this->index->loadItems($ids);
      // $items now includes loaded items, and those already passed in the
      // search results.
      foreach ($items as $id => $item) {
        // Extract item properties.
        $wrapper = $this->index->entityWrapper($item, FALSE);
        $rows[$id]->_entity_properties += $this->extractFields($wrapper, $missing[$id]);
        $rows[$id]->entity = $item;
      }
    }

    // Finally, add all rows to the Views result set.
    $view->result = array_values($rows);
  }

  /**
   * Helper function for extracting all necessary fields from a result item.
   *
   * Usually, this method isn't needed anymore as the properties are now
   * extracted by the field handlers themselves.
   */
  protected function extractFields(EntityMetadataWrapper $wrapper, array $all_fields) {
    $fields = array();
    foreach ($all_fields as $key => $true) {
      $fields[$key]['type'] = 'string';
    }
    $fields = search_api_extract_fields($wrapper, $fields, array('sanitized' => TRUE));
    $ret = array();
    foreach ($all_fields as $key => $true) {
      $ret[$key] = isset($fields[$key]['value']) ? $fields[$key]['value'] : '';
    }
    return $ret;
  }

  /**
   * Returns the according entity objects for the given query results.
   *
   * This is necessary to support generic entity handlers and plugins with this
   * query backend.
   *
   * If the current query isn't based on an entity type, the method will return
   * an empty array.
   */
  public function get_result_entities($results, $relationship = NULL, $field = NULL) {
    list($type, $wrappers) = $this->get_result_wrappers($results, $relationship, $field);
    $return = array();
    foreach ($wrappers as $id => $wrapper) {
      try {
        $return[$id] = $wrapper->value();
      }
      catch (EntityMetadataWrapperException $e) {
        // Ignore.
      }
    }
    return array($type, $return);
  }

  /**
   * Returns the according metadata wrappers for the given query results.
   *
   * This is necessary to support generic entity handlers and plugins with this
   * query backend.
   */
  public function get_result_wrappers($results, $relationship = NULL, $field = NULL) {
    $is_entity = (boolean) entity_get_info($this->index->item_type);
    $wrappers = array();
    $load_entities = array();
    foreach ($results as $row_index => $row) {
      if ($is_entity && isset($row->entity)) {
        // If this entity isn't load, register it for pre-loading.
        if (!is_object($row->entity)) {
          $load_entities[$row->entity] = $row_index;
        }

        $wrappers[$row_index] = $this->index->entityWrapper($row->entity);
      }
    }

    // If the results are entities, we pre-load them to make use of a multiple
    // load. (Otherwise, each result would be loaded individually.)
    if (!empty($load_entities)) {
      $entities = entity_load($this->index->item_type, array_keys($load_entities));
      foreach ($entities as $entity_id => $entity) {
        $wrappers[$load_entities[$entity_id]] = $this->index->entityWrapper($entity);
      }
    }

    // Apply the relationship, if necessary.
    $type = $this->index->item_type;
    $selector_suffix = '';
    if ($field && ($pos = strrpos($field, ':'))) {
      $selector_suffix = substr($field, 0, $pos);
    }
    if ($selector_suffix || ($relationship && !empty($this->view->relationship[$relationship]))) {
      // Use EntityFieldHandlerHelper to compute the correct data selector for
      // the relationship.
      $handler = (object) array(
        'view' => $this->view,
        'relationship' => $relationship,
        'real_field' => '',
      );
      $selector = EntityFieldHandlerHelper::construct_property_selector($handler);
      $selector .= ($selector ? ':' : '') . $selector_suffix;
      list($type, $wrappers) = EntityFieldHandlerHelper::extract_property_multiple($wrappers, $selector);
    }

    return array($type, $wrappers);
  }

  /**
   * API function for accessing the raw Search API query object.
   *
   * @return SearchApiQueryInterface
   *   The search query object used internally by this handler.
   */
  public function getSearchApiQuery() {
    return $this->query;
  }

  /**
   * API function for accessing the raw Search API results.
   *
   * @return array
   *   An associative array containing the search results, as specified by
   *   SearchApiQueryInterface::execute().
   */
  public function getSearchApiResults() {
    return $this->search_api_results;
  }

  //
  // Query interface methods (proxy to $this->query)
  //

  public function createFilter($conjunction = 'AND') {
    if (!$this->errors) {
      return $this->query->createFilter($conjunction);
    }
  }

  public function keys($keys = NULL) {
    if (!$this->errors) {
      $this->query->keys($keys);
    }
    return $this;
  }

  public function fields(array $fields) {
    if (!$this->errors) {
      $this->query->fields($fields);
    }
    return $this;
  }

  /**
   * Adds a nested filter to the search query object.
   *
   * If $group is given, the filter is added to the relevant filter group
   * instead.
   */
  public function filter(SearchApiQueryFilterInterface $filter, $group = NULL) {
    if (!$this->errors) {
      $this->where[$group]['filters'][] = $filter;
    }
    return $this;
  }

  /**
   * Set a condition on the search query object.
   *
   * If $group is given, the condition is added to the relevant filter group
   * instead.
   */
  public function condition($field, $value, $operator = '=', $group = NULL) {
    if (!$this->errors) {
      $this->where[$group]['conditions'][] = array($field, $value, $operator);
    }
    return $this;
  }

  public function sort($field, $order = 'ASC') {
    if (!$this->errors) {
      $this->query->sort($field, $order);
    }
    return $this;
  }

  public function range($offset = NULL, $limit = NULL) {
    if (!$this->errors) {
      $this->query->range($offset, $limit);
    }
    return $this;
  }

  public function getIndex() {
    return $this->index;
  }

  public function &getKeys() {
    if (!$this->errors) {
      return $this->query->getKeys();
    }
    $ret = NULL;
    return $ret;
  }

  public function getOriginalKeys() {
    if (!$this->errors) {
      return $this->query->getOriginalKeys();
    }
  }

  public function &getFields() {
    if (!$this->errors) {
      return $this->query->getFields();
    }
    $ret = NULL;
    return $ret;
  }

  public function getFilter() {
    if (!$this->errors) {
      return $this->query->getFilter();
    }
  }

  public function &getSort() {
    if (!$this->errors) {
      return $this->query->getSort();
    }
    $ret = NULL;
    return $ret;
  }

  public function getOption($name) {
    if (!$this->errors) {
      return $this->query->getOption($name);
    }
  }

  public function setOption($name, $value) {
    if (!$this->errors) {
      return $this->query->setOption($name, $value);
    }
  }

  public function &getOptions() {
    if (!$this->errors) {
      return $this->query->getOptions();
    }
    $ret = NULL;
    return $ret;
  }

}
