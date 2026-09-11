<?php

namespace Drupal\dkan_metastore\Drush\Commands;

use Drupal\dkan_metastore\Storage\DataFactory;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;

/**
 * Metastore drush commands.
 */
class MetastoreCommands extends DrushCommands {

  use AutowireTrait;

  public function __construct(protected DataFactory $factory) {
    parent::__construct();
  }

  /**
   * Publish the latest version of a dataset.
   */
  #[CLI\Command(name: 'dkan:metastore:publish', description: 'Publish the latest version of a dataset.')]
  #[CLI\Argument(name: 'uuid', description: 'Dataset identifier.')]
  #[CLI\Usage(name: 'dkan:metastore:publish cedcd327-4e5d-43f9-8eb1-c11850fa7c55', description: 'Publish the latest version of the dataset with the given UUID.')]
  public function publish(string $uuid) {
    try {
      $storage = $this->factory->getInstance('dataset');
      $storage->publish($uuid);
      $this->logger()->info("Dataset {$uuid} published.");
    }
    catch (\Exception $e) {
      $this->logger()->error("Error while attempting to publish dataset {$uuid}: " . $e->getMessage());
    }
  }

}
