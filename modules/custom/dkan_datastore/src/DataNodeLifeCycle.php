<?php

namespace Drupal\dkan_datastore;

use Drupal\dkan_common\AbstractDataNodeLifeCycle;
use Drupal\dkan_common\LoggerTrait;

/**
 * DataNodeLifeCycle.
 */
class DataNodeLifeCycle extends AbstractDataNodeLifeCycle {
  use LoggerTrait;

  /**
   * Insert.
   */
  public function insert() {
    $entity = $this->node;
    if ($this->getDataType() != 'distribution') {
      return;
    }

    if ($this->isDatastorable()) {
      try {
        /* @var $datastore_service \Drupal\dkan_datastore\Service */
        $datastore_service = \Drupal::service('dkan_datastore.service');
        $datastore_service->import($entity->uuid(), TRUE);
      }
      catch (\Exception $e) {
        $this->setLoggerFactory(\Drupal::service('logger.factory'));
        $this->log('dkan_datastore', $e->getMessage());
      }
    }

  }

  /**
   * Private.
   */
  private function isDatastorable() {
    $metadata = $this->getMetaData();
    $data = $metadata->data;

    if (!isset($data->downloadURL) && !isset($data->accessURL)) {
      return FALSE;
    }

    if (!(isset($data->mediaType) && $data->mediaType == 'text/csv') &&
      !(isset($data->format) && $data->format == 'csv')) {
      return FALSE;
    }

    return TRUE;
  }

}
