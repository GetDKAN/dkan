<?php

namespace Drupal\datastore\Plugin\DkanApiDocs;

use Drupal\common\Plugin\DkanApiDocsBase;

/**
 * Docs plugin.
 *
 * @DkanApiDocs(
 *  id = "datastore_api_docs",
 *  description = "Datastore docs"
 * )
 */
class DatastoreApiDocs extends DkanApiDocsBase {

  public function spec() {
    return json_decode(file_get_contents($this->docsPath('datastore')), TRUE);
  }
}
