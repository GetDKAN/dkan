<?php

namespace Drupal\dkan\Plugin\DkanApiDocs;

use Drupal\dkan\Plugin\DkanApiDocsBase;

/**
 * API Docs common base.
 *
 * @DkanApiDocs(
 *  id = "dkan_common_api_docs",
 *  description = "Base API docs plugin."
 * )
 */
class CommonApiDocs extends DkanApiDocsBase {

  /**
   * {@inheritdoc}
   */
  public function spec() {
    return $this->getDoc('dkan');
  }

}
