<?php

namespace Drupal\dkan_api\Storage;

use Sae\Contracts\Storage;
use Sae\Contracts\BulkRetriever;

class Organization implements Storage, BulkRetriever
{
  private $datasetStorage;

  public function __construct()
  {
    $this->datasetStorage = new DrupalNodeDataset();
  }

  public function retrieveAll()
  {
    $organizations = [];
    $datasets = json_decode($this->datasetStorage->retrieveAll());
    foreach($datasets as $dataset) {
      if ($organization = $dataset->organization) {
        $organizations[$organization] = $organization;
      }
    }
    $values = array_values($organizations);
    return json_encode($values);
  }

  public function retrieve($id)
  {
    // TODO: Implement retrieve() method.
  }

  public function store($data, $id = null)
  {
    // TODO: Implement store() method.
  }

  public function remove($id)
  {
    // TODO: Implement remove() method.
  }

}