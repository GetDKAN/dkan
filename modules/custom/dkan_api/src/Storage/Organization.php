<?php

namespace Drupal\dkan_api\Storage;

use Harvest\Storage\Storage;

/**
 * Organization.
 */
class Organization implements Storage {
  protected $datasetStorage;

  /**
   * Constructor.
   *
   * @todo makes more snse to have this class extend DrupalNodeDataset instead of injecting it.
   * @param \Drupal\dkan_api\Storage\DrupalNodeDataset $datasetStorage
   *   Injected Nodeset.
   */
  public function __construct(DrupalNodeDataset $datasetStorage) {
    $this->datasetStorage = $datasetStorage;
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveAll():array {
    $organizations = [];
    $datasets = json_decode($this->datasetStorage->retrieveAll());
    foreach ($datasets as $dataset) {
      if ($organization = $dataset->organization) {
        $organizations[$organization] = $organization;
      }
    }
    $values = array_values($organizations);
    return json_encode($values);
  }

  /**
   * {@inheritdoc}
   */
  public function retrieve(string $id): string {
    // TODO: Implement retrieve() method.
  }

  /**
   * {@inheritdoc}
   */
  public function store(string $data, string $id = NULL): string {
    // TODO: Implement store() method.
  }

  /**
   * {@inheritdoc}
   */
  public function remove(string $id) {
    // TODO: Implement remove() method.
  }

}
