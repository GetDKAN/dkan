<?php

namespace Drupal\interra_api;

use Drupal\node\Entity\Node;

class Load {

  private $primaryCollection = 'dataset';

  public function loadDocs() {
		$type = 'dataset';
		$nids = \Drupal::entityQuery('node')
			->condition('status', 1)
			->condition('type', $type)
			->execute();
		return Node::loadMultiple($nids);
  }

  public function loadDoc($nid) {
		return Node::load($nid);
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

  public function loadAll() {
    $docs = $this->loadDocs();
    return $this->formatDocs();
  }

}
