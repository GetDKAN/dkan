<?php

namespace Drupal\datastore\Storage;

use Contracts\FactoryInterface;
use Drupal\common\DataResource;
use Drupal\Core\Database\Connection;
use Psr\Log\LoggerInterface;

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
   */
  protected LoggerInterface $logger;

  /**
   * Constructor.
   */
  public function __construct(
    Connection $connection,
    LoggerInterface $loggerChannel,
  ) {
    $this->connection = $connection;
    $this->logger = $loggerChannel;
  }

  /**
   * Get a DatabaseTable instance.
   *
   * @param string $identifier
   *   Some way to discern between different instances of a class.
   * @param array $config
   *   Must contain a 'resource' key, which is a DataResource object.
   *
   * @return \Drupal\datastore\Storage\DatabaseTable
   *   A DatabaseTable object.
   */
  public function getInstance(string $identifier, array $config = []) {
    if (!isset($config['resource'])) {
      throw new \Exception("config['resource'] is required");
    }

    $resource = $config['resource'];

    return $this->getDatabaseTable($resource);
  }

  /**
   * Get a DatabaseTable object from a DataResource object.
   *
   * @param \Drupal\common\DataResource $resource
   *   A resource.
   *
   * @return \Drupal\datastore\Storage\DatabaseTable
   *   A DatabaseTable object.
   */
  protected function getDatabaseTable(DataResource $resource) {
    return new DatabaseTable($this->connection, $resource, $this->logger);
  }

}
