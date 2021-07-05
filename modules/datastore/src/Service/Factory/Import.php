<?php

namespace Drupal\datastore\Service\Factory;

use Contracts\FactoryInterface;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Service\Import as Instance;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\datastore_mysql_import\Service\MysqlImport;

/**
 * Class Import.
 *
 * @codeCoverageIgnore
 */
class Import implements FactoryInterface {
  private $jobStoreFactory;
  private $databaseTableFactory;
  private $moduleHandler;

  private $services = [];

  /**
   * Constructor.
   */
  public function __construct(JobStoreFactory $jobStoreFactory, DatabaseTableFactory $databaseTableFactory, ModuleHandler $moduleHandler) {
    $this->jobStoreFactory = $jobStoreFactory;
    $this->databaseTableFactory = $databaseTableFactory;
    $this->moduleHandler = $moduleHandler;
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
      if ($this->moduleHandler->moduleExists('datastore_mysql_import')) {
        $importer = new Instance($resource, $this->jobStoreFactory, $this->databaseTableFactory);
        $importer->setImporterClass(MysqlImport::class);
        $this->services[$identifier] = $importer;
      }
      else {
        $this->services[$identifier] = new Instance($resource, $this->jobStoreFactory, $this->databaseTableFactory);
      }
    }

    return $this->services[$identifier];
  }

}
