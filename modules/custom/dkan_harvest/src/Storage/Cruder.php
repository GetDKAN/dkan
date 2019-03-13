<?php

namespace Drupal\dkan_harvest\Storage;

/**
 * @file
 * File for managing internal data structurese for Harvests.
 */
class Cruder {

  protected $db;

  function __construct() {
    $this->db = \Drupal::database();
  }

  /**
   * Reads single entry.
   *
   * @param string $table
   *   The table to query.
   * @param string $fields
   *   The fields to retrieve.
   * @param string $id
   *   The id to retrieve.
   *
   * @return array
   *   A single entry with keys defined by the $field var.
   */
  protected function pRead($table, $fields, $id = NULL) {
    $results = $this->readQuery($table, $fields, $id);
    $items = [];
    foreach ($results as $result) {
      foreach($fields as $field) {
        $items[$field][] = $result->{$field};
      }
    }
    return $items;
  }


  protected function pDelete($table, $id, $val) {
    $result = $this->db->delete($table)
      ->condition($id, $val)
      ->execute();
    return $result;
  }

  /**
   * Read query.
   *
   * @param string $table
   *   The table to query.
   * @param string $fields
   *   The field to retreive.
   * @param string $id
   *   The id to retrieve.
   *
   * @return array
   *   An array of results.
   */
  private function readQuery($table, $fields, $id = NULL) {
    $fieldList = implode(',', $fields);
    if ($id) {
      $f = $fields[0];

      $query = $this->db->query("SELECT $fieldList FROM {$table} WHERE $f = :id", [":id" => $id]);
    }
    else {
      $query = $this->db->query("SELECT $fieldList FROM {$table}");
    }
    return $query->fetchAll();
  }
}
