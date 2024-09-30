<?php

namespace Drupal\common\Util;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provide custom DKAN file storage system functionality.
 *
 * It wraps a few file related Drupal functions, it provides
 * a mechanism to bring remote files locally, and to move local files to a
 * Drupal appropriate place for public access through a URL.
 *
 * @package Drupal\common\Util
 */
class DrupalFiles implements ContainerInjectionInterface {

  /**
   * Drupal file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $filesystem;

  /**
   * Drupal stream wrapper manager.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManager
   */
  private $streamWrapperManager;

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system'),
      $container->get('stream_wrapper_manager')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(FileSystemInterface $filesystem, StreamWrapperManager $streamWrapperManager) {
    $this->filesystem = $filesystem;
    $this->streamWrapperManager = $streamWrapperManager;
  }

  /**
   * Getter.
   */
  public function getFilesystem(): FileSystemInterface {
    return $this->filesystem;
  }

  /**
   * Getter.
   */
  public function getStreamWrapperManager(): StreamWrapperManager {
    return $this->streamWrapperManager;
  }

  /**
   * Retrieve File.
   *
   * Stores the file at the given destination and returns the Drupal url for
   * the newly stored file.
   */
  public function retrieveFile($url, $destination) {
    if (substr_count($url, "file://") == 0 &&
      substr_count($url, "http://") == 0 &&
      substr_count($url, "https://") == 0
    ) {
      throw new \Exception("Only file:// and http(s) urls are supported");
    }

    if (substr_count($destination, "public://") == 0) {
      throw new \Exception("Only moving files to Drupal's public directory (public://) is supported");
    }

    if (substr_count($url, "file://") > 0) {

      $src = str_replace("file://", "", $url);
      $filename = $this->getFilenameFromUrl($url);
      $dest = $this->getFilesystem()->realpath($destination) . "/{$filename}";
      copy($src, $dest);

      return $this->fileCreateUrl("{$destination}/{$filename}");
    }
    else {
      return $this->systemRetrieveFile($url, $destination);
    }
  }

  /**
   * Attempts to get a file using Guzzle HTTP client and to store it locally.
   *
   * The destination file will never be a managed file.
   *
   * @param string $url
   *   The URL of the file to grab.
   * @param string $destination
   *   Stream wrapper URI specifying where the file should be placed. If a
   *   directory path is provided, the file is saved into that directory under
   *   its original name. If the path contains a filename as well, that one will
   *   be used instead.
   *   If this value is omitted, the site's default files scheme will be used,
   *   usually "public://".
   *
   * @return mixed
   *   One of these possibilities:
   *   - If it succeeds the location where the file was saved.
   *   - If it fails, FALSE.
   *
   * @see \system_retrieve_file()
   */
  protected function systemRetrieveFile($url, $destination = NULL) {
    return system_retrieve_file($url, $destination, FALSE, FileSystemInterface::EXISTS_REPLACE);
  }

  /**
   * Given a URI like public:// retrieve the URL.
   */
  public function fileCreateUrl($uri) {
    if (substr_count($uri, 'http') > 0) {
      return $uri;
    }
    elseif ($wrapper = $this->getStreamWrapperManager()->getViaUri($uri)) {
      return $wrapper->getExternalUrl();
    }
    throw new \Exception("No stream wrapper available for {$uri}");
  }

  /**
   * Get the full filesystem path to public://.
   */
  public function getPublicFilesDirectory() {
    return $this->getFilesystem()->realpath("public://");
  }

  /**
   * Private.
   */
  private function getFilenameFromUrl($url) {
    $pieces = parse_url($url);
    $path = explode("/", $pieces['path']);
    return end($path);
  }

}
