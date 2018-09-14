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

  //======================================================================
  // Log CRUD
  //======================================================================

  public function logList() {
		$query = $this->db->query("SELECT source_id FROM {harvest_log}");
		$results = $query->fetchAll();
		$items = [];
    foreach ($results as $result) {
			$items[] = $result->source_id;
		}
    return $items;
  }

  public function logCreate($sourceId, $run_id, $action, $level, $message) {
		$date = date_create();
		$result = $this->db->insert('harvest_log')
			->fields([
				'source_id' => $sourceId,
				'run_id' => $run_id,
				'action' => $action,
				'level' => $level,
				'message' => $message,
        'timestamp' => date_timestamp_get($date),
			])
			->execute();
    return $result;
	}

  public function logRead($sourceId) {
		$result = $this->db->query("SELECT hash FROM {harvest_run} WHERE source_id = :source_id", array(':source_id' => $sourceId))->fetchObject();
    return $result->hash;
	}

  public function logUpdate($sourceId, $results) {
		$date = date_create();
		$result = $this->db->update('harvest_run')
			->fields([
				'source_id' => $sourceId,
				'results' => $results,
        'timestamp' => date_timestamp_get($date),
			])
			->execute();
    return $result;
	}

  public function logDelete($sourceId) {
		$result = $this->db->delete('harvest_run')
			->condition('source_id', $sourceId)
			->execute();
    return $result;
	}

  //======================================================================
  // Hash CRUD
  //======================================================================

  public function hashList() {
		$query = $this->db->query("SELECT source_id FROM {harvest_hash}");
		$results = $query->fetchAll();
		$items = [];
    foreach ($results as $result) {
			$items[] = $result->source_id;
		}
    return $items;
  }

  public function hashCreate($sourceId, $hash, $run_id) {
		$date = date_create();
		$result = $this->db->insert('harvest_hash')
			->fields([
				'source_id' => $sourceId,
				'hash' => $hash,
				'run_id' => $run_id,
        'timestamp' => date_timestamp_get($date),
			])
			->execute();
    return $result;
	}

  public function hashRead($sourceId) {
		$result = $this->db->query("SELECT hash FROM {harvest_run} WHERE source_id = :source_id", array(':source_id' => $sourceId))->fetchObject();
    return $result->hash;
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

  public function hashDelete($sourceId) {
		$result = $this->db->delete('harvest_hash')
			->condition('source_id', $sourceId)
			->execute();
    return $result;
	}

  //======================================================================
  // Source CRUD
  //======================================================================

  public function sourceList() {
		$query = $this->db->query("SELECT source_id FROM {harvest_source}");
		$results = $query->fetchAll();
		$items = [];
    foreach ($results as $result) {
			$items[] = $result->source_id;
		}
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
		$result = $this->db->query("SELECT config FROM {harvest_source} WHERE source_id = :source_id", array(':source_id' => $sourceId))->fetchObject();
    return json_decode($result->config);
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
		$result = $this->db->delete('harvest_source')
			->condition('source_id', $sourceId)
			->execute();
    return $result;
	}

}
