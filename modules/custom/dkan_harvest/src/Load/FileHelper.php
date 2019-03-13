<?php

namespace Drupal\dkan_harvest\Load;

class FileHelper implements IFileHelper {

  public function prepareDir(&$directory) {
    file_prepare_directory($directory, FILE_CREATE_DIRECTORY);
  }

  public function retrieveFile($url, $destination = NULL, $managed = FALSE) {
    system_retrieve_file($url, $destination, $managed, FILE_EXISTS_REPLACE);
  }

  public function fileCreate($uri) {
    file_create_url($uri);
  }

  public function defaultSchemeDirectory() {
    return \Drupal::service('file_system')->realpath(file_default_scheme());
  }

}
