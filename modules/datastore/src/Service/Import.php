<?php

namespace Drupal\datastore\Service;

use Drupal\common\DataResource;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Storage\ImportJobStoreFactory;

/**
 * Datastore import service.
 *
 * @deprecated
 * @see \Drupal\datastore\Service\ImportService
 */
class Import extends ImportService {

  /**
   * Create a resource service instance.
   *
   * @param \Drupal\common\DataResource $resource
   *   DKAN Resource.
   * @param \Drupal\datastore\Storage\ImportJobStoreFactory $importJobStoreFactory
   *   Import jobstore factory.
   * @param \Drupal\datastore\Storage\DatabaseTableFactory $databaseTableFactory
   *   Database Table factory.
   */
  public function __construct(DataResource $resource, ImportJobStoreFactory $importJobStoreFactory, DatabaseTableFactory $databaseTableFactory) {
    parent::__construct($resource, $importJobStoreFactory, $databaseTableFactory);
    @trigger_error(__NAMESPACE__ . '\Import is deprecated. Use \Drupal\datastore\Service\ImportService instead.', E_USER_DEPRECATED);
  }

}
