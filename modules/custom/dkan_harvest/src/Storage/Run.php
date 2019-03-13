<?php

namespace Drupal\dkan_harvest\Storage;


class Run extends Cruder {

  public function index() {
    $items = $this->pRead('harvest_run', array('run_id'));
    return $items;
  }

  public function create($sourceId, $results = array()) {
    $date = date_create();
    $result = $this->db->insert('harvest_run')
      ->fields([
        'source_id' => $sourceId,
        'results' => json_encode($results),
        'timestamp' => date_timestamp_get($date),
      ])
      ->execute();
    // Returns autoincrement run id.
    return $result;
  }

  public function read($runId) {
    $items = $this->pRead('harvest_run', array('source_id', 'results', 'timestamp'), array('run_id' => array(':run_id' => $runId)));
    return $items[0];
  }

  public function update($runId, $sourceId, $results) {
    $date = date_create();
    $result = $this->db->update('harvest_run')
      ->fields([
        'source_id' => $sourceId,
        'results' => json_encode($results),
        'timestamp' => date_timestamp_get($date),
      ])
      ->execute();
    return $result;
  }

  public function delete($runId) {
    $result = $this->pDelete('harvest_run', 'run_id', $runId);
    return $result;
  }



}