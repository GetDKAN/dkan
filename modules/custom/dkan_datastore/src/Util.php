<?php

namespace Drupal\dkan_datastore;

use Dkan\Datastore\Manager\IManager;
use Dkan\Datastore\Resource;
use Dkan\Datastore\Manager\InfoProvider;
use Dkan\Datastore\Manager\Info;
use Dkan\Datastore\Manager\SimpleImport\SimpleImport;
use Dkan\Datastore\LockableBinStorage;
use Dkan\Datastore\Manager\Factory;
use Dkan\Datastore\Locker;

/**
 *
 */
class Util {

  /**
   *
   */
  public static function getDatastoreManager($uuid) : IManager {
    $database = \Drupal::service('dkan_datastore.database');

    $dataset = \Drupal::entityManager()->loadEntityByUuid('node', $uuid);

    $metadata = json_decode($dataset->field_json_metadata->value);
    $resource = new Resource($dataset->id(), $metadata->distribution[0]->downloadURL);

    $provider = new InfoProvider();
    $provider->addInfo(new Info(SimpleImport::class, "simple_import", "SimpleImport"));

    $bin_storage = new LockableBinStorage("dkan_datastore", new Locker("dkan_datastore"), \Drupal::service('dkan_datastore.variable'));
    $factory = new Factory($resource, $provider, $bin_storage, $database);

    return $factory->get();
  }

}
