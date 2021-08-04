<?php

namespace Drupal\common;

/**
 * UrlHostTokenResolver.
 */
class UrlHostTokenResolver {
  const TOKEN = "h-o.st";
  const PUBLIC_SCHEME = 'public://';

  public static function getPublicHttpPath(): ?string {
    $public_stream = \Drupal::service('stream_wrapper_manager')
      ->getViaUri(self::PUBLIC_SCHEME);
    return $public_stream ? $public_stream->getExternalUrl() : NULL;
  }

  /**
   * Resolve host token string to actual domain URL.
   *
   * @param string $string
   *   Full temporary token URL.
   *
   * @return string
   *   Resolved domain URL.
   */
  public static function resolve(string $string): string {
    $public_url = self::getPublicHttpPath();
    $host = $public_url['host'] ?? \Drupal::request()->getHost();
    if (substr_count($string, self::TOKEN) > 0) {
      $string = str_replace(self::TOKEN, $host, $string);
    }
    return $string;
  }

  /**
   * Resolve host token string to public file path.
   *
   * @param string $url
   *   Full temporary token URL.
   *
   * @return string
   *   Resolved public file path.
   */
  public static function resolveFilePath(string $url): string {
    return preg_replace('/^' . preg_quote(self::getPublicHttpPath(), '/') . '/',
      self::PUBLIC_SCHEME, self::resolve($url));
  }

}
