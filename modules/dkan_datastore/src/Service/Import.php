<?php

namespace Drupal\dkan_datastore\Service;

use Drupal\dkan_common\DataResource;
use Drupal\dkan_datastore\Storage\DatabaseTableFactory;
use Drupal\dkan_datastore\Storage\ImportJobStoreFactory;

/**
 * Datastore import service.
 *
 * @deprecated
 * @see \Drupal\dkan_datastore\Service\ImportService
 */
class Import extends ImportService {

  /**
   * Create a resource service instance.
   *
   * @param \Drupal\dkan_common\DataResource $resource
   *   DKAN Resource.
   * @param \Drupal\dkan_datastore\Storage\ImportJobStoreFactory $importJobStoreFactory
   *   Import jobstore factory.
   * @param \Drupal\dkan_datastore\Storage\DatabaseTableFactory $databaseTableFactory
   *   Database Table factory.
   */
  public function __construct(DataResource $resource, ImportJobStoreFactory $importJobStoreFactory, DatabaseTableFactory $databaseTableFactory) {
    parent::__construct($resource, $importJobStoreFactory, $databaseTableFactory);
    @trigger_error(__NAMESPACE__ . '\Import is deprecated. Use \Drupal\dkan_datastore\Service\ImportService instead.', E_USER_DEPRECATED);
  }

}
