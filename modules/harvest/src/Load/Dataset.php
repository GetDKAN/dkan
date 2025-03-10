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
   * Metastore service.
   *
   * @var \Drupal\metastore\MetastoreService
   */
  private MetastoreService $metastoreService;

  /**
   * {@inheritDoc}
   */
  public function __construct($harvest_plan, $hash_storage, $item_storage) {
    $this->metastoreService = \Drupal::service('dkan.metastore.service');
    parent::__construct($harvest_plan, $hash_storage, $item_storage);
  }

  /**
   * Remove dataset item from storage.
   *
   * @param string $identifier
   *   Identifier.
   */
  public function removeItem($identifier): void {
    $this->metastoreService->delete('dataset', $identifier);
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
    if (!is_string($item)) {
      $item = json_encode($item);
    }

    $schema_id = 'dataset';
    $item = $this->metastoreService->getValidMetadataFactory()->get($item, $schema_id);
    try {
      $this->metastoreService->post($schema_id, $item);
    }
    catch (ExistingObjectException) {
      $this->metastoreService->put($schema_id, $item->{"$.identifier"}, $item);
    }
  }

}
