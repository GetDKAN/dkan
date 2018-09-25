<?php

namespace Drupal\dkan_harvest\Load;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\dkan_harvest\Load;
use Drupal\dkan_harvest\DKANHarvest;

class Dkan8 extends Load {

  protected $DKANHarvest;
  protected $schema;

  protected $collectionsToReference = ['publisher', 'keyword', 'theme', 'license'];

  protected $collectionToBundleMap = [
    'publisher' => 'organization',
    'dataset' => 'dataset',
    'keyword' => 'keyword',
    'theme' => 'theme',
    'license' => 'license',
  ];

  protected $collectionToEntityMap = [
    'dataset' => 'node',
    'organization' => 'node',
    'publisher' => 'node',
    'keyword' => 'term',
    'theme' => 'term',
    'license' => 'term'
  ];

  function run($docs) {
    $this->DKANHarvest = new DKANHarvest();
    $currentSchema = dkan_schema_current_schema();
    $this->schema = new Schema($currentSchema);
    $primaryBundle = $this->schema->config['primaryCollection'];
    $this->collectionsToReference = $this->schema->config['refernces'][$primaryBundle];
    $this->collectionToBundleMap = $this->schema->config['collectionToBundleMap'];
    $this->collectionToEntityMap = $this->schema->config['collectionToEntityMap'];

    $this->log->write('DEBUG', 'Load:run', 'Loading to Dkan8.');
    $cols = $this->config->collectionsToUpdate;
    $results = [];
    foreach ($cols as $n => $c) {
      $results[$c] = array('created' => 0, 'updated' => 0, 'skipped' => 0);
    }

    foreach ($docs as $collection => $items) {
      foreach ($items as $doc) {
        if (in_array($collection, $this->config->collectionsToUpdate)) {
          $this->processCollection($collection, $doc, $results);
        }
      }
    }
    $resultLog = $this->resultsPrint($results);
    $this->log->write('INFO', 'Load::run', "Harvest run completed: $resultLog");
    $this->DKANHarvest->runUpdate($this->runId, $this->sourceId, $results);
  }

  function resultsPrint($results) {
    $lg = '';
    foreach ($results as $bundle => $result) {
      $lg .= $bundle . ": ";
      foreach($result as $action => $num) {
        $lg .= $action . "=" . $num . " ";
      }
      $lg .= "\n";
    }
    return $lg;
  }

  function processCollection($collection, $doc, &$results) {
    // Generat hash.
    $hash = $this->generateHash($doc);
    $oldHash = $this->getHash($doc);
    // NEW: There is no old hash record.
    if (!$oldHash) {
      if ($this->collectionToEntityMap[$collection] == 'node') {
        $this->createNode($collection, $doc);
      } else {
        $this->createTerm($collection, $doc);
      }
      $bundle = $this->collectionToBundleMap[$collection];
      $this->createHashRecord($doc->identifier, $bundle, $this->sourceId, $this->runId, $hash);
      $results[$collection]['created']++;
    // UPDATE: Item exists. Update existing since hashes don't match.
    } elseif (!$this->checkHash($hash, $oldHash)) {
      if ($this->collectionToEntityMap[$collection] == 'node') {
        $this->updateNode($doc);
      } else {
        $this->updateTerm($doc, $collection);
      }
      $results[$collection]['updated']++;
    // SKIP: Hash is the same. Nothing changed so skip.
    } else {
      $results[$collection]['skipped']++;
    }
  }

  function getHash($doc) {
    $hashRecord = '';
    $hashRecord = $this->DKANHarvest->hashRead($doc->identifier);
    if ($hashRecord) {
      return $hashRecord['hash'];
    }
    else {
      return FALSE;
    }
  }

  function generateHash($doc) {
    return $this->DKANHarvest->hashGenerate($doc);
  }

  function checkHash($hash, $oldHash) {
    return $hash == $oldHash ? TRUE : FALSE;
  }

  function createHashRecord($identifier, $bundle, $sourceId, $runId, $hash) {
    return $this->DKANHarvest->hashCreate($identifier, $bundle, $sourceId, $runId, $hash);
  }

  function saveFilesLocally($distributions) {
    // TODO: Check if file exists first.
    foreach ($distributions as $n => $dist) {
      // Only get file if it is downloadable.
      $file = $dist->downloadURL ? $dist->downloadURL : '';
      // TODO: Check if file is downloadable format.
      if ($file) {
        $file_content = file_get_contents($file);
        $directory = 'public://';
        file_prepare_directory($directory, FILE_CREATE_DIRECTORY);
        $file = file_save_data($file_content, $directory . basename($file), FILE_EXISTS_REPLACE);
        // TODO: If we are managing files locally we might want to save the file
        // id for storage purposes.
        $distributions[$n]->downloadURL = $file->url;
      }
    }
    return $distributions;
  }

  function createNode($collection, $doc) {
    $bundle = $this->collectionToBundleMap[$collection] ? $this->collectionToBundleMap[$collection] : $collection;
    // TODO: Add mapping for required fields.
    $title = isset($doc->title) ? $doc->title : $doc->name;
    $this->log->write('DEBUG', 'saveNode', 'Saving ' . $title);
    if ($this->migrate) {
      $doc->distributions = $this->saveFilesLlocally($distributions);
    }
    $nodeWrapper = NODE::create([
      'title' => $title,
      'type' => $bundle,
      'uuid' => $doc->identifier,
      'field_json_metadata' => json_encode($doc)
    ]);
    $nodeWrapper->save();
    return $nodeWrapper->id();
  }

  function updateNode($doc) {
    $this->log->write('DEBUG', 'updateNode', 'Updating ' . $doc->identifier);
    // TODO: Just get nid and then load.
    $node = \Drupal::service('entity.repository')->loadEntityByUuid('node', $doc->identifier);
    $date = date_create();
    $node->update = date_timestamp_get($date);
    $node->field_json_metadata = json_encode($doc);
    $node->save();
  }

  function createTerm($bundle, $doc) {
    $this->log->write('DEBUG', 'saveTerm', 'Saving term' . $doc->identifier);
    $term = Term::create([
      'name' => $doc->title,
      'vid' => $bundle,
      'uuid' => $doc->identifier,
      'field_json_metadata' => json_encode($doc)
    ]);
    // Save the taxonomy term.
    $term->save();
    return $term->id();
  }

  function updateTerm($doc) {
    $term = \Drupal::service('entity.repository')->loadEntityByUuid('taxonomy_term', $doc->identifier);
    $date = date_create();
    $term->update = date_timestamp_get($date);
    $term->field_json_metadata = json_encode($doc);
    $term->save();
  }
}
