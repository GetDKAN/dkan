<?php

namespace Drupal\common;

/**
 * UrlHostTokenResolver.
 */
class UrlHostTokenResolver {
  const TOKEN = "h-o.st";

  /**
   * Resolve host token string to actual domain URL.
   *
   * @param string $string
   *   Full temporary token URL.
   * @return string
   *   Resolved domain URL.
   */
  public static function resolve(string $string): string {
    if (substr_count($string, self::TOKEN) > 0) {
      $string = str_replace(self::TOKEN, \Drupal::request()->getHost(), $string);
    }
    return $string;
  }

  /**
   * Resolve host token string to public file path.
   *
   * @param string $url
   *   Full temporary token URL.
   * @return string
   *   Resolved public file path.
   */
  public static function resolveFilePath(string $url): string {
    $public_scheme = 'public://';
    $http_path = \Drupal::service('stream_wrapper_manager')
      ->getViaUri($public_scheme)
      ->getExternalUrl();
    return preg_replace('/^' . preg_quote($http_path, '/') . '/', $public_scheme, $url);
  }

}
