<?php

namespace Drupal\interra_api;

use Drupal\node\Entity\Node;

class Load {

  private $primaryCollection = 'dataset';

  public function loadDocs($type = 'dataset') {
    $nids = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', $type)
      ->execute();
    return Node::loadMultiple($nids);
  }

  public function loadDocById($id) {
    $nid = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('uuid', $id)
      ->execute();
    if ($nid) {
      return Node::load(reset($nid));
    }
    return NULL;
  }

  public function formatDocs($docs) {
    $index = array();
    foreach($docs as $id => $doc) {
      $index[] = $this->formatDoc($doc);
    }
    return $index;
  }

  public function formatDoc($doc) {
    $value = $doc->get('field_json_metadata')->getValue()[0]['value'];
    return json_decode($value);
  }

  public function loadByType($type) {
    $docs = $this->loadDocs($type);
    return $this->formatDocs($docs);
  }

  public function loadAll() {
    $docs = $this->loadDocs();
    return $this->formatDocs($docs);
  }

}
