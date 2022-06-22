<?php

namespace Drupal\datastore\Service;

use RootedData\RootedJsonData;

/**
 * Datastore query data object.
 */
class DatastoreQuery extends RootedJsonData {

  /**
   * Constructor.
   *
   * @param string $json
   *   JSON query string from API payload.
   * @param int|null $rows_limit
   *   Maxmimum rows of data to return.
   */
  public function __construct(string $json, $rows_limit = NULL) {
    $schema = file_get_contents(__DIR__ . "/../../docs/query.json");
    $q = json_decode($schema);
    if ($rows_limit !== NULL) {
      $q->properties->limit->maximum = $rows_limit;
      $q->properties->limit->default = $rows_limit;
    }
    $schema = json_encode($q);
    parent::__construct($json, $schema);
    $this->populateDefaults();
  }

  /**
   * For any root-level properties in the query, set defaults explicitly.
   */
  private function populateDefaults() {
    $schemaJson = new RootedJsonData($this->getSchema());
    $properties = $schemaJson->{"$.properties"};
    foreach ($properties as $key => $property) {
      if (isset($property['default']) && !isset($this->{"$.$key"})) {
        $this->{"$.$key"} = $property['default'];
      }
    }
  }

}
