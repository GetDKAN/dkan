<?php

namespace Drupal\dkan_harvest\Load;

/**
 * Interface.
 */
interface IFileHelper {

  /**
   * Public.
   */
  public function getRealPath($path);

  /**
   * File get contents.
   *
   * @return string|null
   *   return null if file doesn't exist or not readable
   */
  public function fileGetContents($path);

  /**
   * Public.
   */
  public function filePutContents($path, $content);

  /**
   * Public.
   */
  public function prepareDir(&$directory, $options = FILE_CREATE_DIRECTORY);

  /**
   * Public.
   */
  public function retrieveFile($url, $destination = NULL, $managed = FALSE);

  /**
   * Public.
   */
  public function fileCreate($uri);

  /**
   * Public.
   */
  public function fileDelete($uri);

  /**
   * Public.
   */
  public function fileGlob($pattern, $flags = 0);

  /**
   * Public.
   */
  public function defaultSchemeDirectory();

}
