<?php

namespace Drupal\dkan_harvest\Extract;

use Drupal\dkan_harvest\Log\MakeItLog;
use GuzzleHttp\Client;

abstract class Extract {

  use MakeItLog;

  protected $folder;

  protected $uri;

  protected $sourceId;


  function __construct($harvest_info) {
    $this->uri = $harvest_info->source->uri;
    $this->folder = \Drupal::service('file_system')->realpath(file_default_scheme() . "://") . '/dkan_harvest/';
    $this->sourceId = $harvest_info->sourceId;
  }


  protected function httpRequest($uri) {
    try {
      $client = new Client();
      $res = $client
        ->get($this->uri);
      $data = (string) $res->getBody();
      return $data;
    } catch (RequestException $exception) {
      $this->log('ERROR', 'Extract', 'Error reading ' . $uri);
    }
  }

  protected function writeToFile($id, $item) {
    try {
      $harvestFolder = $this->folder . '/' . $this->sourceId;
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

  abstract public function run();

  abstract public function cache();

}
