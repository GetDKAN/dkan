<?php

namespace Drupal\dkan_harvest\Extract;

use Harvest\Extract\Extract;

class DataJson extends Extract {

  public function run() {
    $items = [];
    $this->log('DEBUG', 'extract', 'Running DataJson extraction.');

    $items = $this->storage->retrieveAll();
    if (empty($items)) {
      $this->cache();
    }

    $items = $this->storage->retrieveAll();


  }

  public function cache() {
    $this->log('DEBUG', 'extract', 'Caching DataJson files.');
		$data = $this->httpRequest($this->uri);
		$res = json_decode($data);
		if ($res->dataset) {
			foreach ($res->dataset as $dataset) {

        $this->writeToFile($id, json_encode($dataset));
			}
		}
  }

}
