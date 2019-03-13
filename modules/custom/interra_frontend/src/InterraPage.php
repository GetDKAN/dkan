<?php

namespace Drupal\interra_frontend;

class InterraPage {


  public function __construct($chunkId, $path = '/') {
    $this->chunkId = $chunkId;
    $p = explode('/', $path);
    if (count($p) > 1) {
      $path = $p[1];
    }
    $this->path = $path;
  }

  public function build() {
    return file_get_contents(DRUPAL_ROOT . "/data-catalog-frontend/build/index.html");
  }

}
