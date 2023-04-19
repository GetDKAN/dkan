<?php

namespace Drupal\harvest\Load;

use Drupal\metastore\Exception\ExistingObjectException;
use Drupal\metastore\Service;
use Harvest\ETL\Load\Load;

/**
 * Class.
 */
class Dataset extends Load {

  protected Service $metastoreService;

  public function __construct($harvest_plan, $hash_storage, $item_storage) {
    $this->metastoreService = \Drupal::service('dkan.metastore.service');
    parent::__construct($harvest_plan, $hash_storage, $item_storage);
  }

  /**
   * Public.
   */
  public function removeItem($id) {
    $this->metastoreService->delete("dataset", "{$id}");
  }

  /**
   * Private.
   */
  protected function saveItem($item) {
    if (!is_string($item)) {
      $item = json_encode($item);
    }

    $schema_id = 'dataset';
    $item = $this->metastoreService->getValidMetadataFactory()->get($item, $schema_id);
    try {
      $this->metastoreService->post($schema_id, $item);
    }
    catch (ExistingObjectException $e) {
      $this->metastoreService->put($schema_id, $item->{"$.identifier"}, $item);
    }
  }

}
