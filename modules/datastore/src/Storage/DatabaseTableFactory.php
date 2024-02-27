<?php

namespace Drupal\datastore\Storage;

use Contracts\FactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelInterface;

/**
 * DatabaseTable data object factory.
 */
class DatabaseTableFactory implements FactoryInterface {

  /**
   * Drupal database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * DKAN logger channel service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private LoggerChannelInterface $logger;

  /**
   * Constructor.
   */
  public function __construct(
    Connection $connection,
    LoggerChannelInterface $loggerChannel
  ) {
    $this->connection = $connection;
    $this->logger = $loggerChannel;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getInstance(string $identifier, array $config = []) {
    if (!isset($config['resource'])) {
      throw new \Exception("config['resource'] is required");
    }

    $resource = $config['resource'];
    $databaseTable = $this->getDatabaseTable($resource);

    return $databaseTable;
  }

  /**
   * Protected.
   */
  protected function getDatabaseTable($resource) {
    return new DatabaseTable($this->connection, $resource, $this->logger);
  }

}
