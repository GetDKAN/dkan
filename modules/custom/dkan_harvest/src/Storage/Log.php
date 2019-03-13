<?php

namespace Drupal\dkan_harvest\Storage;

class Log extends Cruder {
  //======================================================================
  // Log CRUD
  //======================================================================

  protected $logFields = array('log_id', 'identifier', 'run_id', 'action', 'level', 'message');

  public function index() {
    $items = $this->pRead('harvest_log', $this->logFields);
    return $items;
  }

  public function create($identifier, $run_id, $action, $level, $message) {
    $date = date_create();
    $result = $this->db->insert('harvest_log')
      ->fields([
        'identifier' => $identifier,
        'run_id' => $run_id,
        'action' => $action,
        'level' => $level,
        'message' => $message,
        'timestamp' => date_timestamp_get($date),
      ])
      ->execute();
    return $result->log_id;
  }

  public function read($logId) {
    $items = $this->read('harvest_run', $this->logFields, array('log_id' => array(':log_id' => $logId)));
    return $items[0];
  }

  public function update($sourceId, $results) {
    $date = date_create();
    $result = $this->db->update('harvest_log')
      ->fields([
        'source_id' => $sourceId,
        'results' => $results,
        'timestamp' => date_timestamp_get($date),
      ])
      ->execute();
    return $result;
  }

  // TODO: Delete by source or run or all.
  public function delete($id) {
    $result = $this->pDelete('harvest_log', 'id', $id);
    return $result;
  }
}