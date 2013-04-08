<?php

/**
 * Interface defining the methods search services have to implement.
 *
 * Before a service object is used, the corresponding server's data will be read
 * from the database (see SearchApiAbstractService for a list of fields).
 */
interface SearchApiServiceInterface {

  /**
   * Constructor for a service class, setting the server configuration used with
   * this service.
   *
   * @param SearchApiServer $server
   *   The server object for this service.
   */
  public function __construct(SearchApiServer $server);

  /**
   * Form callback. Might be called on an uninitialized object - in this case,
   * the form is for configuring a newly created server.
   *
   * @return array
   *   A form array for setting service-specific options.
   */
  public function configurationForm(array $form, array &$form_state);

  /**
   * Validation callback for the form returned by configurationForm().
   *
   * $form_state['server'] will contain the server that is created or edited.
   * Use form_error() to flag errors on form elements.
   *
   * @param array $form
   *   The form returned by configurationForm().
   * @param array $values
   *   The part of the $form_state['values'] array corresponding to this form.
   * @param array $form_state
   *   The complete form state.
   */
  public function configurationFormValidate(array $form, array &$values, array &$form_state);

  /**
   * Submit callback for the form returned by configurationForm().
   *
   * This method should set the options of this service' server according to
   * $values.
   *
   * @param array $form
   *   The form returned by configurationForm().
   * @param array $values
   *   The part of the $form_state['values'] array corresponding to this form.
   * @param array $form_state
   *   The complete form state.
   */
  public function configurationFormSubmit(array $form, array &$values, array &$form_state);

  /**
   * Determines whether this service class implementation supports a given
   * feature. Features are optional extensions to Search API functionality and
   * usually defined and used by third-party modules.
   *
   * There are currently three features defined directly in the Search API
   * project:
   * - "search_api_facets", by the search_api_facetapi module.
   * - "search_api_facets_operator_or", also by the search_api_facetapi module.
   * - "search_api_mlt", by the search_api_views module.
   * Other contrib modules might define additional features. These should always
   * be properly documented in the module by which they are defined.
   *
   * @param string $feature
   *   The name of the optional feature.
   *
   * @return boolean
   *   TRUE if this service knows and supports the specified feature. FALSE
   *   otherwise.
   */
  public function supportsFeature($feature);

  /**
   * View this server's settings. Output can be HTML or a render array, a <dl>
   * listing all relevant settings is preferred.
   */
  public function viewSettings();

  /**
   * Called once, when the server is first created. Allows it to set up its
   * necessary infrastructure.
   */
  public function postCreate();

  /**
   * Notifies this server that its fields are about to be updated. The server's
   * $original property can be used to inspect the old property values.
   *
   * @return
   *   TRUE, if the update requires reindexing of all content on the server.
   */
  public function postUpdate();

  /**
   * Notifies this server that it is about to be deleted from the database and
   * should therefore clean up, if appropriate.
   *
   * Note that you shouldn't call the server's save() method, or any
   * methods that might do that, from inside of this method as the server isn't
   * present in the database anymore at this point.
   */
  public function preDelete();

  /**
   * Add a new index to this server.
   *
   * If the index was already added to the server, the object should treat this
   * as if removeIndex() and then addIndex() were called.
   *
   * @param SearchApiIndex $index
   *   The index to add.
   */
  public function addIndex(SearchApiIndex $index);

  /**
   * Notify the server that the indexed field settings for the index have
   * changed.
   * If any user action is necessary as a result of this, the method should
   * use drupal_set_message() to notify the user.
   *
   * @param SearchApiIndex $index
   *   The updated index.
   *
   * @return
   *   TRUE, if this change affected the server in any way that forces it to
   *   re-index the content. FALSE otherwise.
   */
  public function fieldsUpdated(SearchApiIndex $index);

  /**
   * Remove an index from this server.
   *
   * This might mean that the index has been deleted, or reassigned to a
   * different server. If you need to distinguish between these cases, inspect
   * $index->server.
   *
   * If the index wasn't added to the server, the method call should be ignored.
   *
   * @param $index
   *   Either an object representing the index to remove, or its machine name
   *   (if the index was completely deleted).
   */
  public function removeIndex($index);

  /**
   * Index the specified items.
   *
   * @param SearchApiIndex $index
   *   The search index for which items should be indexed.
   * @param array $items
   *   An array of items to be indexed, keyed by their id. The values are
   *   associative arrays of the fields to be stored, where each field is an
   *   array with the following keys:
   *   - type: One of the data types recognized by the Search API, or the
   *     special type "tokens" for fulltext fields.
   *   - original_type: The original type of the property, as defined by the
   *     datasource controller for the index's item type.
   *   - value: The value to index.
   *
   *   The special field "search_api_language" contains the item's language and
   *   should always be indexed.
   *
   *   The value of fields with the "tokens" type is an array of tokens. Each
   *   token is an array containing the following keys:
   *   - value: The word that the token represents.
   *   - score: A score for the importance of that word.
   *
   * @return array
   *   An array of the ids of all items that were successfully indexed.
   *
   * @throws SearchApiException
   *   If indexing was prevented by a fundamental configuration error.
   */
  public function indexItems(SearchApiIndex $index, array $items);

  /**
   * Delete items from an index on this server.
   *
   * Might be either used to delete some items (given by their ids) from a
   * specified index, or all items from that index, or all items from all
   * indexes on this server.
   *
   * @param $ids
   *   Either an array containing the ids of the items that should be deleted,
   *   or 'all' if all items should be deleted. Other formats might be
   *   recognized by implementing classes, but these are not standardized.
   * @param SearchApiIndex $index
   *   The index from which items should be deleted, or NULL if all indexes on
   *   this server should be cleared (then, $ids has to be 'all').
   */
  public function deleteItems($ids = 'all', SearchApiIndex $index = NULL);

  /**
   * Create a query object for searching on an index lying on this server.
   *
   * @param SearchApiIndex $index
   *   The index to search on.
   * @param $options
   *   Associative array of options configuring this query. See
   *   SearchApiQueryInterface::__construct().
   *
   * @return SearchApiQueryInterface
   *   An object for searching the given index.
   *
   * @throws SearchApiException
   *   If the server is currently disabled.
   */
  public function query(SearchApiIndex $index, $options = array());

  /**
   * Executes a search on the server represented by this object.
   *
   * @param $query
   *   The SearchApiQueryInterface object to execute.
   *
   * @return array
   *   An associative array containing the search results, as required by
   *   SearchApiQueryInterface::execute().
   *
   * @throws SearchApiException
   *   If an error prevented the search from completing.
   */
  public function search(SearchApiQueryInterface $query);

}

/**
 * Abstract class with generic implementation of most service methods.
 */
abstract class SearchApiAbstractService implements SearchApiServiceInterface {

  /**
   * @var SearchApiServer
   */
  protected $server;

  /**
   * Direct reference to the server's $options property.
   *
   * @var array
   */
  protected $options = array();

  /**
   * Constructor for a service class, setting the server configuration used with
   * this service.
   *
   * The default implementation sets $this->server and $this->options.
   *
   * @param SearchApiServer $server
   *   The server object for this service.
   */
  public function __construct(SearchApiServer $server) {
    $this->server = $server;
    $this->options = &$server->options;
  }

  /**
   * Form callback. Might be called on an uninitialized object - in this case,
   * the form is for configuring a newly created server.
   *
   * Returns an empty form by default.
   *
   * @return array
   *   A form array for setting service-specific options.
   */
  public function configurationForm(array $form, array &$form_state) {
    return array();
  }

  /**
   * Validation callback for the form returned by configurationForm().
   *
   * Does nothing by default.
   *
   * @param array $form
   *   The form returned by configurationForm().
   * @param array $values
   *   The part of the $form_state['values'] array corresponding to this form.
   * @param array $form_state
   *   The complete form state.
   */
  public function configurationFormValidate(array $form, array &$values, array &$form_state) {
    return;
  }

  /**
   * Submit callback for the form returned by configurationForm().
   *
   * The default implementation just ensures that additional elements in
   * $options, not present in the form, don't get lost at the update.
   *
   * @param array $form
   *   The form returned by configurationForm().
   * @param array $values
   *   The part of the $form_state['values'] array corresponding to this form.
   * @param array $form_state
   *   The complete form state.
   */
  public function configurationFormSubmit(array $form, array &$values, array &$form_state) {
    if (!empty($this->options)) {
      $values += $this->options;
    }
    $this->options = $values;
  }

  /**
   * Determines whether this service class implementation supports a given
   * feature. Features are optional extensions to Search API functionality and
   * usually defined and used by third-party modules.
   *
   * There are currently three features defined directly in the Search API
   * project:
   * - "search_api_facets", by the search_api_facetapi module.
   * - "search_api_facets_operator_or", also by the search_api_facetapi module.
   * - "search_api_mlt", by the search_api_views module.
   * Other contrib modules might define additional features. These should always
   * be properly documented in the module by which they are defined.
   *
   * @param string $feature
   *   The name of the optional feature.
   *
   * @return boolean
   *   TRUE if this service knows and supports the specified feature. FALSE
   *   otherwise.
   */
  public function supportsFeature($feature) {
    return FALSE;
  }

  /**
   * View this server's settings. Output can be HTML or a render array, a <dl>
   * listing all relevant settings is preferred.
   *
   * The default implementation does a crude output as a definition list, with
   * option names taken from the configuration form.
   */
  public function viewSettings() {
    $output = '';
    $form = $form_state = array();
    $option_form = $this->configurationForm($form, $form_state);
    $option_names = array();
    foreach ($option_form as $key => $element) {
      if (isset($element['#title']) && isset($this->options[$key])) {
        $option_names[$key] = $element['#title'];
      }
    }

    foreach ($option_names as $key => $name) {
      $value = $this->options[$key];
      $output .= '<dt>' . check_plain($name) . '</dt>' . "\n";
      $output .= '<dd>' . nl2br(check_plain(print_r($value, TRUE))) . '</dd>' . "\n";
    }

    return $output ? "<dl>\n$output</dl>" : '';
  }

  /**
   * Called once, when the server is first created. Allows it to set up its
   * necessary infrastructure.
   *
   * Does nothing, by default.
   */
  public function postCreate() {
    return;
  }

  /**
   * Notifies this server that its fields are about to be updated. The server's
   * $original property can be used to inspect the old property values.
   *
   * @return
   *   TRUE, if the update requires reindexing of all content on the server.
   */
  public function postUpdate() {
    return FALSE;
  }

  /**
   * Notifies this server that it is about to be deleted from the database and
   * should therefore clean up, if appropriate.
   *
   * Note that you shouldn't call the server's save() method, or any
   * methods that might do that, from inside of this method as the server isn't
   * present in the database anymore at this point.
   *
   * By default, deletes all indexes from this server.
   */
  public function preDelete() {
    $indexes = search_api_index_load_multiple(FALSE, array('server' => $this->server->machine_name));
    foreach ($indexes as $index) {
      $this->removeIndex($index);
    }
  }

  /**
   * Add a new index to this server.
   *
   * Does nothing, by default.
   *
   * @param SearchApiIndex $index
   *   The index to add.
   */
  public function addIndex(SearchApiIndex $index) {
    return;
  }

  /**
   * Notify the server that the indexed field settings for the index have
   * changed.
   * If any user action is necessary as a result of this, the method should
   * use drupal_set_message() to notify the user.
   *
   * @param SearchApiIndex $index
   *   The updated index.
   *
   * @return
   *   TRUE, if this change affected the server in any way that forces it to
   *   re-index the content. FALSE otherwise.
   */
  public function fieldsUpdated(SearchApiIndex $index) {
    return FALSE;
  }

  /**
   * Remove an index from this server.
   *
   * This might mean that the index has been deleted, or reassigned to a
   * different server. If you need to distinguish between these cases, inspect
   * $index->server.
   *
   * By default, removes all items from that index.
   *
   * @param $index
   *   Either an object representing the index to remove, or its machine name
   *   (if the index was completely deleted).
   */
  public function removeIndex($index) {
    $this->deleteItems('all', $index);
  }

  /**
   * Create a query object for searching on an index lying on this server.
   *
   * @param SearchApiIndex $index
   *   The index to search on.
   * @param $options
   *   Associative array of options configuring this query. See
   *   SearchApiQueryInterface::__construct().
   *
   * @return SearchApiQueryInterface
   *   An object for searching the given index.
   *
   * @throws SearchApiException
   *   If the server is currently disabled.
   */
  public function query(SearchApiIndex $index, $options = array()) {
    return new SearchApiQuery($index, $options);
  }

}
