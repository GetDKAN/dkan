<?php

namespace Drupal\dkan_datastore\Service\Factory;

use Contracts\FactoryInterface;
use Dkan\Datastore\Resource;
use Drupal\dkan_datastore\Storage\DatabaseTableFactory;
use Drupal\dkan_datastore\Service\Import as Instance;
use Drupal\dkan_datastore\Storage\JobStoreFactory;

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
  public function getInstance(string $identifier) {
    if (!isset($this->services[$identifier])) {
      $resource = Resource::hydrate($identifier);
      $this->services[$identifier] = new Instance($resource, $this->jobStoreFactory, $this->databaseTableFactory);
    }

    return $this->services[$identifier];
  }

}
