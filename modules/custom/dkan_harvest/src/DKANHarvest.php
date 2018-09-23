<?php

namespace Drupal\dkan_harvest;

/**
 * @file
 * File for managing internal data structurese for Harvests.
 */

// TODO: Make an actual controller. Clean up. Make entities?
class DKANHarvest {

  function __construct() {
    $this->db = \Drupal::database();
  }

  /**
   * Reads single entry.
   *
   * @param string $table
   *   The table to query.
   * @param string $field
   *   The field to retreive.
   * @param string $id
   *   The id to retreive.
   *
   * @return array
   *   A single entry with keyss defined by the $field var.
   */
  private function read($table, $fields, $id = NULL) {
    $results = $this->readQuery($table, $fields, $id);
		$items = [];
    foreach ($results as $result) {
      foreach($fields as $field) {
        $items[$field] = $result->{$field};
      }
		}
    return $items;
  }

  /**
   * Reads multiple entries.
   *
   * @param string $table
   *   The table to query.
   * @param string $field
   *   The field to retreive.
   * @param string $id
   *   The id to retreive.
   *
   * @return array
   *   A multiple entries with keys defined by the $field var.
   */
  private function readMultiple($table, $fields, $id = NULL) {
    $results = $this->readQuery($table, $fields, $id = NULL);
		$items = [];
    $item = [];
    foreach ($results as $result) {
      foreach($fields as $field) {
        $item[$field] = $result->{$field};
      }
      $items[] = $item;
		}
    return $items;
  }


  /**
   * Read query.
   *
   * @param string $table
   *   The table to query.
   * @param string $field
   *   The field to retreive.
   * @param string $id
   *   The id to retreive.
   *
   * @return array
   *   An array of results.
   */
  private function readQuery($table, $fields, $id = NULL) {
    $fieldList = implode(',', $fields);
    if ($id) {
      $f = array_keys($id)[0];
      $d = array_values($id)[0];
      $s = array_keys($d)[0];
      $query = $this->db->query("SELECT $fieldList FROM {$table} WHERE $f = $s", $d);
    }
    else {
      $query = $this->db->query("SELECT $fieldList FROM {$table}");
    }
		return $query->fetchAll();
  }


  private function delete($table, $id, $val) {
		$result = $this->db->delete($table)
			->condition($id, $val)
			->execute();
    return $result;
  }

  //======================================================================
  // Run CRUD
  //======================================================================

  public function runList() {
    $items = $this->read('harvest_run', array('run_id'));
    return $items;
  }

  public function runCreate($sourceId, $results = array()) {
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

  public function runRead($runId) {
    $items = $this->read('harvest_run', array('source_id', 'results', 'timestamp'), array('run_id' => array(':run_id' => $runId)));
    return $items[0];
	}

  public function runUpdate($runId, $sourceId, $results) {
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

  public function runDelete($sourceId) {
		$result = $this->delte('harvest_run', 'run_id', $runId);
    return $result;
	}

  //======================================================================
  // Log CRUD
  //======================================================================

  protected $logFields = array('log_id', 'identifier', 'run_id', 'action', 'level', 'message');

  public function logList() {
    $items = $this->read('harvest_log', $logFields);
    return $items;
  }

  public function logCreate($identifier, $run_id, $action, $level, $message) {
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

  public function logRead($logId) {
    $items = $this->read('harvest_run', $logFields, array('log_id' => array(':log_id' => $logId)));
    return $items[0];
	}

  public function logUpdate($sourceId, $results) {
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
  public function logDelete($id) {
		$result = $this->delete('harvest_log', 'id', $id);
    return $result;
	}

  //======================================================================
  // Hash CRUD
  //======================================================================

  protected $hashFields = array('identifier', 'run_id', 'hash', 'timestamp');

  public function hashList() {
    $items = $this->read('harvest_hash', $hashFields);
    return $items;
  }

  public function hashCreate($identifier, $bundle, $sourceId, $runId, $hash) {
		$date = date_create();
		$result = $this->db->insert('harvest_hash')
			->fields([
				'identifier' => $identifier,
				'bundle' => $bundle,
				'source_id' => $sourceId,
				'run_id' => $runId,
				'hash' => $hash,
        'timestamp' => date_timestamp_get($date),
			])
			->execute();
    return $result;
	}

  public function hashGenerate($doc) {
    return hash('sha256', serialize($doc));
  }

  public function hashRead($identifier) {
    $items = $this->read('harvest_hash', $this->hashFields, array('identifier' => array(':identifier' => $identifier)));
    return $items;
	}

  public function hashReadIdsBySource($sourceId) {
    $items = $this->readMultiple('harvest_hash', array('bundle', 'identifier'), array('source_id' => array(':sourceId' => $sourceId)));
    $ids = [];
    foreach ($items as $item) {
      $ids[] = array('identifier' => $item['identifier'], 'bundle' => $item['bundle']);
    }
    return $ids;
	}

  public function hashUpdate($sourceId, $hash) {
		$date = date_create();
		$result = $this->db->update('harvest_hash')
			->fields([
				'source_id' => $sourceId,
				'config' => $config,
        'timestamp' => date_timestamp_get($date),
			])
			->execute();
    return $result;
	}

  public function hashDelete($identifier) {
		$result = $this->delte('harvest_hash', 'identifier', $identifier);
    return $result;
	}

  //======================================================================
  // Source CRUD
  //======================================================================

  public function sourceList() {
    $items = $this->read('harvest_source', array('source_id'));
    return $items;
  }

  public function sourceCreate($sourceId, $config) {
		$result = $this->db->insert('harvest_source')
			->fields([
				'source_id' => $sourceId,
				'config' => json_encode($config),
			])
			->execute();
    return $result;
	}

  public function sourceRead($sourceId) {
    $items = $this->read('harvest_source', array('config'), array('source_id' => array(':source_id' => $sourceId)));
    $config = json_decode($items['config']);
    return $config;
	}

  public function sourceUpdate($sourceId, $config) {
		$result = $this->db->update('harvest_source')
			->fields([
				'source_id' => $sourceId,
				'config' => $config,
			])
			->execute();
    return $result;
	}

  public function sourceDelete($sourceId) {
		$result = $this->delete('harvest_source', 'source_id', $sourceId);
    return $result;
	}

}
