<?php

namespace Drupal\datastore\Plugin\DkanApiDocs;

use Drupal\common\ApiDocs\DkanApiDocsBase;

/**
 * Provides a ham sandwich.
 *
 * @Sandwich(
 *   id = "ham_sandwich",
 *   description = @Translation("Ham, mustard, rocket, sun-dried tomatoes."),
 *   calories = 426
 * )
 */
class DatastoreQueryApi extends DkanApiDocsBase {

  public function spec() { 
    return (object) [
      "foo" => "bar",
    ];
  }

}
