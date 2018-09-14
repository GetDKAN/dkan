<?php

namespace Drupal\interra_api;

use Drupal\node\Entity\Node;
use Drupal\dkan_schema\Schema;
use Drupal\interra_api\Load;

/**
 * For handling API requests.
 */
class ApiRequest {
  protected $apiPath = '/api/v1/';

  /**
   * Returns URI for the dataset from the relateive path.
   *
   * @param string $relativePath
   *   Path from the site, ie `/api/v1/collections/dataset`.
   *
   * @return string
   *   The URI for the doc, ie `collections/dataset`.
   */
  public function getUri($relativePath) {
    $path = '';
    if (substr($relativePath, 0, strlen($this->apiPath)) == $this->apiPath) {
      $path = substr($relativePath, strlen($this->apiPath));
    }
    return $path;
  }

  /**
   * Validates the path for the collection API.
   *
   * @param string $path
   *   The path that is provided by the user. Expects
   *   `/api/v1/collections/[collection].json`
   *
   * @return
   *   The collection that has been requested if it is valid or FALSE.
   */
  public function validateCollectionPath($path) {
    $schema = new Schema();
    $collections = $schema->getActiveCollections();
    $items = explode('/', $path);
    if ($items[0] == 'collections') {
      $col = explode('.', $items[1]);
      $collection = $col[0];
      if (in_array($collection, $collections) && $col[1] == 'json') {
        return $collection;
      }
    }
    return FALSE;
  }

  /**
   * Validates the path for the doc API.
   *
   * @param string $path
   *   The path that is provided by the user. Expects
   *   `/api/v1/collections/[collection]/[doc].json`
   *
   * @return
   *   The doc that has been requested if it is valid or FALSE.
   */
  public function validateDocPath($path) {
    $schema = new Schema();
    $collections = $schema->getActiveCollections();
    $items = explode('/', $path);
    if ($items[0] == 'collections') {
      $collection = $items[1];
      if (in_array($collection, $collections)) {
        $d = explode('.', $items[2]);
        $doc = $d[0];
        if (TRUE && $d[1] == 'json') {
          return $doc;
        }
      }
    }
    return FALSE;
  }
}

