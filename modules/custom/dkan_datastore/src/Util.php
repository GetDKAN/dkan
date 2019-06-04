<?php

namespace Drupal\dkan_datastore;

use Dkan\Datastore\Manager\IManager;

/**
 * @codeCoverageIgnore
 */
class Util {

  /**
   * Instantiates a datastore manager.
   *
   * @param string $uuid
   *
   * @return \Dkan\Datastore\Manager\IManager
   *
   * @deprecated see dkan_datastore.manager.datastore_manager_builder service.
   */
  public static function getDatastoreManager(string $uuid) : IManager {

    /** @var Manager\DatastoreManagerBuilder $builder */
    $builder = \Drupal::service('dkan_datastore.manager.datastore_manager_builder');
    return $builder->buildFromUuid($uuid);
  }

}
