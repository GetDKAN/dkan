<?php

namespace Drupal\common;

/**
 * UrlHostTokenResolver.
 */
class UrlHostTokenResolver {
  const TOKEN = "h-o.st";

  /**
   * Resolve.
   *
   * Replace a host's token in a string.
   */
  public static function resolve($string) {
    if (substr_count($string, self::TOKEN) > 0) {
      $string = str_replace(self::TOKEN, \Drupal::request()->getHost(), $string);
    }
    return $string;
  }

}
