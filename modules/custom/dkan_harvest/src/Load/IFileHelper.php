<?php

namespace Drupal\dkan_harvest\Load;

/**
 *
 */
interface IFileHelper {

  /**
   *
   */
  public function getRealPath($path);

  /**
   * @return string|null return null if file doesn't exist or not readable
   */
  public function fileGetContents($path);

  /**
   *
   */
  public function filePutContents($path, $content);

  /**
   *
   */
  public function prepareDir(&$directory, $options = FILE_CREATE_DIRECTORY);

  /**
   *
   */
  public function retrieveFile($url, $destination = NULL, $managed = FALSE);

  /**
   *
   */
  public function fileCreate($uri);

  /**
   *
   */
  public function fileDelete($uri);

  /**
   *
   */
  public function fileGlob($pattern, $flags = 0);

  /**
   *
   */
  public function defaultSchemeDirectory();

}
