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
    // @todo Diagnose why validating against open-api-schema-3.0.json results,
    //   at least in some scenarios, with error such as:
    //
    //   JSON Schema validation failed
    //   keyword: format
    //   pointer: paths//provider-data/api/1/metastore/schemas/dataset/items/post/requestBody/content/application/json/schema/properties/distribution/items/properties/downloadURL/anyOf/1/pattern
    //   message: The attribute should match 'regex' format.
//    $schema = file_get_contents(__DIR__ . "/../../docs/open-api-schema.3.0.json");
//    parent::__construct($json, $schema);

    // Until the above is addressed, keep validating against the null schema,
    // as PDC as done for quite some time.
    parent::__construct($json, '{}');
  }

}
