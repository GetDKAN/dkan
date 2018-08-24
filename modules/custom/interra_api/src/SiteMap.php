<?php

namespace Drupal\interra_api;

use Drupal\Component\Serialization\Yaml;

class SiteMap {

  private $interraConfigDir = 'profiles/dkan2/modules/custom/interra_api/config';

  private function loadConfig() {
    return Yaml::decode(file_get_contents($this->interraConfigDir . '/siteMap.yml'));
  }

  private function load() {
    $config = $this->loadConfig();
    $siteMap = $config->siteMap;
    return $siteMap;
  }

}
