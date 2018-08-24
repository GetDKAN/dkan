<?php

namespace Drupal\interra_api;

use Drupal\node\Entity\Node;

class Search {

  private $primaryCollection = 'dataset';

  private function loadDocs() {
		$type = 'dataset';
		$nids = \Drupal::entityQuery('node')
			->condition('status', 1)
			->condition('type', $type)
			->execute();
		return Node::loadMultiple($nids);
  }

  private function formatDocs($docs) {
    $index = array();
    foreach($docs as $id => $doc) {
      $index[] = $this->formatDoc($doc);
    }
    return $index;
  }

  private function formatDoc($doc) {
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
