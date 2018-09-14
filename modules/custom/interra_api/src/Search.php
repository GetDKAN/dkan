<?php

namespace Drupal\interra_api;

use Drupal\node\Entity\Node;

class Search extends Load {

  public function formatDoc($doc) {
    $formatted = new \stdClass();
    $value = $doc->get('field_json_metadata')->getValue()[0]['value'];
    $formatted->doc = json_decode($value);
    $formatted->ref = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'. $doc->id());
    return $formatted;
  }

  public function index() {
    $docs = $this->loadDocs();
    return $this->formatDocs($docs);
  }

}
