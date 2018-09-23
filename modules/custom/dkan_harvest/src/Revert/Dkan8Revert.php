<?php

namespace Drupal\dkan_harvest\Revert;

use Drupal\node\Entity\Node;
use Drupal\dkan_harvest\Revert;
use Drupal\dkan_harvest\DKANHarvest;

class Dkan8Revert extends Revert {

  protected $collectionsToEntityMap = [
    'dataset' => 'node',
    'organization' => 'node',
    'keyword' => 'taxonomy_term',
    'term' => 'taxonomy_term',
    'theme' => 'taxonomy_term',
    'license' => 'taxonomy_term'
  ];

  function run() {
    $this->log->write('DEBUG', 'revert', 'Reverting harvest ' . $this->sourceId);
    $this->DKANHarvest = new DKANHarvest();
    $ids = $this->DKANHarvest->hashReadIdsBySource($this->sourceId);
		foreach ($ids as $id) {
      $this->entityDelete($id);
		}
  }

  function entityDelete($id) {
    $this->log->write('DEBUG', 'revert', 'Reverting harvest item ' . $id['identifier']);
		$type = $this->collectionsToEntityMap[$id['bundle']];
		$items = \Drupal::entityTypeManager()->getStorage($type)->loadByProperties(['uuid' => $id['identifier']]);
    foreach ($items as $item) {
      $item->delete();
    }
  }
}
