<?php

namespace Drupal\datastore_mysql_import\Factory;

use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Service\Factory\ImportFactoryInterface;
use Drupal\datastore\Service\ImportService;
use Drupal\datastore_mysql_import\Service\MysqlImport;
use Drupal\datastore_mysql_import\Storage\MySqlDatabaseTableFactory;

/**
 * Mysql importer factory.
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
   * @var \Drupal\datastore_mysql_import\Storage\MySqlDatabaseTableFactory
   */
  private $databaseTableFactory;

  /**
   * Constructor.
   */
  public function __construct(JobStoreFactory $jobStoreFactory, MySqlDatabaseTableFactory $databaseTableFactory) {
    $this->jobStoreFactory = $jobStoreFactory;
    $this->databaseTableFactory = $databaseTableFactory;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getInstance(string $identifier, array $config = []) {
    $resource = $config['resource'] ?? FALSE;
    if (!$resource) {
      throw new \Exception("config['resource'] is required");
    }

    $importer = new ImportService($resource, $this->jobStoreFactory, $this->databaseTableFactory);
    $importer->setImporterClass(MysqlImport::class);
    return $importer;
  }

}
