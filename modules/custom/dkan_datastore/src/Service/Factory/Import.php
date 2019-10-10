<?php

namespace Drupal\dkan_datastore\Service\Factory;

use Contracts\FactoryInterface;
use Dkan\Datastore\Resource;
use Drupal\dkan_datastore\Storage\DatabaseTableFactory;
use Drupal\dkan_datastore\Storage\JobStore;
use Drupal\dkan_datastore\Service\Import as Instance;

/**
 * Class Import.
 *
 * @codeCoverageIgnore
 */
class Import implements FactoryInterface {
  private $jobStore;
  private $databaseTableFactory;

  private $services = [];

  /**
   * Constructor.
   */
  public function __construct(JobStore $jobStore, DatabaseTableFactory $databaseTableFactory) {
    $this->jobStore = $jobStore;
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
      $this->services[$identifier] = new Instance($resource, $this->jobStore, $this->databaseTableFactory);
    }

    return $this->services[$identifier];
  }

}
