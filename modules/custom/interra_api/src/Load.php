<?php

namespace Drupal\interra_api;

use Drupal\dkan_api\Storage\DrupalNodeDataset;
use Drupal\dkan_schema\Schema;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

class Load {

  private $schema = NULL;
  private $collectionToBundleMap = [];
  private $collectionToEntityMap = [];
  private $primaryCollection = 'dataset';
  // TODO: We can get this from the schema.
  protected $collectionTypes = [
    'publisher' => 'object',
    'theme' => 'array',
    'keyword' => 'array',
    'license' => 'string',
    'distribution' => 'array',
  ];

  function __construct() {
    $schema = new Schema();
    $this->schema = $schema;
    $this->primaryCollection = $schema->config['primaryCollection'];
    $this->collectionToBundleMap = $schema->config['collectionToBundleMap'];
    $this->collectionToEntityMap = $schema->config['collectionToEntityMap'];
    $this->references = $schema->config['references'];
  }


  public function loadDocs($bundle = 'dataset', $entity = 'node') {
    if ($entity == 'node') {
      $ids = \Drupal::entityQuery($entity)
        ->condition('status', 1)
        ->condition('type', $bundle)
        ->execute();
      return Node::loadMultiple($ids);
    }
    else if ($entity == 'taxonomy_term') {
      $ids = \Drupal::entityQuery($entity)
        //->condition('status', 1)
        ->condition('vid', $bundle)
        ->execute();
      return Term::loadMultiple($ids);
    }

    return NULL;
  }

  public function loadDocById($uuid, $entity = 'node') {
    $id = \Drupal::entityQuery($entity)
      //->condition('status', 1)
      ->condition('uuid', $uuid)
      ->execute();
    if ($id && $entity == 'node') {
      return Node::load(reset($id));
    }
    else if ($id && $entity == 'taxonomy_term') {
      return Term::load(reset($id));
    }

    return NULL;
  }

  public function loadAPIDoc($id, $entity) {
    if (substr($entity, 0, 1) == '!') {
      if ($bundle = array_search($entity, $this->collectionToEntityMap)) {
        $docs = $this->loadByType($bundle);
        foreach ($docs as $doc) {
          if (isset($doc->identifier) && $doc->identifier === $id) {
            return $doc;
          }
        }
      }
    }
    else {
      $doc = $this->loadDocById($id, $entity);
      $formatted = $this->formatDoc($doc);
      $dereferenced = $this->dereference($formatted);
      return $dereferenced;
    }
    return NULL;
  }

  public function formatDocs($docs) {
    $index = array();
    foreach($docs as $id => $doc) {
      $index[] = $this->formatDoc($doc);
    }
    return $index;
  }

  public function derefDocs($docs) {
    $index = array();
    foreach($docs as $id => $doc) {
      $index[] = $this->dereference($doc);
    }
    return $index;
  }

  public function formatDoc($doc) {
    $item = $doc->get('field_json_metadata')->getValue()[0]['value'];
    return json_decode($item);
  }

  public function loadByType($bundle) {
    if ($bundle == "organization") {
      $store = new DrupalNodeDataset();
      $datasets = $store->retrieveAll();
      $organizations = [];
      foreach ($datasets as $json) {
        $dataset = json_decode($json);
        if (isset($dataset->publisher)) {
          $organizations[$dataset->publisher->name] = $dataset->publisher;
        }
      }
      return array_values($organizations);
    }
    $entity = $this->collectionToEntityMap[$bundle];
    // We've found from the schema !collection.field which lets us have a route
    // for a field.
    if (substr($entity, 0, 1) == '!') {
      $entity = substr($entity, 1);
      $items = explode('.', $entity);
      $bundle = $items[0];
      $field = $items[1];
      $entity = $this->collectionToEntityMap[$bundle];
      $pdocs = $this->loadDocs($bundle, $entity);
      $pdocs = $this->formatDocs($pdocs);
      $fieldType = $this->collectionTypes[$field];
      $docs = [];
      foreach ($pdocs as $doc) {
        if (isset($doc->{$field})) {
          if ($fieldType == 'array') {
            foreach ($doc->{$field} as $item) {
              $docs[] = $item;
            }
          }
          else {
            $docs[] = $doc->{$field};
          }
        }
      }
      return $docs;
    } else {
      $docs = $this->loadDocs($bundle, $entity);
      $docs = $this->formatDocs($docs);
    }
    return $this->derefDocs($docs);
  }

  public function loadAll() {
    $docs = $this->loadDocs();
    return $this->formatDocs($docs);
  }

  public function dereference($doc) {
    foreach ($this->references[$this->primaryCollection] as $collection => $bundle) {
      $dataType = $this->collectionTypes[$collection];
      $entity = $this->collectionToEntityMap[$collection];
      if (isset($doc->{$collection})) {
        if ($dataType == 'string') {
          $id = $doc->{$collection}->{'dkan-id'};
          if ($id) {
            $refDoc = $this->loadDocById($id, $entity);
            if ($refDoc) {
              $doc->{$collection} = $this->formatDoc($refDoc);
            }
          }
        }
        // For now assuming this is an array of strings.
        elseif ($dataType == 'array') {
          $items = [];
          foreach ($doc->{$collection} as $i) {
            if (isset($i->{'dkan-id'})) {
              $id = $i->{'dkan-id'};
              if ($id) {
                $refDoc = $this->loadDocById($id, $entity);
                if ($refDoc) {
                  $items[] = $this->formatDoc($refDoc);
                }
              }
            }
          }
          $doc->{$collection} = $items;
        }
        elseif ($dataType == 'object') {
          // TODO: Map this to a schema. For now we know this is a publisher.
          $item = $doc->{$collection};
          $id = $item->{'dkan-id'};
          if ($id) {
            $refDoc = $this->loadDocById($id, $entity);
            if ($refDoc) {
              $doc->{$collection} = $this->formatDoc($refDoc);
            }
          }
        }
      }
    }
    return $doc;
  }
}
