<?php

namespace Drupal\datastore\Service\Factory;

use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Storage\ImportJobStoreFactory;

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
  public function __construct(ImportJobStoreFactory $importJobStoreFactory, DatabaseTableFactory $databaseTableFactory) {
    parent::__construct($importJobStoreFactory, $databaseTableFactory);
    @trigger_error(__NAMESPACE__ . '\Import is deprecated. Use \Drupal\datastore\Service\Factory\ImportServiceFactory instead.', E_USER_DEPRECATED);
  }

}
