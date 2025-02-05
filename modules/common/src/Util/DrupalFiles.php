<?php

namespace Drupal\common\Util;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\Exception\InvalidStreamWrapperException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Http\Client\ClientExceptionInterface;
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
   * HTTP client factory service.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  private ClientFactory $httpClientFactory;

  /**
   * Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private MessengerInterface $messenger;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system'),
      $container->get('stream_wrapper_manager'),
      $container->get('http_client_factory'),
      $container->get('messenger')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(
    FileSystemInterface $filesystem,
    StreamWrapperManager $streamWrapperManager,
    ClientFactory $httpClientFactory,
    MessengerInterface $messenger,
  ) {
    $this->filesystem = $filesystem;
    $this->streamWrapperManager = $streamWrapperManager;
    $this->httpClientFactory = $httpClientFactory;
    $this->messenger = $messenger;
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
   * @deprecated
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
    return $this->systemRetrieveFile($url, $destination);
  }

  /**
   * Attempts to get a file using Guzzle HTTP client and to store it locally.
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
   * @param bool $managed
   *   If this is set to TRUE, the file API hooks will be invoked and the file
   *   is registered in the database.
   * @param int $replace
   *   Replace behavior when the destination file already exists:
   *   - FileSystemInterface::EXISTS_REPLACE: Replace the existing file.
   *   - FileSystemInterface::EXISTS_RENAME: Append _{incrementing number} until
   *   the filename is unique.
   *   - FileSystemInterface::EXISTS_ERROR: Do nothing and return FALSE.
   *
   * @return mixed
   *   One of these possibilities:
   *   - If it succeeds and $managed is FALSE, the location where the file was
   *   saved.
   *   - If it succeeds and $managed is TRUE, a \Drupal\file\FileInterface
   *   object which describes the file.
   *   - If it fails, FALSE.
   *
   * @see \system_retrieve_file()
   * @see https://www.drupal.org/node/3223362
   */
  protected function systemRetrieveFile($url, $destination = NULL, $managed = FALSE, $replace = FileSystemInterface::EXISTS_RENAME) {
    $parsed_url = parse_url($url);
    if (!isset($destination)) {
      $path = $this->filesystem->basename($parsed_url['path']);
      $path = \Drupal::config('system.file')->get('default_scheme') . '://' . $path;
      $path = $this->streamWrapperManager->normalizeUri($path);
    }
    else {
      if (is_dir($this->filesystem->realpath($destination))) {
        // Prevent URIs with triple slashes when glueing parts together.
        $path = str_replace('///', '//', "$destination/") . \Drupal::service('file_system')->basename($parsed_url['path']);
      }
      else {
        $path = $destination;
      }
    }
    try {
      $data = (string) $this->httpClientFactory->fromOptions()
        ->get($url)
        ->getBody();
      if ($managed) {
        /** @var \Drupal\file\FileRepositoryInterface $file_repository */
        $file_repository = \Drupal::service('file.repository');
        $local = $file_repository->writeData($data, $path, $replace);
      }
      else {
        $local = $this->filesystem->saveData($data, $path, $replace);
      }
    }
    catch (ClientExceptionInterface $exception) {
      $this->messenger->addError($this->t('Failed to fetch file due to error "%error"', [
        '%error' => $exception->getMessage(),
      ]));
      return FALSE;
    }
    catch (FileException | InvalidStreamWrapperException $e) {
      $this->messenger->addError($this->t('Failed to save file due to error "%error"', [
        '%error' => $e->getMessage(),
      ]));
      return FALSE;
    }
    if (!$local) {
      $this->messenger->addError($this->t('@remote could not be saved to @path.', [
        '@remote' => $url,
        '@path' => $path,
      ]));
    }

    return $local;
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
