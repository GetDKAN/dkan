<?php

namespace Drupal\datastore\Service;

use Drupal\common\DataResource;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Storage\DatabaseTableFactory;

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
   * @param \Drupal\common\Storage\JobStoreFactory $jobStoreFactory
   *   Jobstore factory.
   * @param \Drupal\datastore\Storage\DatabaseTableFactory $databaseTableFactory
   *   Database Table factory.
   */
  public function __construct(DataResource $resource, JobStoreFactory $jobStoreFactory, DatabaseTableFactory $databaseTableFactory) {
    parent::__construct($resource, $jobStoreFactory, $databaseTableFactory);
    @trigger_error(__NAMESPACE__ . '\Import is deprecated. Use \Drupal\datastore\Service\ImportService instead.', E_USER_DEPRECATED);
  }

}
