<?php

namespace Drupal\dkan_harvest\Load;

use Drupal\taxonomy\Entity\Term;
use Drupal\dkan_schema\Schema;
use Drupal\dkan_api\Controller\Dataset;

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

  protected $fileHelper;

  public function __construct($log, $config, $sourceId, $runId) {
    parent::__construct($log, $config, $sourceId, $runId);
    $this->fileHelper = new FileHelper();
  }

  function run($docs) {
    $this->DKANHarvest = new Cruder();
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
    $this->log->write('DEBUG', 'Load::run', "Harvester run completed: $resultLog");
    $this->DKANHarvest->runUpdate($this->runId, $this->sourceId, $results);
    return $results;
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
    $entity = $this->collectionToEntityMap[$collection];
    $bundle = $this->collectionToBundleMap[$collection];
    // Generat hash.
    $hash = $this->generateHash($doc);
    $oldHash = $this->getHash($doc);
    // NEW: There is no old hash record.
    if (!$oldHash) {
      if ($this->entityExists($entity, $doc->identifier)) {
        $this->updateEntity($entity, $doc);
        $results[$collection]['updated']++;
      }
      else {
        $this->createEntity($entity, $bundle, $doc);
        $results[$collection]['created']++;
      }
      $this->createHashRecord($doc->identifier, $bundle, $this->sourceId, $this->runId, $hash);
    // UPDATE: Item exists. Update existing since hashes don't match.
    } elseif (!$this->checkHash($hash, $oldHash)) {
      // Still check if entity exists in case hash is wrong.
      if ($this->entityExists($entity, $doc->identifier)) {
        $this->updateEntity($entity, $doc);
        $results[$collection]['updated']++;
      }
      else {
        $this->createEntity($entity, $bundle, $doc);
        $results[$collection]['created']++;
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

  function entityExists($entity, $uuid) {
    $table = $entity == 'node' ? 'node' : 'taxonomy_term_data';
    $db = \Drupal::database();
    $query = $db->query("SELECT uuid FROM {$table} WHERE uuid = :uuid", [':uuid' => $uuid]);
    $r = $query->fetchAll();
    return $r ? TRUE : FALSE;
  }

  function createEntity($entity, $bundle, $doc) {
    if ($entity == 'node') {
      // TODO: Add mapping for required fields.
      $title = isset($doc->title) ? $doc->title : $doc->name;
      $this->log->write('DEBUG', 'saveNode', 'Saving ' . $title);
      $myDataset = new Dataset();
      return $myDataset->storeDataset($doc);
    }
    else if ($entity == 'taxonomy_term') {
      $this->log->write('DEBUG', 'saveTerm', 'Saving term ' . $doc->identifier);
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
  }

  function updateEntity($entity, $doc) {
    if ($entity == 'node') {
      $this->log->write('DEBUG', 'updateNode', 'Updating ' . $doc->identifier);
      $myDataset = new Dataset();
      $myDataset->storeDataset($doc);
    }
    else if ($entity == 'taxonomy_term') {
      $term = \Drupal::service('entity.repository')->loadEntityByUuid('taxonomy_term', $doc->identifier);
      $date = date_create();
      $term->update = date_timestamp_get($date);
      $term->field_json_metadata = json_encode($doc);
      $term->save();
    }

  }

  function saveDatasetFilesLocally(&$doc) {
    if (isset($doc->distribution)) {
      foreach ($doc->distribution as $n => $distribution) {
        if (isset($distribution->downloadURL)) {
          $defaultSchemeDir = $this->fileHelper->defaultSchemeDirectory();
          $targetDir = $defaultSchemeDir . '/distribution';
          $this->fileHelper->prepareDir($targetDir);
          if ($result = $this->fileHelper->retrieveFile($distribution->downloadURL, $targetDir, FALSE)) {
            $doc->distribution[$n]->downloadURL = $this->fileHelper->fileCreate($distribution->downloadURL);
          }
        }
      }
    }
  }

  protected function saveItem($item) {
    // TODO: Implement saveItem() method.
  }


}
