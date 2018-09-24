<?php

namespace Drupal\dkan_harvest\Transform;

use Drupal\dkan_harvest\Transform;

class DataJsonToDkan extends Transform {

  // TODO: This should come from the schema.
  protected $collections = ['dataset', 'publisher', 'keyword', 'license'];
  protected $collectionsToUpdate = [
    'publisher' => 'object',
    'theme' => 'array',
    'keyword' => 'array',
    'license' => 'string',
  ];

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
    //parent::run($items);
    $this->log->write('DEBUG', 'DataJsonToDkan', 'Running transform from DataJsonToDkan');
    $migrate = FALSE;
    $docs = [];
    foreach ($items as $item) {
      $docs = $this->reference($docs, $item);
    }
    $items = $docs;
  }

  function reference($docs, $item) {
    foreach ($this->collectionsToUpdate as $collection => $dataType) {
      if (isset($item->{$collection})) {
        if ($dataType == 'string') {
          $doc = $this->prepString($item->{$collection});
          // Add reference to doc to primary collection, ie dataset.
          $item->{$collection} = ['dkan-id' => $doc->identifier];
          // Break docs into buckets of collections.
          $docs[$collection][$doc->identifier] = $doc;
        }
        // For now assuming this is an array of strings.
        elseif ($dataType == 'array') {
          $items = [];
          foreach ($item->{$collection} as $i) {
            $doc = $this->prepString($i);
            // Add references.
            $items[] = ['dkan-id' => $doc->identifier];
            // Break docs into buckets of collections.
            $docs[$collection][$doc->identifier] = $doc;
          }
          $item->{$collection} = $items;
        }
        elseif ($dataType == 'object') {
          // TODO: Map this to a schema. For now we know this is a publisher.
          $doc = $item->{$collection};
          $doc->identifier = $this->createIdentifier($doc->name);
          $item->{$collection} = ['dkan-id' => $doc->identifier];
          $docs[$collection][$doc->identifier] = $doc;
        }
      }
    }
    $docs['dataset'][] = $item;
    return $docs;
  }

  function prepString($string) {
    $doc = (object)[];
    $doc->title = $string;
    $doc->identifier = $this->referenceId($doc);
    return $doc;
  }

  function referenceId($doc) {
    if (isset($doc->identifier)) {
      return $doc->identifier;
    }
    else {
      return $this->createIdentifier($doc->title);
    }
  }

  function createIdentifier($title) {
    return strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/','', $title));
  }

}

