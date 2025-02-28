<?php

namespace Drupal\common\Util;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\Exception\InvalidStreamWrapperException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use GuzzleHttp\Exception\TransferException;
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

  use StringTranslationTrait;

  /**
   * Drupal file system service.
   */
  private FileSystemInterface $filesystem;

  /**
   * Drupal stream wrapper manager.
   */
  private StreamWrapperManagerInterface $streamWrapperManager;

  /**
   * HTTP client factory service.
   */
  private ClientFactory $httpClientFactory;

  /**
   * Logger service.
   */
  private LoggerChannelInterface $logger;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system'),
      $container->get('stream_wrapper_manager'),
      $container->get('http_client_factory'),
      $container->get('dkan.common.logger_channel')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(
    FileSystemInterface $filesystem,
    StreamWrapperManager $streamWrapperManager,
    ClientFactory $httpClientFactory,
    LoggerChannelInterface $logger,
  ) {
    $this->filesystem = $filesystem;
    $this->streamWrapperManager = $streamWrapperManager;
    $this->httpClientFactory = $httpClientFactory;
    $this->logger = $logger;
  }

  /**
   * Get the Drupal file_system service.
   *
   * @returns FileSystemInterface
   *   The file_system service.
   */
  public function getFilesystem(): FileSystemInterface {
    return $this->filesystem;
  }

  /**
   * Getter.
   *
   * @deprecated in dkan:2.20.1 and is removed from dkan:2.21.0.
   *   Unsed, cleaning up.
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

    // Handle file:// URIs.
    if (substr_count($url, "file://") > 0) {

      $src = str_replace("file://", "", $url);
      $filename = $this->getFilenameFromUrl($url);
      $dest = $this->getFilesystem()->realpath($destination) . "/{$filename}";
      copy($src, $dest);

      return $this->fileCreateUrl("{$destination}/{$filename}");
    }
    // Handle http(s):// URIs.
    return $this->retrieveRemoteFile($url, $destination);
  }

  /**
   * Attempts to get a file using Guzzle HTTP client and to store it locally.
   *
   * @param string $url
   *   The URL of the file to grab.
   * @param string|null $destination
   *   Stream wrapper URI specifying where the file should be placed. Can be a
   *   directory or full path with file name if you want to rename. If NULL, the
   *   file will be placed in "public://" with the same name as the remote file.
   *
   * @return false|string
   *   If it succeeds , the new location URI. If it fails, FALSE.
   *
   * @see \system_retrieve_file()
   * @see https://www.drupal.org/node/3223362
   */
  protected function retrieveRemoteFile(string $url, ?string $destination = NULL) {
    $this->fixDestination($destination, $url);
    try {
      $client = $this->httpClientFactory->fromOptions();
      $data = (string) $client->get($url)->getBody();
      // @todo Switch FileSystemInterface::EXISTS_REPLACE for
      // FileExists::Replace once we drop D10.2 support.
      return $this->filesystem->saveData($data, $destination, FileSystemInterface::EXISTS_REPLACE);
    }
    catch (TransferException $exception) {
      $this->logger->error($this->t('Failed to fetch file due to error "%error"', ['%error' => $exception->getMessage()]));
      return FALSE;
    }
    catch (FileException | InvalidStreamWrapperException $e) {
      $this->logger->error($this->t('Failed to save file due to error "%error"', ['%error' => $e->getMessage()]));
      return FALSE;
    }
  }

  /**
   * Fix missing or extra-escaped destination string.
   *
   * @param string|null $destination
   *   The destination string.
   * @param string $url
   *   The source URL.
   */
  private function fixDestination(?string &$destination, $url): void {
    $parsed_url = parse_url($url);
    if (!isset($destination)) {
      $destination = $this->filesystem->basename($parsed_url['path']);
      $destination = 'public://' . $destination;
      $destination = $this->streamWrapperManager->normalizeUri($destination);
    }
    elseif (is_dir($this->filesystem->realpath($destination))) {
      // Prevent URIs with triple slashes when glueing parts together.
      $destination = str_replace('///', '//', "$destination/") . $this->filesystem->basename($parsed_url['path']);
    }
  }

  /**
   * Given a URI like public://, retrieve the http URL.
   *
   * @returns string
   *   The URL.
   */
  public function fileCreateUrl($uri) : string {
    if (substr_count($uri, 'http') > 0) {
      return $uri;
    }
    elseif ($wrapper = $this->streamWrapperManager->getViaUri($uri)) {
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
