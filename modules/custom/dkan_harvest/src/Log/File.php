<?php

namespace Drupal\dkan_harvest\Log;

/**
 *
 */
class File extends Log {

  /**
   *
   */
  public function write($level, $action, $message) {
    $logEntry = $this->logEntry($action, $level, $message);
    $entry = $this->flatten($logEntry);
    $this->appendToFile($entry);
  }

  /**
   *
   * @todo this could be replaced by json_encode, more or less.
   * @param mixed $entry
   * @return string
   */
  public function flatten($entry) {

    $log = '';
    foreach ($entry as $key => $item) {
      $log .= $key . ": " . $item . ", ";
    }
    $log = rtrim($log, ", ");
    $log = $log . "\n";
    return $log;
  }

  /**
   *
   */
  protected function appendToFile($entry) {
    try {
      // TODO: get a better folder, add to log or File declaration.
      $harvestFolder = $this->sourceId;
      if (!file_exists($harvestFolder)) {
        @mkdir($harvestFolder, 0777, TRUE);
      }
      $file = $harvestFolder . '/log.txt';
      $handle = @fopen($file, 'a');
      if (!$handle) {
        throw new Exception('Log file open failed.');
      }
      fwrite($handle, $entry);
      fclose($handle);
    }
    catch (Exception $e) {
      // Who logs the logs?
    }
  }

}
