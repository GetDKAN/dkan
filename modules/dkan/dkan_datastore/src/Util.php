<?php

namespace Drupal\Dkan\Datastore;

use Dkan\Datastore\Resource;
use Drupal\Dkan\Datastore\Storage\Database;
use Drupal\Dkan\Datastore\Storage\Variable;
use Dkan\Datastore\Manager\InfoProvider;
use Dkan\Datastore\Manager\Info;
use Dkan\Datastore\Manager\SimpleImport\SimpleImport;
use Dkan\Datastore\LockableBinStorage;
use Dkan\Datastore\Manager\Factory;
use Dkan\Datastore\Locker;

class Util
{
  public static function getManager(Resource $resource) {
    $provider = new InfoProvider();
    $provider->addInfo(new Info(SimpleImport::class, "simple_import", "SimpleImport"));

    $bin_storage = self::getBinStorage();

    $database = new Database();

    return (new Factory($resource, $provider, $bin_storage, $database))->get();
  }

  public static function getBinStorage() {
    return new LockableBinStorage("dkan_datastore", new Locker("dkan_datastore"), new Variable());
  }
}