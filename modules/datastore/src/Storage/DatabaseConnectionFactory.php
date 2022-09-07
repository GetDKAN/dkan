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

    $connection_info['init_commands'] ??= [];
    $connection_info['init_commands']['sql_mode'] ??= '';
    $this->addSqlModeOption($connection_info['init_commands']['sql_mode'], 'ALLOW_INVALID_DATES');

    return $connection_info;
  }

  /**
   * Add option to 'sql_mode' setting initialization command.
   *
   * @param string $sql_mode
   *   'sql_mode' setting initialization command.
   * @param string $option
   *   Option to add.
   */
  protected function addSqlModeOption(string &$sql_mode, string $option): void {
    $matches = [];
    preg_match("/^SET sql_mode = '(?P<args>.+?)'$/", $sql_mode, $matches);

    if (isset($matches['args']) && strpos($matches['args'], $option) === FALSE) {
      $options = $matches['args'] . ',' . $option;
      $sql_mode = "SET sql_mode = '" . $options . "'";
    }
  }

}
