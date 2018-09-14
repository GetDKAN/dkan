<?php

namespace Drupal\dkan_harvest\Transform;

use Drupal\dkan_harvest\Transform;

class DataJsonToDkan extends Transform {

  protected $collections = ['dataset', 'organization', 'keyword', 'license'];
  protected $collectionsToUpdate = ['organization', 'keyword', 'license'];

  /**
   * Prepares documents by grouping by collection and adding references.
   *
   * @param array $items;
   *   Array of $docs to prepare. These are expected to come directly from a
   *   data.json file.
   *
   * @return array
   *   Array of $docs with the collections as first level keys and referenced
   *   docs.
   */
  function run(&$items) {
    parent::run($items);
    $migrate = FALSE;
    $docs = $this->collections();
    foreach ($items as $item) {
      $docs = $this->reference($cols, $item);
    }
    return $docs;
  }

  function reference($docs, $item) {
    foreach ($collectionsToUpdate as $collection) {
      if ($item->{$collection}) {
        $docs[$collection][] = $item->{$collection};
        $item->{$collection} = $this->refernceId($refItem, $collection);
      }
    }
    $docs['dataset'][] = $item;
    return $docs;
  }

  function referenceId($refItem, $collection) {
    if ($collection == 'organization') {
      return $refItem->identifier;
    }
    else {
      return $this->createIdentifier($refItem->title);
    }
  }

  function createIdentifier($title) {
    return strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/','', $title));
  }

}
