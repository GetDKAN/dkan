<?php

namespace Drupal\datastore\Service\Factory;

use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\common\Storage\JobStoreFactory;

/**
 * Create an importer object for a given resource.
 *
 * @deprecated
 * @see \Drupal\datastore\Service\Factory\ImportServiceFactory
 */
class Import extends ImportServiceFactory {

  /**
   * Constructor.
   */
  public function __construct(JobStoreFactory $jobStoreFactory, DatabaseTableFactory $databaseTableFactory) {
    parent::__construct($jobStoreFactory, $databaseTableFactory);
    @trigger_error(__NAMESPACE__ . '\Import is deprecated. Use \Drupal\datastore\Service\Factory\ImportServiceFactory instead.', E_USER_DEPRECATED);
  }

}
