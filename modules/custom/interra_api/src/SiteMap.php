<?php

namespace Drupal\interra_api;

use Drupal\Component\Serialization\Yaml;
use Drupal\interra_api\Load;
use Drupal\dkan_schema\Schema;

class SiteMap {

  private function loadConfig() {
    $file = __DIR__ . '/../config/siteMap.yml';
    return Yaml::decode(file_get_contents($file));
  }

  public function load() {
    $siteMap = [];
		if ($siteMap = $this->cacheGet()) {
      return [$siteMap];
    }
    else {
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
      $this->cacheSet($siteMap);
      return [$siteMap];
    }
  }

  private function cacheGet() {
		$cid = 'interra_sitemap:' . \Drupal::languageManager()
			->getCurrentLanguage()
			->getId();
		$data = NULL;
		if ($cache = \Drupal::cache()
			->get($cid)) {
			$data = $cache->data;
		}
		return $data;
  }

  private function cacheSet($data) {
		$cid = 'interra_sitemap:' . \Drupal::languageManager()
			->getCurrentLanguage()
			->getId();
		\Drupal::cache()
			->set($cid, $data);
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
