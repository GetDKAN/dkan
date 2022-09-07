<?php

namespace Drupal\datastore\Storage;

use Drupal\common\Storage\DatabaseConnectionFactoryInterface;
use Drupal\common\Storage\DatabaseConnectionFactory as DatabaseConnectionFactoryBase;

/**
 * Create separate datastore connection at runtime with unbuffered queries.
 *
 * @return \Drupal\Core\Database\Connection
 *   New datastore connection object.
 */
class DatabaseConnectionFactory extends DatabaseConnectionFactoryBase implements DatabaseConnectionFactoryInterface {

  /**
   * {@inheritdoc}
   */
  protected string $target = 'default';

  /**
   * {@inheritdoc}
   */
  protected string $key = 'datastore';

  /**
   * {@inheritdoc}
   */
  protected function buildConnectionInfo(): array {
    $connection_info = parent::buildConnectionInfo();
    $connection_info['pdo'][\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = FALSE;
    if (strpos($connection_info['init_commands']['sql_mode'], 'ALLOW_INVALID_DATES') === FALSE) {
      $connection_info['init_commands']['sql_mode'] .= ',ALLOW_INVALID_DATES';
    }
    return $connection_info;
  }

}
