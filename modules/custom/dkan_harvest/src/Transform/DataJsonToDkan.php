<?php

namespace Drupal\dkan_harvest\Transform;

/**
 *
 */
class DataJsonToDkan extends DrupalModuleHook {

  /**
   * TODO: This should come from the schema.
   */
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
  public function run(&$items) {
    $this->log->write('DEBUG', 'DataJsonToDkan', 'Running transform from DataJsonToDkan');
    $migrate = FALSE;
    $docs = [];
    foreach ($items as $item) {
      $docs = $this->reference($docs, $item);
    }
    $docs = $this->prepareDist($docs);
    $docs = $this->prepareIds($docs);
    $items = $docs;
  }

  /**
   *
   */
  public function prepareIds($docs) {
    foreach ($docs as $collection => $items) {
      if ($collection == 'dataset') {
        foreach ($items as $k => $doc) {
          if (filter_var($doc->identifier, FILTER_VALIDATE_URL)) {
            $i = explode("/", $doc->identifier);
            $doc->identifier = end($i);
            $docs['dataset'][$k] = $doc;
          }
        }
      }
    }
    return $docs;
  }

  /**
   * Adds ids to distributions.
   */
  public function prepareDist($docs) {
    foreach ($docs as $collection => $items) {
      // Add identifiers to distributions.
      if ($collection == 'dataset') {
        foreach ($items as $k => $doc) {
          if (isset($doc->distribution)) {
            foreach ($doc->distribution as $key => $dist) {
              if (isset($dist->title)) {
                $id = $this->slug($doc->identifier . '-' . $dist->title);
              }
              else {
                if (isset($dist->format)) {
                  $id = $this->slug($doc->identifier . '-' . $dist->format);
                  $title = $format;
                  $doc->distribution[$key]->title = $title;
                }
                elseif (isset($dist->mediaType)) {
                  $type = explode("/", $dist->mediaType);
                  $format = end($type);
                  $title = $format;
                  $id = $this->slug($doc->identifier . '-' . $format);
                  $doc->distribution[$key]->title = $title;
                  $doc->distribution[$key]->format = $format;
                }
              }
              $doc->distribution[$key]->identifier = $id;
            }
          }
        }
        $docs['dataset'][$k] = $doc;
      }
    }
    return $docs;
  }

  /**
   *
   */
  public function slug($str) {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = preg_replace('/-+/', "-", $str);
    return $str;
  }

  /**
   * This creates identifiers for the referenced items.
   *
   * This originally set the 'dkan-id' but that is now done in
   * dkan_dataset_entity_presave().
   *
   * @param array $docs
   *   docs to add to.
   * @param object $item
   *   Individual document for the primary collection, ie dataset.
   */
  public function reference($docs, $item) {
    foreach ($this->collectionsToUpdate as $collection => $dataType) {
      if (isset($item->{$collection})) {
        if ($dataType == 'string') {
          $doc = $this->prepString($item->{$collection});
          // Add reference to doc to primary collection, ie dataset.
          $item->{$collection} = $doc->identifier;
          // Break docs into buckets of collections.
          $docs[$collection][$doc->identifier] = $doc;
        }
        // For now assuming this is an array of strings.
        elseif ($dataType == 'array') {
          $items = [];
          foreach ($item->{$collection} as $i) {
            $doc = $this->prepString($i);
            // Add references.
            $items[] = $doc->identifier;
            // Break docs into buckets of collections.
            $docs[$collection][$doc->identifier] = $doc;
          }
          $item->{$collection} = implode(',', $items);
        }
        elseif ($dataType == 'object') {
          // TODO: Map this to a schema. For now we know this is a publisher.
          $doc = $item->{$collection};
          $doc->identifier = $this->createIdentifier($doc->name);
          $item->{$collection} = $doc->identifier;
          $docs[$collection][$doc->identifier] = $doc;
        }
      }
    }
    $docs['dataset'][] = $item;
    return $docs;
  }

  /**
   *
   */
  public function prepString($string) {
    $doc = (object) [];
    $doc->title = $string;
    $doc->identifier = $this->referenceId($doc);
    return $doc;
  }

  /**
   *
   */
  public function referenceId($doc) {
    if (isset($doc->identifier)) {
      return $doc->identifier;
    }
    else {
      return $this->createIdentifier($doc->title);
    }
  }

  /**
   *
   */
  public function createIdentifier($title) {
    return strtolower(preg_replace('/[^a-zA-Z0-9-_]/', '', $title));
  }

}
