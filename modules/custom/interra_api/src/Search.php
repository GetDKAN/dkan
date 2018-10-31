<?php

namespace Drupal\interra_api;

use Drupal\node\Entity\Node;

class Search extends Load {

  public function formatDocs($docs) {
    $index = array();
    foreach($docs as $id => $doc) {
      $index[] = $this->formatSearchDoc($doc);
    }
    return $index;
  }
  public function formatSearchDoc($node) {
    $formatted = new \stdClass();
    $value = $node->get('field_json_metadata')->getValue()[0]['value'];
    $doc = $this->dereference(json_decode($value));
    $formatted->doc = $doc;
    $formatted->ref = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'. $node->id());
    return $formatted;
  }

  public function index() {
    $docs = $this->loadDocs();
    return $this->formatDocs($docs);
  }

}
