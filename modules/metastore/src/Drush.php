<?php

namespace Drupal\metastore;

use Drupal\metastore\Storage\Data;
use Drush\Commands\DrushCommands;

/**
 * Metastore drush commands.
 */
class Drush extends DrushCommands {

  /**
   * Metastore data storage service.
   *
   * @var \Drupal\metastore\Storage\Data
   */
  protected $storage;

  /**
   * Drush constructor.
   *
   * @param \Drupal\metastore\Storage\Data $storage
   *   Metastore data storage.
   */
  public function __construct(Data $storage) {
    parent::__construct();
    $this->storage = $storage;
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
    // @todo: Remove once Storage\Data accept schema string as 3rd parameter.
    $this->storage->setSchema('dataset');

    try {
      $this->storage->publish($uuid);
      $this->logger()->success("Dataset {$uuid} published.");
    }
    catch (\Exception $e) {
      $this->logger()->error("Error while attempting to publish dataset {$uuid}: " . $e->getMessage());
    }
  }

}
