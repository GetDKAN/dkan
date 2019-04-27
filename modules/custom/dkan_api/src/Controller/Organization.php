<?php

namespace Drupal\dkan_api\Controller;

/**
 *
 */
class Organization extends Api {

  /**
   *
   */
  protected function getJsonSchema() {
    "
    {
      \"title\": \"Organization\",
      \"description\": \"Organization.\",
      \"type\": \"object\",
      \"required\": [
        \"name\"
      ],
      \"properties\": {
        \"name\": {
          \"type\": \"string\",
          \"title\": \"Name\"
        }
      }
    }
    ";
  }

  /**
   * @return Drupal\dkan_api\Storage\Organization
   */
  protected function getStorage() {
    return $this->container
      ->get('dkan_api.storage.organization');
  }

}
