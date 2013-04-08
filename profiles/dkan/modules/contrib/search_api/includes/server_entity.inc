<?php

/**
 * Class representing a search server.
 *
 * This can handle the same calls as defined in the SearchApiServiceInterface
 * and pass it on to the service implementation appropriate for this server.
 */
class SearchApiServer extends Entity {

  /* Database values that will be set when object is loaded: */

  /**
   * The primary identifier for a server.
   *
   * @var integer
   */
  public $id = 0;

  /**
   * The displayed name for a server.
   *
   * @var string
   */
  public $name = '';

  /**
   * The machine name for a server.
   *
   * @var string
   */
  public $machine_name = '';

  /**
   * The displayed description for a server.
   *
   * @var string
   */
  public $description = '';

  /**
   * The id of the service class to use for this server.
   *
   * @var string
   */
  public $class = '';

  /**
   * The options used to configure the service object.
   *
   * @var array
   */
  public $options = array();

  /**
   * A flag indicating whether the server is enabled.
   *
   * @var integer
   */
  public $enabled = 1;

  /**
   * Proxy object for invoking service methods.
   *
   * @var SearchApiServiceInterface
   */
  protected $proxy;

  /**
   * Constructor as a helper to the parent constructor.
   */
  public function __construct(array $values = array()) {
    parent::__construct($values, 'search_api_server');
  }

  /**
   * Helper method for updating entity properties.
   *
   * NOTE: You shouldn't change any properties of this object before calling
   * this method, as this might lead to the fields not being saved correctly.
   *
   * @param array $fields
   *   The new field values.
   *
   * @return
   *   SAVE_UPDATED on success, FALSE on failure, 0 if the fields already had
   *   the specified values.
   */
  public function update(array $fields) {
    $changeable = array('name' => 1, 'enabled' => 1, 'description' => 1, 'options' => 1);
    $changed = FALSE;
    foreach ($fields as $field => $value) {
      if (isset($changeable[$field]) && $value !== $this->$field) {
        $this->$field = $value;
        $changed = TRUE;
      }
    }
    // If there are no new values, just return 0.
    if (!$changed) {
      return 0;
    }
    return $this->save();
  }

  /**
   * Magic method for determining which fields should be serialized.
   *
   * Serialize all properties except the proxy object.
   *
   * @return array
   *   An array of properties to be serialized.
   */
  public function __sleep() {
    $ret = get_object_vars($this);
    unset($ret['proxy'], $ret['status'], $ret['module'], $ret['is_new']);
    return array_keys($ret);
  }

  /**
   * Helper method for ensuring the proxy object is set up.
   */
  protected function ensureProxy() {
    if (!isset($this->proxy)) {
      $class = search_api_get_service_info($this->class);
      if ($class && class_exists($class['class'])) {
        if (empty($this->options)) {
          // We always have to provide the options.
          $this->options = array();
        }
        $this->proxy = new $class['class']($this);
      }
      if (!($this->proxy instanceof SearchApiServiceInterface)) {
        throw new SearchApiException(t('Search server with machine name @name specifies illegal service class @class.', array('@name' => $this->machine_name, '@class' => $this->class)));
      }
    }
  }

  /**
   * If the service class defines additional methods, not specified in the
   * SearchApiServiceInterface interface, then they are called via this magic
   * method.
   */
  public function __call($name, $arguments = array()) {
    $this->ensureProxy();
    return call_user_func_array(array($this->proxy, $name), $arguments);
  }

  // Proxy methods

  // For increased clarity, and since some parameters are passed by reference,
  // we don't use the __call() magic method for those.

  public function configurationForm(array $form, array &$form_state) {
    $this->ensureProxy();
    return $this->proxy->configurationForm($form, $form_state);
  }

  public function configurationFormValidate(array $form, array &$values, array &$form_state) {
    $this->ensureProxy();
    return $this->proxy->configurationFormValidate($form, $values, $form_state);
  }

  public function configurationFormSubmit(array $form, array &$values, array &$form_state) {
    $this->ensureProxy();
    return $this->proxy->configurationFormSubmit($form, $values, $form_state);
  }

  public function supportsFeature($feature) {
    $this->ensureProxy();
    return $this->proxy->supportsFeature($feature);
  }

  public function viewSettings() {
    $this->ensureProxy();
    return $this->proxy->viewSettings();
  }

  public function postCreate() {
    $this->ensureProxy();
    return $this->proxy->postCreate();
  }

  public function postUpdate() {
    $this->ensureProxy();
    return $this->proxy->postUpdate();
  }

  public function preDelete() {
    $this->ensureProxy();
    return $this->proxy->preDelete();
  }

  public function addIndex(SearchApiIndex $index) {
    $this->ensureProxy();
    return $this->proxy->addIndex($index);
  }

  public function fieldsUpdated(SearchApiIndex $index) {
    $this->ensureProxy();
    return $this->proxy->fieldsUpdated($index);
  }

  public function removeIndex($index) {
    $this->ensureProxy();
    return $this->proxy->removeIndex($index);
  }

  public function indexItems(SearchApiIndex $index, array $items) {
    $this->ensureProxy();
    return $this->proxy->indexItems($index, $items);
  }

  public function deleteItems($ids = 'all', SearchApiIndex $index = NULL) {
    $this->ensureProxy();
    return $this->proxy->deleteItems($ids, $index);
  }

  public function query(SearchApiIndex $index, $options = array()) {
    $this->ensureProxy();
    return $this->proxy->query($index, $options);
  }

  public function search(SearchApiQueryInterface $query) {
    $this->ensureProxy();
    return $this->proxy->search($query);
  }

}
