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
    $this->updateSqlMode($connection);
  }

  /**
   * Update the SQL_Mode session setting on the provided connection.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Connection to update the SQL Mode setting on.
   */
  protected function updateSqlMode(Connection $connection): void {
    // Add sql mode option, "ALLOW_INVALID_DATES", and remove sql mode options
    // "NO_ZERO_DATE" and "NO_ZERO_IN_DATE".
    $sql_mode_options = $connection->query('SELECT @@sql_mode')->fetchField();
    $sql_mode_cmd = $this->buildSqlModeCommand(
      explode(',', $sql_mode_options),
      ['ALLOW_INVALID_DATES'],
      ['NO_ZERO_DATE', 'NO_ZERO_IN_DATE']
    );
    $connection->query($sql_mode_cmd);

    $sql_mode = $connection->query('SELECT @@sql_mode')->fetchField();
    $strict_mode = $connection->query('SELECT @@innodb_strict_mode')->fetchField();
    \Drupal::logger('datastore')->notice('a ' . $sql_mode . ' ' . $strict_mode);
  }

  /**
   * Build 'sql_mode' setting initialization command.
   *
   * @param string[] $existing_options
   *   The current 'sql_mode' setting.
   * @param string[] $new_options
   *   Options to add.
   * @param string[] $options_to_exclude
   *   Options to exclude from the generated command.
   *
   * @return string
   *   A revised 'sql_mode' setting initialization command.
   */
  protected function buildSqlModeCommand(array $existing_options, array $new_options, array $options_to_exclude): string {
    // Remove the excluded options.
    $generated_options = array_diff($existing_options, $options_to_exclude);
    // Add the new options.
    $generated_options = array_merge($generated_options, $new_options);
    // Ensure all options are unique.
    $generated_options = array_unique($generated_options);

    // Return the generated command.
    return 'SET SESSION sql_mode = "' . implode(',', $generated_options) . '"';
  }

}
