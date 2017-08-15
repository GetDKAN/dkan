<?php

namespace Drupal\DKANExtension\Context;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Defines application features from the specific context.
 */
class ServicesContext extends RawDKANContext {
  private $base_url = '';
  private $cookie_session = '';
  private $csrf_token = '';
  private $endpoints = array();

  // Each node field should be formatted properly before the information is sent on a request.
  // This is a map from 'Field name' -> 'Field format'.
  /**
   * TODO: Move to configuration passed in to constructor.
   */
  private $request_fields_map;

  public function __construct($request_fields_map = array()) {
    $this->request_fields_map = $request_fields_map['request_fields_map'];
  }

  /**
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    parent::gatherContexts($scope);
    $environment = $scope->getEnvironment();
    $this->dkanContext = $environment->getContext('Drupal\DKANExtension\Context\DKANContext');
    $this->datasetContext = $environment->getContext('Drupal\DKANExtension\Context\DatasetContext');
  }

  /**
   * @BeforeScenario
   */
  public function setup(BeforeScenarioScope $scope) {
    // Setup base URL.
    $this->base_url = $this->getMinkParameter('base_url');
  }

  /**
   * @Given endpoints:
   */
  public function endpoints($data) {
    foreach ($data->getHash() as $endpoint_data) {
      $this->endpoints[$endpoint_data['name']] = $endpoint_data['path'];
    }
  }

  /**
   * @Given I use the :arg1 endpoint to login with user :arg2 and pass :arg3
   */
  public function iUseTheEndpointToLoginWithUserAndPass($endpoint, $username, $password) {
    // Build request URL.
    $request_url = $this->base_url . $this->getEndpointPath($endpoint) . '/user/login';
    // Get cookie_session and csrf_token.
    $user_login = $this->services_request_user_login($request_url, $username, $password);
    $this->cookie_session = $user_login['cookie_session'];
    $this->csrf_token = $this->services_request_get_csrf($this->cookie_session, $user_login['curl'], $this->base_url);
  }

  /**
   * @Given I use the :arg1 endpoint to create the nodes:
   */
  public function iUseTheEndpointToCreateTheNodes($endpoint, $nodes) {
    $request_url = $this->base_url . $this->getEndpointPath($endpoint) . '/node';
    // Create nodes.
    foreach ($nodes->getHash() as $node_data) {
      // Get node data.
      $processed_data = $this->build_node_data($node_data);
      // Create node.
      $response = $this->services_request_create_node($processed_data, $this->csrf_token, $this->cookie_session, $request_url);
      // Keep track of all created node.
      $node = node_load($response->nid);
      $wrapper = entity_metadata_wrapper('node', $node);
      $this->dkanContext->entityStore->store('node', $processed_data['type'], $node->nid, $wrapper, $wrapper->label());
    }
    return TRUE;
  }

  /**
   * @Given I use the :arg1 endpoint to attach the file :arg2 to :arg3
   */
  public function iUseTheEndpointToAttachTheFileTo($endpoint, $file_name, $node_name) {
    // Get node.
    $node = $this->dkanContext->entityStore->retrieve_by_name($node_name);
    if ($node) {
      // Build file path.
      $file_path = rtrim(realpath($this->getMinkParameter('files_path')), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file_name;
      if (!is_file($file_path)) {
        throw new Exception(sprintf('The file %s could not be found', $file_name));
      }
      // Prepare file data.
      $file_data = array(
        "files[1]" => curl_file_create($file_path),
        "field_name" => "field_upload",
      // 0 -> replace 1 -> append.
        "attach" => 0,
      );
      // Build request URL.
      $request_url = $this->base_url . $this->getEndpointPath($endpoint) . '/node/' . $node->getIdentifier() . '/attach_file';
      // Attach file.
      $this->services_request_attach_file($file_data, $this->csrf_token, $this->cookie_session, $request_url);
    }
    else {
      throw new Exception(sprintf('The resource could not be found.'));
    }

    return TRUE;
  }

  /**
   * @Given I use the :arg1 endpoint to update the node :arg2 with:
   */
  public function iUseTheEndpointToUpdateTheNodeWith($endpoint, $node_name, $data) {
    // Get node.
    $node = $this->dkanContext->entityStore->retrieve_by_name($node_name);
    if ($node) {
      // Update nodes.
      foreach ($data->getHash() as $node_data) {
        // Get node data.
        $processed_data = $this->build_node_data($node_data, $node);
        // Build request URL.
        $request_url = $this->base_url . $this->getEndpointPath($endpoint) . '/node/' . $node->getIdentifier();
        // Update node.
        $this->services_request_update_node($processed_data, $this->csrf_token, $this->cookie_session, $request_url);
      }
    }
    else {
      throw new Exception(sprintf('The node could not be found.'));
    }
    return TRUE;

  }

  /**
   * @Given I use the :arg1 endpoint to delete the node :arg2
   */
  public function iUseTheEndpointToDeleteTheNode($endpoint, $node_name) {
    // Get node.
    $node = $this->dkanContext->entityStore->retrieve_by_name($node_name);
    if ($node) {
      // Build request URL.
      $request_url = $this->base_url . $this->getEndpointPath($endpoint) . '/node/' . $node->getIdentifier();
      // Delete node.
      $this->services_request_delete_node($this->csrf_token, $this->cookie_session, $request_url);
    }
    else {
      throw new Exception(sprintf('The node could not be found.'));
    }
    return TRUE;
  }

  /**
   * Get path based on endpoint name.
   */
  private function getEndpointPath($endpoint_name) {
    if (isset($this->endpoints[$endpoint_name])) {
      return $this->endpoints[$endpoint_name];
    }
    else {
      throw new Exception(sprintf('The %s endpoint could not be found.', $endpoint_name));
    }
  }

  /**
   * Init CURL object.
   */
  private function services_request_curl_init($request_url, $csrf_token = FALSE) {
    // cURL.
    $curl = curl_init($request_url);
    if ($csrf_token) {
      curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'X-CSRF-Token: ' . $csrf_token,
      ));
    }
    else {
      // Accept JSON response.
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    }
    // Ask to not return Header.
    curl_setopt($curl, CURLOPT_HEADER, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);
    return $curl;
  }

  /**
   * Execute CURL request and process response.
   */
  private function services_request_curl_parse($curl) {
    $response = array();

    $result = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $response['http_code'] = $http_code;

    if ($http_code == 200) {
      $response['success'] = TRUE;
      $response['response'] = json_decode($result);
    }
    else {
      $response['success'] = FALSE;
      $response['response'] = curl_error($curl);
    }

    return $response;
  }

  /**
   * Logs in user.
   */
  private function services_request_user_login($request_url, $username, $password) {
    // User data.
    $user_data = array(
      'username' => $username,
      'password' => $password,
    );
    $user_data = http_build_query($user_data);

    $curl = $this->services_request_curl_init($request_url);
    // Do a regular HTTP POST.
    curl_setopt($curl, CURLOPT_POST, 1);
    // Set POST data.
    curl_setopt($curl, CURLOPT_POSTFIELDS, $user_data);

    $response = $this->services_request_curl_parse($curl);

    if ($response['success']) {
      // Define cookie session.
      $cookie_session = $response['response']->session_name . '=' . $response['response']->sessid;
      return array('cookie_session' => $cookie_session, 'curl' => $curl);
    }
    else {
      throw new \Exception(sprintf('Error: %s', $response['response']));
    }
  }

  /**
   * Retrives CSRF token.
   */
  private function services_request_get_csrf($cookie_session, $curl, $base_url) {
    // GET CSRF TOKEN.
    curl_setopt_array($curl, array(
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => $base_url . '/services/session/token',
    ));
    curl_setopt($curl, CURLOPT_COOKIE, "$cookie_session");

    $ret = new \stdClass();

    $ret->response = curl_exec($curl);
    $ret->error = curl_error($curl);
    $ret->info = curl_getinfo($curl);

    $csrf_token = $ret->response;
    return $csrf_token;
  }

  /**
   * Create node.
   */
  private function services_request_create_node($node_data, $csrf_token, $cookie_session, $request_url) {
    $node_data = http_build_query($node_data);

    $curl = $this->services_request_curl_init($request_url, $csrf_token);
    // Do a regular HTTP POST.
    curl_setopt($curl, CURLOPT_POST, 1);
    // Set POST data.
    curl_setopt($curl, CURLOPT_POSTFIELDS, $node_data);
    // Use the previously saved session.
    curl_setopt($curl, CURLOPT_COOKIE, "$cookie_session");

    $response = $this->services_request_curl_parse($curl);

    if ($response['success']) {
      return $response['response'];
    }
    else {
      throw new \Exception(sprintf('Error: %s', $response['response']));
    }
  }

  /**
   * Update node.
   */
  private function services_request_update_node($node_data, $csrf_token, $cookie_session, $request_url) {

    $node_data = http_build_query($node_data);

    $curl = $this->services_request_curl_init($request_url, $csrf_token);
    // Do a regular HTTP POST.
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
    // Set POST data.
    curl_setopt($curl, CURLOPT_POSTFIELDS, $node_data);
    // Use the previously saved session.
    curl_setopt($curl, CURLOPT_COOKIE, "$cookie_session");

    $response = $this->services_request_curl_parse($curl);

    if ($response['success']) {
      return $response['response'];
    }
    else {
      throw new \Exception(sprintf('Error: %s', $response['response']));
    }
  }

  /**
   * Attach file to node.
   */
  private function services_request_attach_file($file_data, $csrf_token, $cookie_session, $request_url) {

    $curl = $this->services_request_curl_init($request_url, $csrf_token);
    // Add 'Content-Type: multipart/form-data' on header.
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'Content-Type: multipart/form-data',
      'Accept: application/json',
      'X-CSRF-Token: ' . $csrf_token,
    ));
    // Do a regular HTTP POST.
    curl_setopt($curl, CURLOPT_POST, 1);
    // Set POST data.
    curl_setopt($curl, CURLOPT_POSTFIELDS, $file_data);
    // Use the previously saved session.
    curl_setopt($curl, CURLOPT_COOKIE, "$cookie_session");

    $response = $this->services_request_curl_parse($curl);

    if ($response['success']) {
      return $response['response'];
    }
    else {
      throw new \Exception(sprintf('Error: %s', $response['response']));
    }
  }

  /**
   * Delete node.
   */
  private function services_request_delete_node($csrf_token, $cookie_session, $request_url) {

    $curl = $this->services_request_curl_init($request_url, $csrf_token);
    // Set POST data.
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
    // Use the previously saved session.
    curl_setopt($curl, CURLOPT_COOKIE, "$cookie_session");

    $response = $this->services_request_curl_parse($curl);

    if ($response['success']) {
      return $response['response'];
    }
    else {
      throw new \Exception(sprintf('Error: %s', $response['response']));
    }
  }

  /**
   * Build node data as needed by endpoint.
   */
  public function build_node_data($data, $node = NULL) {
    $node_data = array();

    if (!$node && !isset($data['type'])) {
      throw new Exception(sprintf('The "type" column is required.'));
    }

    // Get node type.
    $node_type = ($node) ? $node->getBundle() : $data['type'];

    // Get the rest api field map for the content type.
    $rest_api_fields = $this->request_fields_map[$node_type];

    if ($node_type == "dataset") {
      $this->datasetContext->applyMissingRequiredFields($data);
    }

    foreach ($data as $field => $field_value) {
      if (isset($rest_api_fields[$field])) {
        $node_data[$rest_api_fields[$field]] = $this->process_field($field, $field_value);
      }
    }

    // If the node is being updated then the type of node should not be modified.
    if ($node && isset($node_data['type'])) {
      unset($node_data['type']);
    }

    return $node_data;
  }

  /**
   * Process field if needed to be sent using the rest API.
   */
  private function process_field($field, $field_value) {
    switch ($field) {
      case 'publisher':
      case 'groups':
        if (is_array($field_value)) {
          $field_value = $field_value[0]->nid;
        }

        if (!is_numeric($field_value)) {
          $field_value = (int) db_select('node', 'n')
            ->fields('n', array('nid'))
            ->condition('type', 'group')
            ->condition('title', $field_value)
            ->execute()
            ->fetchField();
        }

        if (is_array($field_value)) {
          $field_value = $field_value[0]->nid;
        }
        break;

      case 'tags':
        if (is_array($field_value)) {
          $field_value = $field_value[0]->name;
        }
        break;

      case 'program code':
        if (is_array($field_value)) {
          $field_value = $field_value[0];
        }
        break;

      case 'resource':
        $resource = $this->dkanContext->entityStore->retrieve_by_name($field_value);
        if ($resource) {
          $field_value = $resource->entityKey('title') . ' (' . $resource->getIdentifier() . ')';
        }
        break;

      case "attest date":
        if (is_numeric($field_value)) {
          $field_value = date('m/d/Y', (int) $field_value);
        }
        break;
    }

    return $field_value;
  }

}
