<?php

namespace Drupal\datastore\Service\Factory;

use Contracts\FactoryInterface;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Service\Import as Instance;
use Drupal\datastore_fast_import\Service\FastImporter;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\Core\Extension\ModuleHandler;

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
      if ($this->moduleHandler->moduleExists('datastore_fast_import')) {
        $importer = new Instance($resource, $this->jobStoreFactory, $this->databaseTableFactory);
        $importer->setImporterClass(FastImporter::class);
        $this->services[$identifier] = $importer;
      }
      else {
        $this->services[$identifier] = new Instance($resource, $this->jobStoreFactory, $this->databaseTableFactory);
      }
    }

    return $this->services[$identifier];
  }

}
