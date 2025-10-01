<?php

namespace Drupal\dkan_common\Plugin\DkanApiDocs;

use Drupal\dkan_common\Plugin\DkanApiDocsBase;

/**
 * API Docs common base.
 *
 * @DkanApiDocs(
 *  id = "common_dkan_api_docs",
 *  description = "Base API docs plugin."
 * )
 */
class CommonApiDocs extends DkanApiDocsBase {

  /**
   * {@inheritdoc}
   */
  public function spec() {
    return $this->getDoc('common');
  }

}
