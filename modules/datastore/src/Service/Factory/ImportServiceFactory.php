<?php

namespace Drupal\datastore\Service\Factory;

use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Service\ImportService;
use Drupal\common\Storage\JobStoreFactory;

/**
 * Create an importer object for a given resource.
 */
class ImportServiceFactory implements ImportFactoryInterface {

  /**
   * Job store factory.
   *
   * @var \Drupal\common\Storage\JobStoreFactory
   */
  private $jobStoreFactory;

  /**
   * Database table factory.
   *
   * @var \Drupal\datastore\Storage\DatabaseTableFactory
   */
  private $databaseTableFactory;

  /**
   * Constructor.
   */
  public function __construct(JobStoreFactory $jobStoreFactory, DatabaseTableFactory $databaseTableFactory) {
    $this->jobStoreFactory = $jobStoreFactory;
    $this->databaseTableFactory = $databaseTableFactory;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getInstance(string $identifier, array $config = []) {
    if ($resource = $config['resource'] ?? FALSE) {
      return new ImportService($resource, $this->jobStoreFactory, $this->databaseTableFactory);
    }
    throw new \Exception("config['resource'] is required");
  }

}
