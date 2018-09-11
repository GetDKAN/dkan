<?php

namespace Drupal\dkan_harvest;

use GuzzleHttp\Client;

abstract class Extract {

  protected $folder;

  protected $uri;

  protected $harvestId;

  function __construct($config, $harvest) {
    $this->uri = $harvest->source->uri;
    $this->folder = $config->fileLocation . '/dkan_harvest/';
    $this->harvestId = $harvest->id;
  }

	protected function httpRequest($uri) {
		try {
		  $client = new Client();
			$res = $client
				->get($this->uri);
			$data = (string) $res->getBody();
			return $data;
		} catch (RequestException $exception) {
			var_dump('ooops');
			var_dump($exception);
		}
	}

	protected function writeToFile($id, $item) {
		try {
      $harvestFolder = $this->folder . '/' . $this->harvestId;
      if (!file_exists($harvestFolder)) {
        mkdir($harvestFolder, 0777, true);
      }
			$file =  $harvestFolder . '/' . $id . '.json';
			$handle = fopen($file, 'w');
			if ( !$handle ) {
        throw new Exception('File open failed.');
      }
			fwrite($handle, $item);
			fclose($handle);
		} catch ( Exception $e ) {
      // Let's log.
    }
	}

  function run() {
    var_dump('loading Extract');
    return array();
  }

  function cache() {
    var_dump('loading cache');
  }

}
