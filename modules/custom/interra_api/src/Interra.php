<?php

namespace Drupal\interra_api;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Serialization\Json;

class Interra {

  public $config = FALSE;

  public function loadPageSchema() {
    $file = __DIR__ . '/../config/pageSchema.yml';
    return Yaml::decode(file_get_contents($file));
  }

}
