<?php

namespace Drupal\dkan_harvest\Extract;

use Drupal\dkan_harvest\Extract;

use GuzzleHttp\Client;

class DataJson extends Extract {

  function run() {
    $items = [];
    $this->log->write('DEBUG', 'extract', 'Running DataJson extraction.');
    $harvestFolder = $this->folder . '/' . $this->sourceId;
    // TODO: validation!
    foreach(glob("$harvestFolder/*.json") as $file) {
      $items[] = json_decode(file_get_contents($file));
    }
    return $items;
  }

  function cache() {
    $this->log->write('DEBUG', 'extract', 'Caching DataJson files.');
		$data = $this->httpRequest($this->uri);
		$res = json_decode($data);
		if ($res->dataset) {
			foreach ($res->dataset as $dataset) {
        $this->writeToFile($dataset->identifier, json_encode($dataset));
			}
		}
  }

}
