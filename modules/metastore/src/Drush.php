<?php

namespace Drupal\metastore;

use Drupal\metastore\Storage\Data;
use Drush\Commands\DrushCommands;

/**
 * Metastore drush commands.
 *
 * @codeCoverageIgnore
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
    // @todo: Possibly remove once \Drupal\metastore\Storage\Data is passed
    //   a third parameter, a string for schema.
    $this->storage->setSchema('dataset');

    try {
      $this->storage->publish($uuid);
      $this->logger()->success(dt("Dataset {$uuid} published."));
    }
    catch (\Exception $e) {
      $this->logger()->error(dt("Error while attempting to publish dataset {$uuid}: " . $e->getMessage()));
    }
  }

}
