<?php

namespace Drupal\interra_api;

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
  ];

  function __construct() {
    $currentSchema = dkan_schema_current_schema();
    $schema = new Schema($currentSchema);
    $this->schema = $schema;
    $this->primaryCollection = $schema->config['primaryCollection'];
    $this->collectionToBundleMap = $schema->config['collectionToBundleMap'];
    $this->collectionToEntityMap = $schema->config['collectionToEntityMap'];
    $this->references = $schema->config['references'];
  }


  public function loadDocs($type = 'dataset', $entity = 'node') {
    if ($entity == 'node') {
      $ids = \Drupal::entityQuery($entity)
        ->condition('status', 1)
        ->condition('type', $type)
        ->execute();
      return Node::loadMultiple($ids);
    }
    else if ($entity == 'taxonomy_term') {
      $ids = \Drupal::entityQuery($entity)
        //->condition('status', 1)
        ->condition('vid', $type)
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

  public function loadByType($type) {
    $entity = $this->collectionToEntityMap[$type];
    $docs = $this->loadDocs($type, $entity);
    $docs = $this->formatDocs($docs);
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
          $refDoc = $this->loadDocById($id, $entity);
          $doc->{$collection} = $this->formatDoc($refDoc);
        }
        // For now assuming this is an array of strings.
        elseif ($dataType == 'array') {
          $items = [];
          foreach ($doc->{$collection} as $i) {
            if (isset($i->{'dkan-id'})) {
              $id = $i->{'dkan-id'};
              $refDoc = $this->loadDocById($id, $entity);
              $items[] = $this->formatDoc($refDoc);
            }
          }
          $doc->{$collection} = $items;
        }
        elseif ($dataType == 'object') {
          // TODO: Map this to a schema. For now we know this is a publisher.
          $item = $doc->{$collection};
          $id = $item->{'dkan-id'};
          $refDoc = $this->loadDocById($id, $entity);
          $doc->{$collection} = $this->formatDoc($refDoc);
        }
      }
    }
    return $doc;
  }
}
