<?php

namespace Drupal\common;

/**
 * Convert between local file paths and public file URLs.
 *
 * @todo Stop using tokenized host names altogether.
 */
class UrlHostTokenResolver {

  const TOKEN = 'h-o.st';

  const PUBLIC_URI = 'public://';

  /**
   * Get the HTTP server public files URL.
   *
   * @return string|null
   *   The HTTP server public files URL, or NULL in the case of failure.
   */
  public static function getServerPublicFilesUrl(): ?string {
    // Get public file stream.
    /** @var \Drupal\Core\StreamWrapper\StreamWrapperInterface $wrapper */
    $wrapper = \Drupal::service('stream_wrapper_manager')
      ->getViaUri(self::PUBLIC_URI);
    // Retrieve the URL path for the public stream.
    return $wrapper ? $wrapper->getExternalUrl() : NULL;
  }

  /**
   * Resolve a 'hostified' resource HTTP URL to use the site domain.
   *
   * @param string $resourceUrl
   *   Hostified resource URL.
   *
   * @return string
   *   Resolved resource URL (with site domain).
   */
  public static function resolve(string $resourceUrl): string {
    // Get HTTP server public files URL and extract the host.
    $serverPublicFilesUrl = self::getServerPublicFilesUrl();
    $serverPublicFilesUrl = isset($serverPublicFilesUrl) ? parse_url($serverPublicFilesUrl) : NULL;
    $serverHost = $serverPublicFilesUrl['host'] ?? \Drupal::request()->getHost();
    // Determine whether the hostified token is present in the resource URL, and
    // replace the token if necessary.
    if (substr_count($resourceUrl, self::TOKEN) > 0) {
      $resourceUrl = str_replace(self::TOKEN, $serverHost, $resourceUrl);
    }
    return $resourceUrl;
  }

  /**
   * Resolve an HTTP URL into a public URI.
   *
   * @param string $resourceUrl
   *   Full temporary token URL.
   *
   * @return string
   *   Resolved public file path.
   */
  public static function resolveFilePath(string $resourceUrl): string {
    return urldecode((string) preg_replace(
      '/^' . preg_quote((string) self::getServerPublicFilesUrl(), '/') . '/',
      self::PUBLIC_URI, self::resolve($resourceUrl)
    ));
  }

  /**
   * Substitute the host for local URLs with a custom localhost token.
   *
   * @param string $resourceUrl
   *   The URL of the resource being substituted.
   *
   * @return string
   *   The resource URL with the custom localhost token.
   */
  public static function hostify(string $resourceUrl): string {
    // Get HTTP server public files URL and extract the host.
    $serverPublicFilesUrl = self::getServerPublicFilesUrl();
    $serverPublicFilesUrl = isset($serverPublicFilesUrl) ? parse_url($serverPublicFilesUrl) : NULL;
    $serverHost = $serverPublicFilesUrl['host'] ?? \Drupal::request()->getHost();
    // Determine whether the resource URL has the same host as this server.
    $resourceParsedUrl = parse_url($resourceUrl);
    if (isset($resourceParsedUrl['host']) && $resourceParsedUrl['host'] == $serverHost) {
      // Swap out the host portion of the resource URL with the localhost token.
      $resourceParsedUrl['host'] = UrlHostTokenResolver::TOKEN;
      $resourceUrl = self::unparseUrl($resourceParsedUrl);
    }
    return $resourceUrl;
  }

  /**
   * Private.
   */
  private static function unparseUrl($parsedUrl) {
    $url = '';
    $urlParts = [
      'scheme',
      'host',
      'port',
      'user',
      'pass',
      'path',
      'query',
      'fragment',
    ];

    foreach ($urlParts as $part) {
      if (!isset($parsedUrl[$part])) {
        continue;
      }
      $url .= ($part == "port") ? ':' : '';
      $url .= ($part == "query") ? '?' : '';
      $url .= ($part == "fragment") ? '#' : '';
      $url .= $parsedUrl[$part];
      $url .= ($part == "scheme") ? '://' : '';
    }

    return $url;
  }

}
