<?php

namespace Drupal\datastore\Service;

use RootedData\RootedJsonData;

/**
 * DatastoreQuery.
 */
class DatastoreQuery extends RootedJsonData {

  /**
   * Constructor.
   *
   * @param string $json
   *   JSON query string from API payload.
   */
  public function __construct(string $json) {
    $schema = file_get_contents(__DIR__ . "/../../docs/query.json");
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
