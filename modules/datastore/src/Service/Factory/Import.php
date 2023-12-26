<?php

namespace Drupal\datastore\Service\Factory;

use Drupal\datastore\Storage\DatabaseTableFactory;
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
   *
   * @param \Drupal\datastore\Storage\ImportJobStoreFactory $importJobStoreFactory
   *   Import job store factory service.
   * @param \Drupal\datastore\Storage\DatabaseTableFactory $databaseTableFactory
   *   Database table factory.
   */
  public function __construct(ImportJobStoreFactory $importJobStoreFactory, DatabaseTableFactory $databaseTableFactory) {
    parent::__construct($importJobStoreFactory, $databaseTableFactory);
    @trigger_error(__NAMESPACE__ . '\Import is deprecated. Use \Drupal\datastore\Service\Factory\ImportServiceFactory instead.', E_USER_DEPRECATED);
  }

}
