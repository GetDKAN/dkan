<?php

namespace Drupal\dkan_harvest\Extract;

use Drupal\dkan_harvest\Log\MakeItLog;
use GuzzleHttp\Client;
use Drupal\dkan_harvest\Load\FileHelperTrait;
use GuzzleHttp\Exception\RequestException;

/**
 *
 */
abstract class Extract {

  use MakeItLog;
  use FileHelperTrait;

  protected $folder;

  protected $uri;

  protected $sourceId;

  /**
   *
   */
  public function __construct($harvest_info) {
    $this->uri = $harvest_info->source->uri;
    $fileHelper = $this->getFileHelper();
    $this->folder = $fileHelper->defaultSchemeDirectory() . '/dkan_harvest/';
    $this->sourceId = $harvest_info->sourceId;
  }

  /**
   *
   */
  protected function httpRequest($uri) {
    try {
      $client = $this->getHttpClient();
      $res = $client
        ->get($uri);
      $data = (string) $res->getBody();
      return $data;
    }
    catch (RequestException $exception) {
      $this->log('ERROR', 'Extract', 'Error reading ' . $uri);
    }
  }

  /**
   * @codeCoverageIgnore
   * @return \GuzzleHttp\Client
   */
  protected function getHttpClient() {
    return new Client();
  }

  /**
   *
   */
  protected function writeToFile($id, $item) {
    try {
      $harvestFolder = $this->folder . '/' . $this->sourceId;
      if (!file_exists($harvestFolder)) {
        @mkdir($harvestFolder, 0777, TRUE);
      }
      $file = $harvestFolder . '/' . $id . '.json';
      $handle = @fopen($file, 'w');
      if (!$handle) {
        throw new \Exception('File open failed.');
      }
      fwrite($handle, $item);
      fclose($handle);
    }
    catch (\Exception $e) {
      // Let's log.
    }
  }

  /**
   *
   */
  abstract public function run();

  /**
   *
   */
  abstract public function cache();

}
