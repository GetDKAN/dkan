<?php

namespace Drupal\Tests\dkan_harvest\Unit;

use Drupal\dkan_harvest\Load\FileHelper;

class TestFileHelper extends FileHelper {

  public function prepareDir(&$directory) {
    return TRUE;
  }

  public function retrieveFile($url, $destination = NULL, $managed = FALSE) {
    return TRUE;
  }

  public function fileCreate($uri) {
    return $this->defaultSchemeDirectory() . 'distribution/' . basename($uri);
  }

  public function defaultSchemeDirectory() {
    return 'public://';
  }

}
