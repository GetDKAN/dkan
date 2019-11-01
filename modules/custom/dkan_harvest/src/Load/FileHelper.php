<?php

namespace Drupal\dkan_harvest\Load;

use Drupal\Core\File\FileSystemInterface;

/**
 * Class.
 *
 * @codeCoverageIgnore
 */
class FileHelper implements IFileHelper {

  /**
   * Public.
   */
  public function getRealPath($path) {
    return \Drupal::service('file_system')
      ->realpath($path);
  }

  /**
   * Public.
   */
  public function prepareDir(&$directory, $options = FileSystemInterface::CREATE_DIRECTORY) {
    return \Drupal::service('file_system')->prepareDirectory($directory, $options);
  }

  /**
   * Public.
   */
  public function retrieveFile($url, $destination = NULL, $managed = FALSE) {
    if (substr_count($url, "file://") > 0) {
      $content = file_get_contents($url);
      $pieces = parse_url($url);
      $path = explode("/", $pieces['path']);
      $filename = end($path);
      return file_save_data($content, $destination . "/{$filename}", $managed, FileSystemInterface::EXISTS_REPLACE);
    }
    else {
      return system_retrieve_file($url, $destination, $managed, FileSystemInterface::EXISTS_REPLACE);
    }
  }

  /**
   * Public.
   */
  public function fileCreate($uri) {
    return file_create_url($uri);
  }

  /**
   * Public.
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
   * Public.
   */
  public function fileGetContents($path) {
    return (is_readable($path) && is_file($path))
    ? file_get_contents($path)
    : NULL;
  }

  /**
   * Public.
   */
  public function filePutContents($path, $content) {
    return file_put_contents($path, $content);
  }

  /**
   * Public.
   */
  public function fileDelete($uri) {
    if (file_exists($uri)) {
      unlink($uri);
    }
  }

  /**
   * Public.
   */
  public function fileGlob($pattern, $flags = 0) {
    return glob($pattern, $flags);
  }

}
