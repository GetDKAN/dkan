<?php

namespace Drupal\dkan_harvest\Load;

/**
 * @codeCoverageIgnore
 */
class FileHelper implements IFileHelper {

  /**
   *
   */
  public function getRealPath($path) {
    return \Drupal::service('file_system')
      ->realpath($path);
  }

  /**
   *
   */
  public function prepareDir(&$directory, $options = FILE_CREATE_DIRECTORY) {
    file_prepare_directory($directory, $options);
  }

  /**
   *
   */
  public function retrieveFile($url, $destination = NULL, $managed = FALSE) {
    return system_retrieve_file($url, $destination, $managed, FILE_EXISTS_REPLACE);
  }

  /**
   *
   */
  public function fileCreate($uri) {
    return file_create_url($uri);
  }

  /**
   *
   */
  public function defaultSchemeDirectory() {
    // @todo this might not always work.
    //   Considering s3fs or others that don't live on disk
    return $this->getRealPath(
                    \Drupal::config('system.file')
                      ->get('default_scheme') . "://"
    );
  }

  /**
   *
   */
  public function fileGetContents($path) {
    return (is_readable($path) && is_file($path))
    ? file_get_contents($path)
    : NULL;
  }

  /**
   *
   */
  public function filePutContents($path, $content) {
    return file_put_contents($path, $content);
  }

  /**
   *
   */
  public function fileDelete($uri) {
    if (file_exists($uri)) {
      unlink($uri);
    }
  }

  /**
   *
   */
  public function fileGlob($pattern, $flags = 0) {
    return glob($pattern, $flags);
  }

}
