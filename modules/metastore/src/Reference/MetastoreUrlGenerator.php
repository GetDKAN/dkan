<?php

namespace Drupal\metastore\Reference;

use Drupal\common\StreamWrapper\DkanStreamWrapper;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\MetastoreService;

/**
 * Convert between local file paths and public file URLs.
 *
 * @todo Convert to service with Dependency Injection.
 */
class MetastoreUrlGenerator {
  const DKAN_SCHEME = 'dkan';
  const API_PATH = '/api/1';

  /**
   * DKAN Stream Wrapper.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManager
   */
  protected StreamWrapperManager $streamWrapperManager;

  /**
   * Metastore service.
   *
   * @var \Drupal\metastore\MetastoreService;
   */
  protected MetastoreService $metastore;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new file URL generator object.
   *
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $stream_wrapper_manager
   *   The stream wrapper manager.
   * @param \Drupal\metastore\Service $metastore
   *   Metastore service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(
    StreamWrapperManagerInterface $stream_wrapper_manager,
    MetastoreService $metastore,
    RequestStack $request_stack
  ) {
    $this->streamWrapperManager = $stream_wrapper_manager;
    $this->metastore = $metastore;
    $this->requestStack = $request_stack;
  }

  /**
   * Retrieve the metastore service.
   *
   * @return \Drupal\metastore\MetastoreService
   *   Metastore service.
   */
  protected function metastore(): MetastoreService {
    return $this->metastore;
  }

  /**
   * Get the HTTP server public files URL.
   *
   * @return string|null
   *   The HTTP server public files URL, or NULL in the case of failure.
   */
  public function generateAbsoluteString($uri): string {
    if (StreamWrapperManager::getScheme($uri) != self::DKAN_SCHEME) {
      throw new \DomainException("Only dkan:// urls accepted.");
    }
    // Retrieve the URL path for the public stream.
    $parts = parse_url($uri);
    return $this->streamWrapperManager->getViaScheme(self::DKAN_SCHEME)->getExternalUrl() . "/$parts[host]$parts[path]";
  }

  /**
   * If possible, convert public URL to dkan:// uri.
   *
   * If already a dkan:// uri, returns $url untouched.
   *
   * @param string $url
   *   Public URL.
   *
   * @return string
   *   URI with the dkan:// scheme.
   *
   * @throws \DomainException
   */
  public function uriFromUrl(string $url): string {
    $allowed = ['dkan', 'http', 'https'];
    $parts = parse_url($url);
    if (!isset($parts['scheme']) || !in_array($parts['scheme'], $allowed)) {
      throw new \DomainException("Invalid URL $url");
    }

    if ($parts['scheme'] == 'dkan') {
      return $url;
    }

    $request = $this->requestStack->getCurrentRequest();
    $host = $request->getHost();
    if ($parts['host'] != $host) {
      throw new \DomainException("Current host $host does not match URL host {$parts['host']}");
    }

    // Length of API base path.
    $base_len = strlen(DkanStreamWrapper::DKAN_API_URL_BASE);

    if (substr($parts['path'], 0, $base_len) != DkanStreamWrapper::DKAN_API_URL_BASE) {
      throw new \DomainException("URL $url path does not match DKAN API path.");
    }

    $uri_path = substr($parts['path'], $base_len);

    return self::DKAN_SCHEME . "://{$uri_path}";
  }

  /**
   * Confirm metastore URI path is correct, ID exists.
   *
   * @param string $uri
   *   Metastore URI (dkan://metastore/schemas/{schema}/items/{id})
   * @param string|null $schema
   *   Optional schema ID to validate against as well.
   *
   * @return bool
   *   TRUE if valid, existing metastore URI.
   */
  public function validateUri(string $uri, ?string $schema = NULL): bool {
    $uri_scheme = StreamWrapperManager::getScheme($uri);
    if ($uri_scheme != self::DKAN_SCHEME) {
      return FALSE;
    }

    $path = substr($uri, strlen(self::DKAN_SCHEME) + 3);
    $parts = explode('/', $path);
    if ($parts[0] != 'metastore' || $parts[1] != 'schemas' || $parts[3] != 'items') {
      return FALSE;
    }

    if ($schema && ($parts[2] != $schema)) {
      return FALSE;
    }

    try {
      $this->metastore()->get($parts[2], $parts[4]);
      return TRUE;
    }
    catch (MissingObjectException $e) {
      return FALSE;
    }

  }

  /**
   * Extract an item ID from a DKAN URI.
   *
   * @param string $uri
   *   DKAN URI, e.g. dkan://metastore/schemas/dataset/items/444.
   * @param null|string $schema
   *   Restrict to specific metastore schema, optional.
   *
   * @return string
   *   The identifier string.
   */
  public function extractItemId(string $uri, ?string $schema = NULL): string {
    if ($this->validateUri($uri, $schema)) {
      $path = substr($uri, strlen(self::DKAN_SCHEME) + 3);
      $parts = explode('/', $path);
      return $parts[4];
    }
    throw new \DomainException("Invalid metastore URI: $uri");
  }

}
