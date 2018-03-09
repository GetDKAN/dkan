<?php

namespace Drupal\DKANExtension\Context;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Defines application features from the specific context.
 */
class ServicesContext extends RawDKANContext {
  private $base_url = '';
  private $csrf_token = '';
  private $endpoints = array();

  private $client;

  // Each node field should be formatted properly before the information is sent on a request.
  // This is a map from 'Field name' -> 'Field format'.
  /**
   * TODO: Move to configuration passed in to constructor.
   */
  private $request_fields_map;

  public function __construct($request_fields_map = array()) {
    $this->request_fields_map = $request_fields_map['request_fields_map'];
    $this->client = new GuzzleClient(['cookies' => true]);
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

    $data = array(
      'username' => $username,
      'password' => $password,
    );

    $response = $this->client->request("POST", $request_url, ['json' => $data]);

    if ($response->getStatusCode() == 200){
      $body = json_decode($response->getBody()->getContents());
      $this->csrf_token = $body->token;
    }
    else {
      throw new \Exception("Unable to login");
    }
  }

  /**
   * @Given I use the :arg1 endpoint to create the nodes:
   */
  public function iUseTheEndpointToCreateTheNodes($endpoint, $data) {
    $request_url = $this->base_url . $this->getEndpointPath($endpoint) . '/node';
    // Create nodes.
    foreach ($data->getHash() as $node_data) {
      $node = $this->getNodeFromData($node_data);

      $response = $this->client->request("POST", $request_url,
        [
          'headers' => ['X-CSRF-Token' => $this->csrf_token],
          'json' => $node
        ]
      );

      if ($response->getStatusCode() == '200') {
        $body = json_decode($response->getBody()->getContents());
        // Keep track of all created node.
        $node = node_load($body->nid);
        $wrapper = entity_metadata_wrapper('node', $node);
        $this->dkanContext->entityStore->store('node', $node->type, $node->nid, $wrapper, $wrapper->label());
      }
      else {
        throw new \Exception("Node could not be created");
      }
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

      $file = curl_file_create($file_path);

      // Prepare file data.
      $file_data = array(
        [
          'name' => 'files[1]',
          'contents' => fopen($file->name, 'r')
        ],
        [
          'name' => "field_name",
          'contents' => "field_upload"
        ],
        [
          'name' => "attach",
          'contents' => 0
        ]
      );

      $request_url = $this->base_url . $this->getEndpointPath($endpoint) . '/node/' . $node->getIdentifier() . '/attach_file';

      $response = $this->client->request("POST", $request_url, [
        'headers' => [
          'X-CSRF-Token' => $this->csrf_token,
        ],
        'multipart' => $file_data,
      ]);

      if ($response->getStatusCode() == '200') {
        return TRUE;
      }
      else {
        throw new \Exception(sprintf('Error: %s', $response['response']));
      }
    }
    else {
      throw new Exception(sprintf('The resource could not be found.'));
    }
  }

  private function printBody(\GuzzleHttp\Psr7\Response $response) {
    print_r((array) json_decode($response->getBody()->getContents()));
  }

  /**
   * @Given I use the :arg1 endpoint to update the node :arg2 with:
   */
  public function iUseTheEndpointToUpdateTheNodeWith($endpoint, $node_name, $data) {
    // Get node.
    $node = $this->dkanContext->entityStore->retrieve_by_name($node_name);
    if ($node) {
      foreach ($data->getHash() as $node_data) {

        $request_url = $this->base_url . $this->getEndpointPath($endpoint) . '/node/' . $node->getIdentifier();

        $node = $this->getNodeFromData($node_data);
        $response = $this->client->request("PUT", $request_url, [
          'headers' => [
            'X-CSRF-Token' => $this->csrf_token,
          ],
          'json' => $node,
        ]);

        if ($response->getStatusCode() == '200') {
          return TRUE;
        }
        else {
          throw new \Exception(sprintf('Error: %s', $response['response']));
        }
        break;
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
      $request_url = $this->base_url . $this->getEndpointPath($endpoint) . '/node/' . $node->getIdentifier();

      $response = $this->client->request("DELETE", $request_url, [
        'headers' => [
          'X-CSRF-Token' => $this->csrf_token,
        ]
      ]);

      if ($response->getStatusCode() == '200') {
        return TRUE;
      }
      else {
        throw new \Exception(sprintf('Error: %s', $response['response']));
      }
    }
    else {
      throw new Exception(sprintf('The node could not be found.'));
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
      case 'body':
      case 'description':
        if (is_array($field_value)) {
          $field_value = $field_value['value'];
        }
        break;
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

  private function getNodeFromData($data)
  {
    $properties = ['title', 'status', 'type'];
    $node = [];

    foreach ($data as $key => $value) {
      if (!in_array($key, $properties)) {
        $node[$key] = [
          'und' => [
            0 => ['value' => $value]
          ]
        ];
      } else {
        $node[$key] = $value;
      }
    }

    return $node;
  }

}
