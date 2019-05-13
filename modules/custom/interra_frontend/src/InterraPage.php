<?php

namespace Drupal\interra_frontend;

/**
 *
 */
class InterraPage {

  /**
   *
   * @TODO /data-catalog-frontend/build/index.html may not always exist.
   * @return string|boolean false if file doesn't exist.
   */
  public function build() {
    $file = \Drupal::service('app.root') . "/data-catalog-frontend/build/index.html";
    return is_file($file) ? file_get_contents($file) : FALSE;
  }

}
