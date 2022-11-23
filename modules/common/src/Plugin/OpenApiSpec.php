<?php

namespace Drupal\common\Plugin;

use RootedData\RootedJsonData;

/**
 * DatastoreQuery.
 */
class OpenApiSpec extends RootedJsonData {

  /**
   * Constructor.
   *
   * @param string $json
   *   JSON query string from API payload.
   */
  public function __construct(string $json) {
    $schema = file_get_contents(__DIR__ . "/../../docs/open-api-schema.3.0.json");
    parent::__construct($json, $schema);
  }

}
