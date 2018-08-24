<?php

namespace Drupal\interra_api;

use Drupal\Component\Serialization\Yaml;

class SiteMap {

  private $interraConfigDir = 'profiles/dkan2/modules/custom/interra_api/config';

  private function loadConfig() {
    return Yaml::decode(file_get_contents($this->interraConfigDir . '/siteMap.yml'));
  }

  public function load() {
    $config = $this->loadConfig();
    return $config['siteMap'];
  }

}
