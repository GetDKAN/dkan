<?php

namespace Drupal\datastore\Service\Factory;

use Contracts\FactoryInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\File\FileSystem;
use Drupal\datastore\Service\Resource as Instance;
use Drupal\datastore\Storage\JobStoreFactory;

/**
 * Class Resource.
 *
 * @codeCoverageIgnore
 */
class Resource implements FactoryInterface {
  private $entityRepository;
  private $fileSystem;
  private $jobStoreFactory;

  /**
   * Constructor.
   */
  public function __construct(
    EntityRepository $entityRepository,
    FileSystem $fileSystem,
    JobStoreFactory $jobStoreFactory
  ) {
    $this->entityRepository = $entityRepository;
    $this->fileSystem = $fileSystem;
    $this->jobStoreFactory = $jobStoreFactory;
  }

  /**
   * Inherited.
   *
   * @inheritDoc
   */
  public function getInstance(string $identifier, array $config = []) {
    if (!isset($this->services[$identifier])) {
      $this->services[$identifier] = new Instance($identifier, $this->entityRepository, $this->fileSystem, $this->jobStoreFactory);
    }

    return $this->services[$identifier];
  }

}
