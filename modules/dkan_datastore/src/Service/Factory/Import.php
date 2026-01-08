<?php

namespace Drupal\dkan_datastore\Service\Factory;

use Drupal\dkan_datastore\Storage\DatabaseTableFactory;
use Drupal\dkan_datastore\Storage\ImportJobStoreFactory;

/**
 * Create an importer object for a given resource.
 *
 * @deprecated
 * @see \Drupal\dkan_datastore\Service\Factory\ImportServiceFactory
 */
class Import extends ImportServiceFactory {

  /**
   * Constructor.
   *
   * @param \Drupal\dkan_datastore\Storage\ImportJobStoreFactory $importJobStoreFactory
   *   Import job store factory service.
   * @param \Drupal\dkan_datastore\Storage\DatabaseTableFactory $databaseTableFactory
   *   Database table factory.
   */
  public function __construct(ImportJobStoreFactory $importJobStoreFactory, DatabaseTableFactory $databaseTableFactory) {
    parent::__construct($importJobStoreFactory, $databaseTableFactory);
    @trigger_error(__NAMESPACE__ . '\Import is deprecated. Use \Drupal\dkan_datastore\Service\Factory\ImportServiceFactory instead.', E_USER_DEPRECATED);
  }

}
