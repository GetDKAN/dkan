<?php

namespace Drupal\common;

/**
 * Convert between local file paths and public file URLs.
 *
 * @todo Convert to service with Dependency Injection.
 */
class UrlHostTokenResolver {
  const TOKEN = "h-o.st";
  const PUBLIC_SCHEME = 'public://';

  /**
   * Get the HTTP server public files URL.
   *
   * @return string|null
   *   The HTTP server public files URL, or NULL in the case of failure.
   */
  public static function getServerPublicFilesUrl(): ?string {
    // Get public file stream.
    $public_stream = \Drupal::service('stream_wrapper_manager')
      ->getViaUri(self::PUBLIC_SCHEME);
    // Retrieve the URL path for the public stream.
    return $public_stream ? $public_stream->getExternalUrl() : NULL;
  }

  /**
   * Resolve hostified resource URL to actual domain URL.
   *
   * @param string $resourceUrl
   *   Hostified resource URL.
   *
   * @return string
   *   Resolved resource URL (with actual domain).
   */
  public static function resolve(string $resourceUrl): string {
    // Get HTTP server public files URL and extract the host.
    $serverPublicFilesUrl = self::getServerPublicFilesUrl();
    $serverPublicFilesUrl = isset($serverPublicFilesUrl) ? parse_url($serverPublicFilesUrl) : NULL;
    $serverHost = $serverPublicFilesUrl['host'] ?? \Drupal::request()->getHost();
    // Determine whether the localhost token is present in the resource URL, and
    // replace the token if necessary.
    if (substr_count($resourceUrl, self::TOKEN) > 0) {
      $resourceUrl = str_replace(self::TOKEN, $serverHost, $resourceUrl);
    }
    return $resourceUrl;
  }

  /**
   * Resolve host token string to public file path.
   *
   * @param string $resourceUrl
   *   Full temporary token URL.
   *
   * @return string
   *   Resolved public file path.
   */
  public static function resolveFilePath(string $resourceUrl): string {
    return urldecode(preg_replace('/^' . preg_quote(self::getServerPublicFilesUrl(), '/') . '/',
      self::PUBLIC_SCHEME, self::resolve($resourceUrl)));
  }

}
