<?php


namespace Drupal\dkan_datastore;


class Util
{

  public static function getDatastoreManager($uuid) : IManager {
    $database = \Drupal::service('dkan_datastore.database');

    $dataset = \Drupal::entityManager()->loadEntityByUuid('node', $uuid);

    $metadata = json_decode($dataset->field_json_metadata->value);
    $resource = new Resource($dataset->id(), $metadata->distribution[0]->downloadURL);

    $provider = new \Dkan\Datastore\Manager\InfoProvider();
    $provider->addInfo(new \Dkan\Datastore\Manager\Info(SimpleImport::class, "simple_import", "SimpleImport"));

    $bin_storage = new \Dkan\Datastore\LockableBinStorage("dkan_datastore", new \Dkan\Datastore\Locker("dkan_datastore"), new \Drupal\dkan_datastore\Storage\Variable());
    $factory = new \Dkan\Datastore\Manager\Factory($resource, $provider, $bin_storage, $database);

    return  $factory->get();
  }

}