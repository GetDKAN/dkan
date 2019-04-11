<?php

namespace Drupal\interra_api;

use Drupal\dkan_api\Controller\Dataset;

class Search extends Load {

  public function formatDocs($docs) {
    $index = array();
    foreach($docs as $id => $doc) {
      $index[] = $this->formatSearchDoc($doc);
    }
    return $index;
  }
  public function formatSearchDoc($value) {
    $formatted = new \stdClass();
    $formatted->doc = $value;
    $formatted->ref = "";
    return $formatted;
  }

  public function index() {
    $dataset_api = new Dataset();
    $api_engine = $dataset_api->getEngine();
    $array_of_json_strings = $api_engine->get();
    $json_array = "[" . implode(",", $array_of_json_strings) . "]";
    return $this->formatDocs(json_decode($json_array));
  }

}
