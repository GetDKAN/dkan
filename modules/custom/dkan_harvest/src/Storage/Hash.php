<?php

namespace Drupal\dkan_harvest\Storage;

class Hash extends Cruder {

  protected $hashFields = array('identifier', 'run_id', 'hash', 'timestamp');

  public function hashList() {
    $items = $this->pRead('harvest_hash', $this->hashFields);
    return $items;
  }

  public function create($identifier, $source_id, $hash) {
    $date = date_create();
    $result = $this->db->insert('harvest_hash')
      ->fields([
        'identifier' => $identifier,
        'source_id' => $source_id,
        'hash' => $hash,
        'timestamp' => date_timestamp_get($date),
      ])
      ->execute();
    return $result;
  }

  public function hashGenerate($doc) {
    return hash('sha256', serialize($doc));
  }

  public function read($identifier) {
    $items = $this->pRead('harvest_hash', ['identifier', 'hash'], $identifier);

    if (isset($items['hash'][0])) {
      return $items['hash'][0];
    }

    return NULL;
  }

  public function readIdsBySource($sourceId) {
    $items = $this->pRead('harvest_hash', array('source_id', 'identifier'), $sourceId);
    $ids = [];

    if (isset($items['identifier'])) {
      $ids = $items['identifier'];
    }

    return $ids;
  }

  public function update($identifier, $hash) {
    $date = date_create();
    $result = $this->db->update('harvest_hash')
      ->fields([
        'identifier' => $identifier,
        'hash' => $hash,
        'timestamp' => date_timestamp_get($date),
      ])
      ->execute();
    return $result;
  }

  public function delete($identifier) {
    $result = $this->pDelete('harvest_hash', 'identifier', $identifier);
    return $result;
  }

}