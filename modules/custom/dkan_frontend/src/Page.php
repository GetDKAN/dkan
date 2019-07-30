<?php

namespace Drupal\dkan_frontend;

/**
 *
 */
class Page {

  /**
   *
   * @TODO /data-catalog-frontend/build/index.html may not always exist.
   * @return string|boolean false if file doesn't exist.
   */
  public function build($name) {
    if ($name == 'home') {
      $file = \Drupal::service('app.root') . "/data-catalog-frontend/public/index.html";
    }
    else {
      $name = str_replace("__", "/", $name);
      $file = \Drupal::service('app.root') . "/data-catalog-frontend/public/{$name}/index.html";
    }
    return is_file($file) ? file_get_contents($file) : FALSE;
  }

}
