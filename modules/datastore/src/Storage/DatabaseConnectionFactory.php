<?php

namespace Drupal\datastore\Storage;

use Drupal\common\Storage\DatabaseConnectionFactoryInterface;
use Drupal\common\Storage\DatabaseConnectionFactory as CommonDatabaseConnectionFactory;

/**
 * Database connection factory for connections with unbuffered queries.
 */
class DatabaseConnectionFactory extends CommonDatabaseConnectionFactory implements DatabaseConnectionFactoryInterface {

  /**
   * {@inheritdoc}
   */
  protected string $target = 'unbuffered_datastore';

  /**
   * {@inheritdoc}
   */
  protected function buildConnectionInfo(string $source_key = 'default', string $source_target = 'default'): array {
    $connection_info = parent::buildConnectionInfo($source_key, $source_target);
    // All our connections will be unbuffered.
    $connection_info['pdo'][\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = FALSE;
    return $connection_info;
  }

}
