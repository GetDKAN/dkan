<?php

namespace Drupal\datastore\Service\Factory;

use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Service\ImportService;
use Drupal\datastore\Storage\ImportJobStoreFactory;

/**
 * Create an importer object for a given resource.
 */
class ImportServiceFactory implements ImportFactoryInterface {

  /**
   * Job store factory.
   *
   * @var \Drupal\datastore\Storage\ImportJobStoreFactory
   */
  private ImportJobStoreFactory $importJobStoreFactory;

  /**
   * Database table factory.
   *
   * @var \Drupal\datastore\Storage\DatabaseTableFactory
   */
  private $databaseTableFactory;

  /**
   * Import services.
   *
   * @var \Drupal\datastore\Service\ImportService[]
   */
  private $services = [];

  /**
   * Constructor.
   */
  public function __construct(ImportJobStoreFactory $importJobStoreFactory, DatabaseTableFactory $databaseTableFactory) {
    $this->importJobStoreFactory = $importJobStoreFactory;
    $this->databaseTableFactory = $databaseTableFactory;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getInstance(string $identifier, array $config = []) {

    if (!isset($config['resource'])) {
      throw new \Exception("config['resource'] is required");
    }

    $resource = $config['resource'];
    return new ImportService($resource, $this->importJobStoreFactory, $this->databaseTableFactory);
  }

}
