<?php

namespace Drupal\dkan_harvest;

use GuzzleHttp\Client;

class DataJson extends Extract {

  function run() {
    $this->log->write('DEBUG', 'extract', 'Running DataJson extraction.');
  }

  function readFolder() {
    $harvestFolder = $this->folder . '/' . $this->sourceId;
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
