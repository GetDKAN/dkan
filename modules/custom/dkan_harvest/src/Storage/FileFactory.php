<?php

namespace Drupal\dkan_harvest\Storage;

use Contracts\FactoryInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 * FileFactory.
 */
class FileFactory implements FactoryInterface {

  private $stores = [];
  private $fileSystem;

  /**
   * Constructor.
   */
  public function __construct(FileSystemInterface $fileSystem) {
    $this->fileSystem = $fileSystem;
  }

  /**
   * Inherited.
   *
   * {@inheritDoc}
   */
  public function getInstance(string $identifier) {
    if (!isset($this->stores[$identifier])) {
      $public_directory = $this->fileSystem->realpath("public://");
      $harvest_config_directory = $public_directory . "/dkan_harvest";
      $this->stores[$identifier] = $this->getFileStorage($harvest_config_directory);
    }
    return $this->stores[$identifier];
  }

  /**
   * Private.
   *
   * Set to protected for testing and mocking.
   *
   * @todo make private again.
   */
  protected function getFileStorage($directory) {
    return new File($directory);
  }

}
