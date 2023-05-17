<?php

namespace Drupal\Tests\datastore\Unit\Plugin\QueueWorker;

use Drupal\common\Storage\DatabaseTableInterface;
use Drupal\common\Storage\Query;

/**
 * @todo Modify the storage class in Contracts to allow the necessary changes.
 */
class TestMemStorage implements DatabaseTableInterface, \JsonSerializable {

  protected $schema = [];

  protected $storage = [];

  public function destruct() {
  }

  public function query(Query $query) {
  }

  public function primaryKey() {
  }

  public function retrieve(string $id) {
    if (isset($this->storage[$id])) {
      return $this->storage[$id];
    }
    return NULL;
  }

  public function retrieveAll(): array {
    return $this->storage;
  }

  public function store($data, string $id = NULL): string {
    return $this->storeMultiple([$data], $id);
  }

  public function storeMultiple(array $data, string $id = NULL): string {
    if (!isset($id)) {
      $ids = array_keys($this->storage);
      if (empty($ids)) {
        $id = 0;
      }
      else {
        $id = array_unshift($ids) + 1;
      }
    }
    foreach ($data as $datum) {
      if (!isset($this->storage[$id])) {
        $this->storage[$id] = $datum;
        $id++;
      }
    }
    return TRUE;
  }

  public function remove(string $id) {
    if (isset($this->storage[$id])) {
      unset($this->storage[$id]);
      return TRUE;
    }
    return FALSE;
  }

  public function count(): int {
    return count($this->storage);
  }

  public function setTable(): void {
  }

  #[\ReturnTypeWillChange]
  public function jsonSerialize() {
    return (object) ['storage' => $this->storage];
  }

  /**
   * Clean up and set the schema for SQL storage.
   */
  private function cleanSchema() {
    $cleanSchema = $this->schema;
    $cleanSchema['fields'] = [];
    foreach ($this->schema['fields'] as $field => $info) {
      $new = preg_replace('/[^A-Za-z0-9_ ]/', '', $field);
      $new = trim($new);
      $new = strtolower($new);
      $new = str_replace(' ', '_', $new);

      $mysqlMaxColLength = 64;
      if (strlen($new) > $mysqlMaxColLength) {
        $strings = str_split($new, $mysqlMaxColLength - 5);
        $token = $this->generateToken($field);
        $new = $strings[0] . '_' . $token;
      }

      if ($field != $new) {
        $info['description'] = $field;
      }

      $cleanSchema['fields'][$new] = $info;
    }

    $this->schema = $cleanSchema;
  }

  public function setSchema($schema): void {
    $this->schema = $schema;
    $this->cleanSchema();
  }

  public function getSchema(): array {
    return $this->schema;
  }

  public function generateToken($field) {
    $md5 = md5($field);
    return substr($md5, 0, 4);
  }

}
