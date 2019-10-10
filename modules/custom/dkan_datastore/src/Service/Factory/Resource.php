<?php

namespace Drupal\dkan_datastore\Service\Factory;

use Contracts\FactoryInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\File\FileSystem;
use Drupal\dkan_datastore\Service\Resource as Instance;
use Drupal\dkan_datastore\Storage\JobStore;

/**
 * Class Resource.
 *
 * @codeCoverageIgnore
 */
class Resource implements FactoryInterface {
  private $entityRepository;
  private $fileSystem;
  private $jobStore;

  /**
   * Constructor.
   */
  public function __construct(
    EntityRepository $entityRepository,
    FileSystem $fileSystem,
    JobStore $jobStore
  ) {
    $this->entityRepository = $entityRepository;
    $this->fileSystem = $fileSystem;
    $this->jobStore = $jobStore;
  }

  /**
   * Inherited.
   *
   * @inheritDoc
   */
  public function getInstance(string $identifier) {
    if (!isset($this->services[$identifier])) {
      $this->services[$identifier] = new Instance($identifier, $this->entityRepository, $this->fileSystem, $this->jobStore);
    }

    return $this->services[$identifier];
  }

}
