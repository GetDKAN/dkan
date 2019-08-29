<?php

namespace Drupal\dkan_frontend;

/**
 * Class.
 */
class Page {

  private $appRoot;

  /**
   * Constructor.
   */
  public function __construct(string $appRoot) {
    $this->appRoot = $appRoot;
  }

  /**
   * Build.
   *
   * @return string|bool
   *   False if file doesn't exist.
   *
   * @todo /data-catalog-frontend/build/index.html may not always exist.
   */
  public function build($name) {
    if ($name == 'home') {
      $file = $this->appRoot . "/data-catalog-frontend/public/index.html";
    }
    else {
      $name = str_replace("__", "/", $name);
      $file = $this->appRoot . "/data-catalog-frontend/public/{$name}/index.html";
    }
    return is_file($file) ? file_get_contents($file) : FALSE;
  }

}
