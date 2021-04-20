<?php

namespace Drupal\Tests\datastore\Traits;

use MockChain\Options;
use Drupal\Core\Config\ConfigFactory;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Service\Factory\Resource as ResourceServiceFactory;
use Drupal\Core\Database\Connection;

/**
 *
 */
trait TestHelperTrait {

  /**
   * Private.
   */
  private function getServices() {
    return (new Options())
      ->add('config.factory', ConfigFactory::class)
      ->add('dkan.datastore.database_table_factory', DatabaseTableFactory::class)
      ->add('dkan.datastore.service.factory.resource', ResourceServiceFactory::class)
      ->add('database', Connection::class)
      ->index(0);
  }

}
