<?php

namespace Drupal\harvest\Load;

use Drupal\metastore\Exception\ExistingObjectException;
use Drupal\metastore\MetastoreService;
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
   * Save a harvested dataset item into our metastore.
   *
   * @param object $item
   *   An object representing the dataset. This object should comport to
   *   DCAT-US Schema v1.1 once JSON-encoded.
   *
   * @see schema/collections/dataset.json
   */
  protected function saveItem($item) {
    $service = $this->getMetastoreService();
    if (!is_string($item)) {
      $item = json_encode($item);
    }

    $schema_id = 'dataset';
    $item = $service->getValidMetadataFactory()->get($item, $schema_id);
    try {
      $service->post($schema_id, $item);
    }
    catch (ExistingObjectException) {
      $service->put($schema_id, $item->{"$.identifier"}, $item);
    }
  }

  /**
   * Get the metastore service.
   *
   * @return \Drupal\metastore\MetastoreService
   *   Metastore service.
   */
  protected function getMetastoreService(): MetastoreService {
    return \Drupal::service('dkan.metastore.service');
  }

}
