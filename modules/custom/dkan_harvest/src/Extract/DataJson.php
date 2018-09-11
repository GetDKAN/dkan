<?php

namespace Drupal\dkan_harvest;

use GuzzleHttp\Client;

class DataJson extends Extract {

  function run() {
    var_dump('loading datajson');
  }

  function cache() {
		$data = $this->httpRequest($this->uri);
		$res = json_decode($data);
		if ($res->dataset) {
			foreach ($res->dataset as $dataset) {
        $this->writeToFile($dataset->identifier, json_encode($dataset));
			}
		}
    var_dump('loading cache datajon');
  }

}
