<?php

declare(strict_types = 1);

namespace Drupal\dkan_harvest\Storage;


use Contracts\Storage;

class Source extends Cruder implements Storage {

  public function index() {
    $items = $this->pRead('harvest_source', array('source_id'));
    return $items;
  }

  private function create(string $sourceId, string $config) {
    $result = $this->db->insert('harvest_source')
      ->fields([
        'source_id' => $sourceId,
        'config' => $config,
      ])
      ->execute();
    return $result;
  }

  private function update(string $sourceId, string $config) {
    $result = $this->db->update('harvest_source')
      ->fields([
        'source_id' => $sourceId,
        'config' => $config,
      ])
      ->execute();
    return $result;
  }

  private function delete($sourceId) {
    $result = $this->pDelete('harvest_source', 'source_id', $sourceId);
    return $result;
  }

  public function retrieve(string $id): ?string {
    $items = $this->pRead('harvest_source', array('source_id', 'config'), $id);

    if (isset($items['config'][0])) {
      return $items['config'][0];
    }
    return NULL;
  }


  public function store(string $data, string $id = NULL): string {
    if (!$id) {
      throw new \Exception("id is required");
    }

    $config = $this->retrieve($id);
    if ($config) {
      $result = $this->update($id, $data);
    }
    else {
      $result = $this->create($id, $data);
    }
    return json_encode($result);
  }

  public function remove(string $id) {
    $this->delete($id);
  }


}