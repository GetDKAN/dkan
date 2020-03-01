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
   *
   * If a CSV resource is being saved a job should be created.
   */
  public function insert() {
    $entity = $this->node;
    if ($this->getDataType() != 'distribution') {
      return;
    }

    if ($this->isDatastorable()) {
      try {
        /* @var $datastoreService \Drupal\dkan_datastore\Service */
        $datastoreService = \Drupal::service('dkan_datastore.service');
        $datastoreService->import($entity->uuid(), TRUE);
      }
      catch (\Exception $e) {
        $this->setLoggerFactory(\Drupal::service('logger.factory'));
        $this->log('dkan_datastore', $e->getMessage());
      }
    }

  }

  /**
   * Predelete.
   *
   * When a resource is deleted, any incomplete import jobs should be removed.
   * Also, its datastore should go.
   */
  public function predelete() {
    $entity = $this->node;
    if ($this->getDataType() != 'distribution') {
      return;
    }

    try {
      /* @var $datastoreService \Drupal\dkan_datastore\Service */
      $datastoreService = \Drupal::service('dkan_datastore.service');
      $datastoreService->drop($entity->uuid());
    }
    catch (\Exception $e) {
      $this->setLoggerFactory(\Drupal::service('logger.factory'));
      $this->log('dkan_datastore', $e->getMessage());
    }

    $metadata = $this->getMetaData();
    $data = $metadata->data;
    if (isset($data->downloadURL)) {
      $url = $data->downloadURL;
      $pieces = explode('sites/default/files/', $url);
      $path = "public://" . end($pieces);
      file_unmanaged_delete($path);
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
