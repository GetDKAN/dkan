<?php

namespace Drupal\harvest\Load;

use Drupal\metastore\Exception\ExistingObjectException;
use Drupal\metastore\Service;
use Harvest\ETL\Load\Load;

/**
 * Class.
 */
class Dataset extends Load {

  /**
   * Public.
   */
  public function removeItem($id) {
    $service = $this->getMetastoreService();
    $service->delete("dataset", "{$id}");
  }

  /**
   * Private.
   */
  protected function saveItem($item) {
    $service = $this->getMetastoreService();
    if (!is_string($item)) {
      $item = json_encode($item);
    }

    try {
      $service->post('dataset', $item);
    }
    catch (ExistingObjectException $e) {
      $object = json_decode($item, TRUE);
      $service->put('dataset', $object['identifier'], $item);
    }
  }

  /**
   * Get the metastore service.
   *
   * @return \Drupal\metastore\Service
   *   Metastore service.
   *
   * @codeCoverageIgnore
   */
  protected function getMetastoreService(): Service {
    $service = \Drupal::service('dkan.metastore.service');
    return $service;
  }

}
