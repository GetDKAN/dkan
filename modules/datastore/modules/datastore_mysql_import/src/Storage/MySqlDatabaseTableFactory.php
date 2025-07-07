<?php

namespace Drupal\datastore_mysql_import\Storage;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Database\Connection;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * MySQL import database table.
 */
class MySqlDatabaseTableFactory extends DatabaseTableFactory {

  /**
   * Config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $config;

  /**
   * Constructor.
   */
  public function __construct(
    Connection $connection,
    LoggerInterface $loggerChannel,
    EventDispatcherInterface $eventDispatcher,
    ConfigFactoryInterface $configFactory,
  ) {
    $this->connection = $connection;
    $this->logger = $loggerChannel;
    $this->eventDispatcher = $eventDispatcher;
    $this->config = $configFactory->get('datastore_mysql_import.settings');
  }

  /**
   * {@inheritDoc}
   */
  protected function getDatabaseTable($resource) {
    $table = new MySqlDatabaseTable(
      $this->connection,
      $resource,
      $this->logger,
      $this->eventDispatcher
    );
    $table->setStrictModeDisabled($this->config->get('strict_mode_disabled') ?? FALSE);
    return $table;
  }

}
