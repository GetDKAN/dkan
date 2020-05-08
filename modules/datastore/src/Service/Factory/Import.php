<?php

namespace Drupal\datastore\Service\Factory;

use Contracts\FactoryInterface;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Service\Import as Instance;
use Drupal\datastore\Storage\JobStoreFactory;

/**
 * Class Import.
 *
 * @codeCoverageIgnore
 */
class Import implements FactoryInterface {
  private $jobStoreFactory;
  private $databaseTableFactory;

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
   * @inheritDoc
   */
  public function getInstance(string $identifier, array $config = []) {

    if (!isset($config['resource'])) {
      throw new \Exception("config['resource'] is required");
    }

    $resource = $config['resource'];

    if (!isset($this->services[$identifier])) {
      $this->services[$identifier] = new Instance($resource, $this->jobStoreFactory, $this->databaseTableFactory);
    }

    return $this->services[$identifier];
  }

}
