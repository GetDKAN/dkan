<?php

namespace Drupal\interra_api;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Serialization\Json;

class Interra {

  public $config = FALSE;

  private $interraConfigDir = 'profiles/dkan2/modules/custom/interra_api/config';

  public function loadPageSchema() {
    $file = $this->interraConfigDir . '/pageSchema.yml';
    return Yaml::decode(file_get_contents($file));
  }

}
