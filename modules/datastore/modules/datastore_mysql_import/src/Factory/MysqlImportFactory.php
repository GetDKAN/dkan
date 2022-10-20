<?php

namespace Drupal\datastore_mysql_import\Factory;

use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Service\Factory\ImportFactoryInterface;
use Drupal\datastore\Service\Import as Instance;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore_mysql_import\Service\MysqlImport;

/**
 * Importer factory.
 *
 * @codeCoverageIgnore
 */
class MysqlImportFactory implements ImportFactoryInterface {

  /**
   * The JobStore Factory service.
   *
   * @var \Drupal\common\Storage\JobStoreFactory
   */
  private $jobStoreFactory;

  /**
   * Database table factory service.
   *
   * @var \Drupal\datastore\Storage\DatabaseTableFactory
   */
  private $databaseTableFactory;

  /**
   * Services array. Not really needed, following FactoryInterface.
   *
   * @var array
   */
  private $services = [];

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

    if (!isset($config['resource'])) {
      throw new \Exception("config['resource'] is required");
    }

    $resource = $config['resource'];

    if (!isset($this->services[$identifier])) {
      $this->services[$identifier] = new Instance($resource, $this->jobStoreFactory, $this->databaseTableFactory);
    }

    $this->services[$identifier]->setImporterClass(MysqlImport::class);

    return $this->services[$identifier];
  }

}
