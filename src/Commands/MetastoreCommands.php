<?php

namespace Drupal\dkan\Commands;

use Drupal\dkan\Storage\DataFactory;
use Drush\Commands\DrushCommands;

/**
 * Metastore drush commands.
 */
class MetastoreCommands extends DrushCommands {

  /**
   * Metastore data storage service.
   *
   * @var \Drupal\dkan\Storage\DataFactory
   */
  protected $factory;

  /**
   * Drush constructor.
   *
   * @param \Drupal\dkan\Storage\DataFactory $factory
   *   A data factory.
   */
  public function __construct(DataFactory $factory) {
    parent::__construct();
    $this->factory = $factory;
  }

  /**
   * Publish the latest version of a dataset.
   *
   * @param string $uuid
   *   Dataset identifier.
   *
   * @command dkan:metastore:publish
   */
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
