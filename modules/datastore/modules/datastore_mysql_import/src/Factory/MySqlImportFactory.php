<?php

namespace Drupal\datastore_mysql_import\Factory;

use Drupal\datastore\Service\Factory\Import;
use Drupal\datastore_mysql_import\Service\MySqlImportJob;

/**
 * Importer factory.
 */
class MySqlImportFactory extends Import {

  /**
   * {@inheritDoc}
   */
  public function getInstance(string $identifier, array $config = []) {
    /** @var \Drupal\datastore\Service\Import $instance */
    $instance = parent::getInstance($identifier, $config);
    $instance->setImporterClass(MySqlImportJob::class);
    return $instance;
  }

}
