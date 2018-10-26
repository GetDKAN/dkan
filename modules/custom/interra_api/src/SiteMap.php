<?php

namespace Drupal\interra_api;

use Drupal\Component\Serialization\Yaml;
use Drupal\interra_api\Load;

class SiteMap {

  private $interraConfigDir = 'profiles/dkan2/modules/custom/interra_api/config';

  private function loadConfig() {
    return Yaml::decode(file_get_contents($this->interraConfigDir . '/siteMap.yml'));
  }

  public function load() {
    $config = $this->loadConfig();
    $siteMap = reset($config['siteMap']);
    array_walk_recursive(
      $siteMap,
      function (&$value) {
        if ($value == 'collections') {
          $value = $this->buildCollections();
        }
      }
    );

    return [$siteMap];
  }

  private function buildCollections() {
    $load = new Load();
    $docs = $load->LoadDocs();
    $docs = $load->formatDocs($docs);
    $map = [];
    $result = [];
    foreach ($docs as $doc) {
      if (isset($doc->publisher)) {
        $item = [
          'title' => $doc->title,
          'loc' => '/dataset/' . $doc->identifier
        ];
        if (isset($doc->distribution)) {
          $dists = [];
          foreach ($doc->distribution as $dist) {
            $dists[] = [
              'title' => $dist->title,
              'loc' => '/distribution/' . $dist->identifier
            ];
          }
          $item['children'] = $dists;
        }
        $map[$doc->publisher->{'dkan-id'}][] = $item;
      }
    }
    foreach ($map as $groupId => $items) {
      $group = $load->loadDocById($groupId);
      if ($group) {
        $group = $load->formatDoc($group);
        $i = (object)[];
        $i->title = $group->name;
        $i->loc = '/organization/' . $group->identifier;
        $i->children = $items;
        $result[] = $i;
      }
    }
    return $result;
  }

}
