<?php

namespace Drupal\dkan_harvest\Load;

interface IFileHelper {

  public function prepareDir(&$directory);

  public function retrieveFile($url, $destination = NULL, $managed = FALSE);

  public function fileCreate($uri);

  public function defaultSchemeDirectory();

}
