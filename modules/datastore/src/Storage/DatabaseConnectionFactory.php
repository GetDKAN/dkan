<?php

namespace Drupal\datastore\Storage;

use Drupal\Core\Database\Connection;

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

    $connection_info['init_commands'] ??= [];
    $connection_info['init_commands']['sql_mode'] ??= '';

    return $connection_info;
  }

  /**
   * Prepare database connection.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   */
  protected function prepareConnection(Connection $connection): void {
    parent::prepareConnection($connection);
    // Add sql mode option, "ALLOW_INVALID_DATES".
    $sql_mode = $connection->query('SELECT @@sql_mode')->fetchField();
    $sql_mode_cmd = $this->buildSqlModeCommand($sql_mode, 'ALLOW_INVALID_DATES');
    if (!empty($sql_mode_cmd)) {
      $connection->query($sql_mode_cmd);
    }
  }

  /**
   * Add option to 'sql_mode' setting initialization command.
   *
   * @param string $sql_mode
   *   The current 'sql_mode' setting.
   * @param string $option
   *   Option to add.
   *
   * @return string
   *   The revised 'sql_mode' setting initialization command.
   */
  protected function buildSqlModeCommand(string $sql_mode, string $option): string {
    $sql_mode_cmd = '';

    if (strpos($sql_mode, $option) === FALSE) {
      $options = ltrim($sql_mode . ',' . $option, ',');
      $sql_mode_cmd = 'SET SESSION sql_mode = "' . $options . '"';
    }

    return $sql_mode_cmd;
  }

}
