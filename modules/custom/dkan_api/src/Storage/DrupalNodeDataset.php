<?php

namespace Drupal\dkan_api\Storage;

use Drupal\node\Entity\Node;
use Contracts\Storage;
use Contracts\BulkRetriever;

class DrupalNodeDataset implements Storage, BulkRetriever {

  /**
   * @var Drupal\dkan_api\Storage\ThemeValueReferencer
   */
  private $themeValueReferencer;

  /**
   * Constructs a DrupalNodeDataset.
   */
  public function __construct() {
    $this->themeValueReferencer = new ThemeValueReferencer();
  }

  protected function getType() {
    return 'data';
  }

  public function retrieve(string $id): ?string {

    foreach ($this->getNodesByUuid($id) as $nid) {
      $node = Node::load($nid);
      return $this->themeDereferenced($node->field_json_metadata->value);
    }

    throw new \Exception("No data with the identifier {$id} was found.");
  }

  public function retrieveAll(): array {
    $node_nids = \Drupal::entityQuery('node')
      ->condition('type', $this->getType())
      ->condition('field_data_type', 'dataset')
      ->execute();

    $all = [];
    foreach ($node_nids as $nid) {
      $node = Node::load($nid);
      $all[] = $this->themeDereferenced($node->field_json_metadata->value);
    }
    return $all;
  }

  public function remove(string $id) {

    foreach ($this->getNodesByUuid($id) as $nid) {
      $node = Node::load($nid);
      return $node->delete();
    }
  }

  public function store(string $data, string $id = NULL): string {

    $data = json_decode($data);

    if (isset($data->theme)) {
      $data->theme = $this->themeValueReferencer->reference($data);
    }

    if (!$id && isset($data->identifier)) {
        $id = $data->identifier;
    }

    if ($id) {
        $node = \Drupal::service('entity.repository')->loadEntityByUuid('node', $id);
    }

    /* @var $node \Drupal\node\NodeInterface */
    if ($node) {    // update existing node
      $node->field_data_type = "dataset";
      $node->field_json_metadata = json_encode($data);
      $node->save();
      return $node->uuid();
    }
    else {    // create new node
      $title = isset($data->title) ? $data->title : $data->name;
      $nodeWrapper = NODE::create([
        'title' => $title,
        'type' => 'data',
        'uuid' => $id,
        'field_data_type' => 'dataset',
        'field_json_metadata' => json_encode($data)
      ]);
      $nodeWrapper->save();
      return $nodeWrapper->uuid();
    }

    return NULL;
  }

  private function getNodesByUuid($uuid) {
    return \Drupal::entityQuery('node')
      ->condition('type', $this->getType())
      ->condition('field_data_type', 'dataset')
      ->condition('uuid', $uuid)
      ->execute();
  }

  protected function themeDereferenced($json) {
    $data = json_decode($json);
    if (isset($data->theme)) {
      $data->theme = $this->themeValueReferencer->dereference($data);
    }
    return json_encode($data);
  }

}
