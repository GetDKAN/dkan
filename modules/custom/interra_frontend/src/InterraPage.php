<?php

namespace Drupal\interra_frontend;

class InterraPage {

  public function build() {
    return file_get_contents(DRUPAL_ROOT . "/data-catalog-frontend/build/index.html");
  }

}
