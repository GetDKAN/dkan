<?php

namespace Drupal\dkan_harvest\Load;

use Drupal\dkan_metastore\Service;
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
    $service->post('dataset', $item);
  }

  /**
   * Get the metastore service.
   *
   * @return \Drupal\dkan_metastore\Service
   *   Metastore service.
   *
   * @codeCoverageIgnore
   */
  protected function getMetastoreService(): Service {
    $service = \Drupal::service('dkan_metastore.service');
    return $service;
  }

}
