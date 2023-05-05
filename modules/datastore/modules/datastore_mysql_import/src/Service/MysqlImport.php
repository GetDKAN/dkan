<?php

namespace Drupal\datastore_mysql_import\Service;

/**
 * MySQL LOAD DATA importer.
 *
 * @deprecated
 * @see \Drupal\datastore_mysql_import\Service\MySqlImportJob
 */
class MysqlImport extends MySqlImportJob {

  protected function __construct(string $identifier, $storage, array $config = NULL) {
    parent::__construct($identifier, $storage, $config);
    @trigger_error(__NAMESPACE__ . '\MysqlImport is deprecated. Use \Drupal\datastore_mysql_import\Service\MySqlImportJob instead.', E_USER_DEPRECATED);
  }

}
