<?php

/**
 * Interface representing a search query on an Search API index.
 *
 * Methods not returning something else will return the object itself, so calls
 * can be chained.
 */
interface SearchApiQueryInterface {

  /**
   * Constructor used when creating SearchApiQueryInterface objects.
   *
   * @param SearchApiIndex $index
   *   The index the query should be executed on.
   * @param array $options
   *   Associative array of options configuring this query. Recognized options
   *   are:
   *   - conjunction: The type of conjunction to use for this query - either
   *     'AND' or 'OR'. 'AND' by default. This only influences the search keys,
   *     filters will always use AND by default.
   *   - 'parse mode': The mode with which to parse the $keys variable, if it
   *     is set and not already an array. See SearchApiQuery::parseModes() for
   *     recognized parse modes.
   *   - languages: The languages to search for, as an array of language IDs.
   *     If not specified, all languages will be searched. Language-neutral
   *     content (LANGUAGE_NONE) is always searched.
   *   - offset: The position of the first returned search results relative to
   *     the whole result in the index.
   *   - limit: The maximum number of search results to return. -1 means no
   *     limit.
   *   - 'filter class': Can be used to change the SearchApiQueryFilterInterface
   *     implementation to use.
   *   - 'search id': A string that will be used as the identifier when storing
   *     this search in the Search API's static cache.
   *   - search_api_access_account: The account which will be used for entity
   *     access checks, if available and enabled for the index.
   *   - search_api_bypass_access: If set to TRUE, entity access checks will be
   *     skipped, even if enabled for the index.
   *   All options are optional. Third-party modules might define and use other
   *   options not listed here.
   *
   * @throws SearchApiException
   *   If a search on that index (or with those options) won't be possible.
   */
  public function __construct(SearchApiIndex $index, array $options = array());

  /**
   * @return array
   *   An associative array of parse modes recognized by objects of this class.
   *   The keys are the parse modes' ids, values are associative arrays
   *   containing the following entries:
   *   - name: The translated name of the parse mode.
   *   - description: (optional) A translated text describing the parse mode.
   */
  public function parseModes();

  /**
   * Method for creating a filter to use with this query object.
   *
   * @param $conjunction
   *   The conjunction to use for the filter - either 'AND' or 'OR'.
   *
   * @return SearchApiQueryFilterInterface
   *   A filter object that is set to use the specified conjunction.
   */
  public function createFilter($conjunction = 'AND');

  /**
   * Sets the keys to search for. If this method is not called on the query
   * before execution, this will be a filter-only query.
   *
   * @param $keys
   *   A string with the unparsed search keys, or NULL to use no search keys.
   *
   * @return SearchApiQueryInterface
   *   The called object.
   */
  public function keys($keys = NULL);

  /**
   * Sets the fields that will be searched for the search keys. If this is not
   * called, all fulltext fields should be searched.
   *
   * @param array $fields
   *   An array containing fulltext fields that should be searched.
   *
   * @throws SearchApiException
   *   If one of the fields isn't of type "text".
   *
   * @return SearchApiQueryInterface
   *   The called object.
   */
  public function fields(array $fields);

  /**
   * Adds a subfilter to this query's filter.
   *
   * @param SearchApiQueryFilterInterface $filter
   *   A SearchApiQueryFilter object that should be added as a subfilter.
   *
   * @return SearchApiQueryInterface
   *   The called object.
   */
  public function filter(SearchApiQueryFilterInterface $filter);

  /**
   * Add a new ($field $operator $value) condition filter.
   *
   * @param $field
   *   The field to filter on, e.g. 'title'.
   * @param $value
   *   The value the field should have (or be related to by the operator).
   * @param $operator
   *   The operator to use for checking the constraint. The following operators
   *   are supported for primitive types: "=", "<>", "<", "<=", ">=", ">". They
   *   have the same semantics as the corresponding SQL operators.
   *   If $field is a fulltext field, $operator can only be "=" or "<>", which
   *   are in this case interpreted as "contains" or "doesn't contain",
   *   respectively.
   *   If $value is NULL, $operator also can only be "=" or "<>", meaning the
   *   field must have no or some value, respectively.
   *
   * @return SearchApiQueryInterface
   *   The called object.
   */
  public function condition($field, $value, $operator = '=');

  /**
   * Add a sort directive to this search query. If no sort is manually set, the
   * results will be sorted descending by relevance.
   *
   * @param $field
   *   The field to sort by. The special fields 'search_api_relevance' (sort by
   *   relevance) and 'search_api_id' (sort by item id) may be used.
   * @param $order
   *   The order to sort items in - either 'ASC' or 'DESC'.
   *
   * @throws SearchApiException
   *   If the field is multi-valued or of a fulltext type.
   *
   * @return SearchApiQueryInterface
   *   The called object.
   */
  public function sort($field, $order = 'ASC');

  /**
   * Adds a range of results to return. This will be saved in the query's
   * options. If called without parameters, this will remove all range
   * restrictions previously set.
   *
   * @param $offset
   *   The zero-based offset of the first result returned.
   * @param $limit
   *   The number of results to return.
   *
   * @return SearchApiQueryInterface
   *   The called object.
   */
  public function range($offset = NULL, $limit = NULL);

  /**
   * Executes this search query.
   *
   * @return array
   *   An associative array containing the search results. The following keys
   *   are standardized:
   *   - 'result count': The overall number of results for this query, without
   *     range restrictions. Might be approximated, for large numbers.
   *   - results: An array of results, ordered as specified. The array keys are
   *     the items' IDs, values are arrays containing the following keys:
   *     - id: The item's ID.
   *     - score: A float measuring how well the item fits the search.
   *     - fields: (optional) If set, an array containing some field values
   *       already ready-to-use. This allows search engines (or postprocessors)
   *       to store extracted fields so other modules don't have to extract them
   *       again. This fields should always be checked by modules that want to
   *       use field contents of the result items.
   *     - entity: (optional) If set, the fully loaded result item. This field
   *       should always be used by modules using search results, to avoid
   *       duplicate item loads.
   *     - excerpt: (optional) If set, an HTML text containing highlighted
   *       portions of the fulltext that match the query.
   *   - warnings: A numeric array of translated warning messages that may be
   *     displayed to the user.
   *   - ignored: A numeric array of search keys that were ignored for this
   *     search (e.g., because of being too short or stop words).
   *   - performance: An associative array with the time taken (as floats, in
   *     seconds) for specific parts of the search execution:
   *     - complete: The complete runtime of the query.
   *     - hooks: Hook invocations and other client-side preprocessing.
   *     - preprocessing: Preprocessing of the service class.
   *     - execution: The actual query to the search server, in whatever form.
   *     - postprocessing: Preparing the results for returning.
   *   Additional metadata may be returned in other keys. Only 'result count'
   *   and 'result' always have to be set, all other entries are optional.
   */
  public function execute();

  /**
   * Prepares the query object for the search.
   *
   * This method should always be called by execute() and contain all necessary
   * operations before the query is passed to the server's search() method.
   */
  public function preExecute();

  /**
   * Postprocess the search results before they are returned.
   *
   * This method should always be called by execute() and contain all necessary
   * operations after the results are returned from the server.
   *
   * @param array $results
   *   The results returned by the server, which may be altered.
   */
  public function postExecute(array &$results);

  /**
   * @return SearchApiIndex
   *   The search index this query should be executed on.
   */
  public function getIndex();

  /**
   * @return
   *   This object's search keys - either a string or an array specifying a
   *   complex search expression.
   *   An array will contain a '#conjunction' key specifying the conjunction
   *   type, and search strings or nested expression arrays at numeric keys.
   *   Additionally, a '#negation' key might be present, which means – unless it
   *   maps to a FALSE value – that the search keys contained in that array
   *   should be negated, i.e. not be present in returned results. The negation
   *   works on the whole array, not on each contained term individually – i.e.,
   *   with the "AND" conjunction and negation, only results that contain all
   *   the terms in the array should be excluded; with the "OR" conjunction and
   *   negation, all results containing one or more of the terms in the array
   *   should be excluded.
   */
  public function &getKeys();

  /**
   * @return
   *   The unprocessed search keys, exactly as passed to this object. Has the
   *   same format as getKeys().
   */
  public function getOriginalKeys();

  /**
   * @return array
   *   An array containing the fields that should be searched for the search
   *   keys.
   */
  public function &getFields();

  /**
   * @return SearchApiQueryFilterInterface
   *   This object's associated filter object.
   */
  public function getFilter();

  /**
   * @return array
   *   An array specifying the sort order for this query. Array keys are the
   *   field names in order of importance, the values are the respective order
   *   in which to sort the results according to the field.
   */
  public function &getSort();

  /**
   * @param $name string
   *   The name of an option.
   * @param $default mixed
   *   The value to return if the specified option is not set.
   *
   * @return mixed
   *   The value of the option with the specified name, if set. NULL otherwise.
   */
  public function getOption($name, $default = NULL);

  /**
   * @param string $name
   *   The name of an option.
   * @param mixed $value
   *   The new value of the option.
   *
   * @return The option's previous value.
   */
  public function setOption($name, $value);

  /**
   * @return array
   *   An associative array of query options.
   */
  public function &getOptions();

}

/**
 * Standard implementation of SearchApiQueryInterface.
 */
class SearchApiQuery implements SearchApiQueryInterface {

  /**
   * The index.
   *
   * @var SearchApiIndex
   */
  protected $index;

  /**
   * The search keys. If NULL, this will be a filter-only search.
   *
   * @var mixed
   */
  protected $keys;

  /**
   * The unprocessed search keys, as passed to the keys() method.
   *
   * @var mixed
   */
  protected $orig_keys;

  /**
   * The fields that will be searched for the keys.
   *
   * @var array
   */
  protected $fields;

  /**
   * The search filter associated with this query.
   *
   * @var SearchApiQueryFilterInterface
   */
  protected $filter;

  /**
   * The sort associated with this query.
   *
   * @var array
   */
  protected $sort;

  /**
   * Search options configuring this query.
   *
   * @var array
   */
  protected $options;

  /**
   * Count for providing a unique ID.
   */
  protected static $count = 0;

  /**
   * Constructor for SearchApiQuery objects.
   *
   * @param SearchApiIndex $index
   *   The index the query should be executed on.
   * @param array $options
   *   Associative array of options configuring this query. Recognized options
   *   are:
   *   - conjunction: The type of conjunction to use for this query - either
   *     'AND' or 'OR'. 'AND' by default. This only influences the search keys,
   *     filters will always use AND by default.
   *   - 'parse mode': The mode with which to parse the $keys variable, if it
   *     is set and not already an array. See SearchApiQuery::parseModes() for
   *     recognized parse modes.
   *   - languages: The languages to search for, as an array of language IDs.
   *     If not specified, all languages will be searched. Language-neutral
   *     content (LANGUAGE_NONE) is always searched.
   *   - offset: The position of the first returned search results relative to
   *     the whole result in the index.
   *   - limit: The maximum number of search results to return. -1 means no
   *     limit.
   *   - 'filter class': Can be used to change the SearchApiQueryFilterInterface
   *     implementation to use.
   *   - 'search id': A string that will be used as the identifier when storing
   *     this search in the Search API's static cache.
   *   - search_api_access_account: The account which will be used for entity
   *     access checks, if available and enabled for the index.
   *   - search_api_bypass_access: If set to TRUE, entity access checks will be
   *     skipped, even if enabled for the index.
   *   All options are optional. Third-party modules might define and use other
   *   options not listed here.
   *
   * @throws SearchApiException
   *   If a search on that index (or with those options) won't be possible.
   */
  public function __construct(SearchApiIndex $index, array $options = array()) {
    if (empty($index->options['fields'])) {
      throw new SearchApiException(t("Can't search an index which hasn't got any fields defined."));
    }
    if (empty($index->enabled)) {
      throw new SearchApiException(t("Can't search a disabled index."));
    }
    if (isset($options['parse mode'])) {
      $modes = $this->parseModes();
      if (!isset($modes[$options['parse mode']])) {
        throw new SearchApiException(t('Unknown parse mode: @mode.', array('@mode' => $options['parse mode'])));
      }
    }
    $this->index = $index;
    $this->options = $options + array(
      'conjunction' => 'AND',
      'parse mode' => 'terms',
      'filter class' => 'SearchApiQueryFilter',
      'search id' => __CLASS__,
    );
    $this->filter = $this->createFilter('AND');
    $this->sort = array();
  }

  /**
   * @return array
   *   An associative array of parse modes recognized by objects of this class.
   *   The keys are the parse modes' ids, values are associative arrays
   *   containing the following entries:
   *   - name: The translated name of the parse mode.
   *   - description: (optional) A translated text describing the parse mode.
   */
  public function parseModes() {
    $modes['direct'] = array(
      'name' => t('Direct query'),
      'description' => t("Don't parse the query, just hand it to the search server unaltered. " .
          "Might fail if the query contains syntax errors in regard to the specific server's query syntax."),
    );
    $modes['single'] = array(
      'name' => t('Single term'),
      'description' => t('The query is interpreted as a single keyword, maybe containing spaces or special characters.'),
    );
    $modes['terms'] = array(
      'name' => t('Multiple terms'),
      'description' => t('The query is interpreted as multiple keywords seperated by spaces. ' .
          'Keywords containing spaces may be "quoted". Quoted keywords must still be seperated by spaces.'),
    );
    // @todo Add fourth mode for complicated expressions, e.g.: »"vanilla ice" OR (love NOT hate)«
    return $modes;
  }

  /**
   * Parses the keys string according to the $mode parameter.
   *
   * @return
   *   The parsed keys. Either a string or an array.
   */
  protected function parseKeys($keys, $mode) {
    if ($keys === NULL || is_array($keys)) {
      return $keys;
    }
    $keys = '' . $keys;
    switch ($mode) {
      case 'direct':
        return $keys;

      case 'single':
        return array('#conjunction' => $this->options['conjunction'], $keys);

      case 'terms':
        $ret = explode(' ', $keys);
        $quoted = FALSE;
        $str = '';
        foreach ($ret as $k => $v) {
          if (!$v) {
            continue;
          }
          if ($quoted) {
            if (substr($v, -1) == '"') {
              $v = substr($v, 0, -1);
              $str .= ' ' . $v;
              $ret[$k] = $str;
              $quoted = FALSE;
            }
            else {
              $str .= ' ' . $v;
              unset($ret[$k]);
            }
          }
          elseif ($v[0] == '"') {
            $len = strlen($v);
            if ($len > 1 && $v[$len-1] == '"') {
              $ret[$k] = substr($v, 1, -1);
            }
            else {
              $str = substr($v, 1);
              $quoted = TRUE;
              unset($ret[$k]);
            }
          }
        }
        if ($quoted) {
          $ret[] = $str;
        }
        $ret['#conjunction'] = $this->options['conjunction'];
        return array_filter($ret);
    }
  }

  /**
   * Method for creating a filter to use with this query object.
   *
   * @param $conjunction
   *   The conjunction to use for the filter - either 'AND' or 'OR'.
   *
   * @return SearchApiQueryFilterInterface
   *   A filter object that is set to use the specified conjunction.
   */
  public function createFilter($conjunction = 'AND') {
    $filter_class = $this->options['filter class'];
    return new $filter_class($conjunction);
  }

  /**
   * Sets the keys to search for. If this method is not called on the query
   * before execution, this will be a filter-only query.
   *
   * @param $keys
   *   A string with the unparsed search keys, or NULL to use no search keys.
   *
   * @return SearchApiQuery
   *   The called object.
   */
  public function keys($keys = NULL) {
    $this->orig_keys = $keys;
    if (isset($keys)) {
      $this->keys = $this->parseKeys($keys, $this->options['parse mode']);
    }
    else {
      $this->keys = NULL;
    }
    return $this;
  }
  /**
   * Sets the fields that will be searched for the search keys. If this is not
   * called, all fulltext fields should be searched.
   *
   * @param array $fields
   *   An array containing fulltext fields that should be searched.
   *
   * @throws SearchApiException
   *   If one of the fields isn't of type "text".
   *
   * @return SearchApiQueryInterface
   *   The called object.
   */
  public function fields(array $fields) {
    $fulltext_fields = $this->index->getFulltextFields();
    foreach (array_diff($fields, $fulltext_fields) as $field) {
      throw new SearchApiException(t('Trying to search on field @field which is no indexed fulltext field.', array('@field' => $field)));
    }
    $this->fields = $fields;
    return $this;
  }

  /**
   * Adds a subfilter to this query's filter.
   *
   * @param SearchApiQueryFilterInterface $filter
   *   A SearchApiQueryFilter object that should be added as a subfilter.
   *
   * @return SearchApiQuery
   *   The called object.
   */
  public function filter(SearchApiQueryFilterInterface $filter) {
    $this->filter->filter($filter);
    return $this;
  }

  /**
   * Add a new ($field $operator $value) condition filter.
   *
   * @param $field
   *   The field to filter on, e.g. 'title'.
   * @param $value
   *   The value the field should have (or be related to by the operator).
   * @param $operator
   *   The operator to use for checking the constraint. The following operators
   *   are supported for primitive types: "=", "<>", "<", "<=", ">=", ">". They
   *   have the same semantics as the corresponding SQL operators.
   *   If $field is a fulltext field, $operator can only be "=" or "<>", which
   *   are in this case interpreted as "contains" or "doesn't contain",
   *   respectively.
   *   If $value is NULL, $operator also can only be "=" or "<>", meaning the
   *   field must have no or some value, respectively.
   *
   * @return SearchApiQuery
   *   The called object.
   */
  public function condition($field, $value, $operator = '=') {
    $this->filter->condition($field, $value, $operator);
    return $this;
  }

  /**
   * Add a sort directive to this search query. If no sort is manually set, the
   * results will be sorted descending by relevance.
   *
   * @param $field
   *   The field to sort by. The special fields 'search_api_relevance' (sort by
   *   relevance) and 'search_api_id' (sort by item id) may be used.
   * @param $order
   *   The order to sort items in - either 'ASC' or 'DESC'.
   *
   * @throws SearchApiException
   *   If the field is multi-valued or of a fulltext type.
   *
   * @return SearchApiQuery
   *   The called object.
   */
  public function sort($field, $order = 'ASC') {
    $fields = $this->index->options['fields'];
    $fields += array(
      'search_api_relevance' => array('type' => 'decimal'),
      'search_api_id' => array('type' => 'integer'),
    );
    if (empty($fields[$field])) {
      throw new SearchApiException(t('Trying to sort on unknown field @field.', array('@field' => $field)));
    }
    $type = $fields[$field]['type'];
    if (search_api_is_list_type($type) || search_api_is_text_type($type)) {
      throw new SearchApiException(t('Trying to sort on field @field of illegal type @type.', array('@field' => $field, '@type' => $type)));
    }
    $order = strtoupper(trim($order)) == 'DESC' ? 'DESC' : 'ASC';
    $this->sort[$field] = $order;
    return $this;
  }

  /**
   * Adds a range of results to return. This will be saved in the query's
   * options. If called without parameters, this will remove all range
   * restrictions previously set.
   *
   * @param $offset
   *   The zero-based offset of the first result returned.
   * @param $limit
   *   The number of results to return.
   *
   * @return SearchApiQueryInterface
   *   The called object.
   */
  public function range($offset = NULL, $limit = NULL) {
    $this->options['offset'] = $offset;
    $this->options['limit'] = $limit;
    return $this;
  }


  /**
   * Executes this search query.
   *
   * @return array
   *   An associative array containing the search results. The following keys
   *   are standardized:
   *   - 'result count': The overall number of results for this query, without
   *     range restrictions. Might be approximated, for large numbers.
   *   - results: An array of results, ordered as specified. The array keys are
   *     the items' IDs, values are arrays containing the following keys:
   *     - id: The item's ID.
   *     - score: A float measuring how well the item fits the search.
   *     - fields: (optional) If set, an array containing some field values
   *       already ready-to-use. This allows search engines (or postprocessors)
   *       to store extracted fields so other modules don't have to extract them
   *       again. This fields should always be checked by modules that want to
   *       use field contents of the result items.
   *     - entity: (optional) If set, the fully loaded result item. This field
   *       should always be used by modules using search results, to avoid
   *       duplicate item loads.
   *     - excerpt: (optional) If set, an HTML text containing highlighted
   *       portions of the fulltext that match the query.
   *   - warnings: A numeric array of translated warning messages that may be
   *     displayed to the user.
   *   - ignored: A numeric array of search keys that were ignored for this
   *     search (e.g., because of being too short or stop words).
   *   - performance: An associative array with the time taken (as floats, in
   *     seconds) for specific parts of the search execution:
   *     - complete: The complete runtime of the query.
   *     - hooks: Hook invocations and other client-side preprocessing.
   *     - preprocessing: Preprocessing of the service class.
   *     - execution: The actual query to the search server, in whatever form.
   *     - postprocessing: Preparing the results for returning.
   *   Additional metadata may be returned in other keys. Only 'result count'
   *   and 'result' always have to be set, all other entries are optional.
   */
  public final function execute() {
    $start = microtime(TRUE);

    // Prepare the query for execution by the server.
    $this->preExecute();

    $pre_search = microtime(TRUE);

    // Execute query.
    $response = $this->index->server()->search($this);

    $post_search = microtime(TRUE);

    // Postprocess the search results.
    $this->postExecute($response);

    $end = microtime(TRUE);
    $response['performance']['complete'] = $end - $start;
    $response['performance']['hooks'] = $response['performance']['complete'] - ($post_search - $pre_search);

    // Store search for later retrieval for facets, etc.
    search_api_current_search(NULL, $this, $response);

    return $response;
  }

  /**
   * Helper method for adding a language filter.
   */
  protected function addLanguages(array $languages) {
    if (array_search(LANGUAGE_NONE, $languages) === FALSE) {
      $languages[] = LANGUAGE_NONE;
    }

    $languages = drupal_map_assoc($languages);
    $langs_to_add = $languages;
    $filters = $this->filter->getFilters();
    while ($filters && $langs_to_add) {
      $filter = array_shift($filters);
      if (is_array($filter)) {
        if ($filter[0] == 'search_api_language' && $filter[2] == '=') {
          $lang = $filter[1];
          if (isset($languages[$lang])) {
            unset($langs_to_add[$lang]);
          }
          else {
            throw new SearchApiException(t('Impossible combination of filters and languages. There is a filter for "@language", but allowed languages are: "@languages".', array('@language' => $lang, '@languages' => implode('", "', $languages))));
          }
        }
      }
      else {
        if ($filter->getConjunction() == 'AND') {
          $filters += $filter->getFilters();
        }
      }
    }
    if ($langs_to_add) {
      if (count($langs_to_add) == 1) {
        $this->condition('search_api_language', reset($langs_to_add));
      }
      else {
        $filter = $this->createFilter('OR');
        foreach ($langs_to_add as $lang) {
          $filter->condition('search_api_language', $lang);
        }
        $this->filter($filter);
      }
    }
  }

  /**
   * Prepares the query object for the search.
   *
   * This method should always be called by execute() and contain all necessary
   * operations before the query is passed to the server's search() method.
   */
  public function preExecute() {
    // Add filter for languages.
    if (isset($this->options['languages'])) {
      $this->addLanguages($this->options['languages']);
    }

    // Add fulltext fields, unless set
    if ($this->fields === NULL) {
      $this->fields = $this->index->getFulltextFields();
    }

    // Preprocess query.
    $this->index->preprocessSearchQuery($this);

    // Let modules alter the query.
    drupal_alter('search_api_query', $this);
  }

  /**
   * Postprocess the search results before they are returned.
   *
   * This method should always be called by execute() and contain all necessary
   * operations after the results are returned from the server.
   *
   * @param array $results
   *   The results returned by the server, which may be altered.
   */
  public function postExecute(array &$results) {
    // Postprocess results.
    $this->index->postprocessSearchResults($results, $this);
  }

  /**
   * @return SearchApiIndex
   *   The search index this query will be executed on.
   */
  public function getIndex() {
    return $this->index;
  }

  /**
   * @return
   *   This object's search keys - either a string or an array specifying a
   *   complex search expression.
   *   An array will contain a '#conjunction' key specifying the conjunction
   *   type, and search strings or nested expression arrays at numeric keys.
   *   Additionally, a '#negation' key might be present, which means – unless it
   *   maps to a FALSE value – that the search keys contained in that array
   *   should be negated, i.e. not be present in returned results. The negation
   *   works on the whole array, not on each contained term individually – i.e.,
   *   with the "AND" conjunction and negation, only results that contain all
   *   the terms in the array should be excluded; with the "OR" conjunction and
   *   negation, all results containing one or more of the terms in the array
   *   should be excluded.
   */
  public function &getKeys() {
    return $this->keys;
  }

  /**
   * @return
   *   The unprocessed search keys, exactly as passed to this object. Has the
   *   same format as getKeys().
   */
  public function getOriginalKeys() {
    return $this->orig_keys;
  }

  /**
   * @return array
   *   An array containing the fields that should be searched for the search
   *   keys.
   */
  public function &getFields() {
    return $this->fields;
  }

  /**
   * @return SearchApiQueryFilterInterface
   *   This object's associated filter object.
   */
  public function getFilter() {
    return $this->filter;
  }

  /**
   * @return array
   *   An array specifying the sort order for this query. Array keys are the
   *   field names in order of importance, the values are the respective order
   *   in which to sort the results according to the field.
   */
  public function &getSort() {
    return $this->sort;
  }

  /**
   * @param $name string
   *   The name of an option.
   *
   * @return mixed
   *   The option with the specified name, if set, or NULL otherwise.
   */
  public function getOption($name, $default = NULL) {
    return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
  }

  /**
   * @param string $name
   *   The name of an option.
   * @param mixed $value
   *   The new value of the option.
   *
   * @return The option's previous value.
   */
  public function setOption($name, $value) {
    $old = $this->getOption($name);
    $this->options[$name] = $value;
    return $old;
  }

  /**
   * @return array
   *   An associative array of query options.
   */
  public function &getOptions() {
    return $this->options;
  }

}

/**
 * Interface representing a search query filter, that filters on one or more
 * fields with a specific conjunction (AND or OR).
 *
 * Methods not noting otherwise will return the object itself, so calls can be
 * chained.
 */
interface SearchApiQueryFilterInterface {

  /**
   * Constructs a new filter that uses the specified conjunction.
   *
   * @param $conjunction
   *   The conjunction to use for this filter - either 'AND' or 'OR'.
   */
  public function __construct($conjunction = 'AND');

  /**
   * Sets this filter's conjunction.
   *
   * @param $conjunction
   *   The conjunction to use for this filter - either 'AND' or 'OR'.
   *
   * @return SearchApiQueryFilterInterface
   *   The called object.
   */
  public function setConjunction($conjunction);

  /**
   * Adds a subfilter.
   *
   * @param $filter
   *   A SearchApiQueryFilterInterface object that should be added as a
   *   subfilter.
   *
   * @return SearchApiQueryFilterInterface
   *   The called object.
   */
  public function filter(SearchApiQueryFilterInterface $filter);

  /**
   * Add a new ($field $operator $value) condition.
   *
   * @param $field
   *   The field to filter on, e.g. 'title'.
   * @param $value
   *   The value the field should have (or be related to by the operator).
   * @param $operator
   *   The operator to use for checking the constraint. The following operators
   *   are supported for primitive types: "=", "<>", "<", "<=", ">=", ">". They
   *   have the same semantics as the corresponding SQL operators.
   *   If $field is a fulltext field, $operator can only be "=" or "<>", which
   *   are in this case interpreted as "contains" or "doesn't contain",
   *   respectively.
   *   If $value is NULL, $operator also can only be "=" or "<>", meaning the
   *   field must have no or some value, respectively.
   *
   * @return SearchApiQueryFilterInterface
   *   The called object.
   */
  public function condition($field, $value, $operator = '=');

  /**
   * @return
   *   The conjunction used by this filter - either 'AND' or 'OR'.
   */
  public function getConjunction();

  /**
   * @return array
   *   An array containing this filter's subfilters. Each of these is either an
   *   array (field, value, operator), or another SearchApiFilter object.
   */
  public function &getFilters();

}

/**
 * Standard implementation of SearchApiQueryFilterInterface.
 */
class SearchApiQueryFilter implements SearchApiQueryFilterInterface {

  /**
   * Array containing subfilters. Each of these is either an array
   * (field, value, operator), or another SearchApiFilter object.
   */
  protected $filters;

  /** String specifying this filter's conjunction ('AND' or 'OR'). */
  protected $conjunction;

  /**
   * Constructs a new filter that uses the specified conjunction.
   *
   * @param $conjunction
   *   The conjunction to use for this filter - either 'AND' or 'OR'.
   */
  public function __construct($conjunction = 'AND') {
    $this->setConjunction($conjunction);
    $this->filters = array();
  }

  /**
   * Sets this filter's conjunction.
   *
   * @param $conjunction
   *   The conjunction to use for this filter - either 'AND' or 'OR'.
   *
   * @return SearchApiQueryFilter
   *   The called object.
   */
  public function setConjunction($conjunction) {
    $this->conjunction = strtoupper(trim($conjunction)) == 'OR' ? 'OR' : 'AND';
    return $this;
  }

  /**
   * Adds a subfilter.
   *
   * @param $filter
   *   A SearchApiQueryFilterInterface object that should be added as a
   *   subfilter.
   *
   * @return SearchApiQueryFilter
   *   The called object.
   */
  public function filter(SearchApiQueryFilterInterface $filter) {
    $this->filters[] = $filter;
    return $this;
  }

  /**
   * Add a new ($field $operator $value) condition.
   *
   * @param $field
   *   The field to filter on, e.g. 'title'.
   * @param $value
   *   The value the field should have (or be related to by the operator).
   * @param $operator
   *   The operator to use for checking the constraint. The following operators
   *   are supported for primitive types: "=", "<>", "<", "<=", ">=", ">". They
   *   have the same semantics as the corresponding SQL operators.
   *   If $field is a fulltext field, $operator can only be "=" or "<>", which
   *   are in this case interpreted as "contains" or "doesn't contain",
   *   respectively.
   *   If $value is NULL, $operator also can only be "=" or "<>", meaning the
   *   field must have no or some value, respectively.
   *
   * @return SearchApiQueryFilter
   *   The called object.
   */
  public function condition($field, $value, $operator = '=') {
    $this->filters[] = array($field, $value, $operator);
    return $this;
  }

  /**
   * @return
   *   The conjunction used by this filter - either 'AND' or 'OR'.
   */
  public function getConjunction() {
    return $this->conjunction;
  }

  /**
   * @return array
   *   An array containing this filter's subfilters. Each of these is either an
   *   array (field, value, operator), or another SearchApiFilter object.
   */
  public function &getFilters() {
    return $this->filters;
  }

}
