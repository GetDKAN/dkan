<?php

namespace Drupal\datastore_mysql_import\Factory;

use Drupal\datastore\Service\Factory\ImportFactoryInterface;
use Drupal\datastore\Service\ImportService;
use Drupal\datastore\Storage\ImportJobStoreFactory;
use Drupal\datastore_mysql_import\Service\MysqlImport;
use Drupal\datastore_mysql_import\Storage\MySqlDatabaseTableFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Mysql importer factory.
 */
class MysqlImportFactory implements ImportFactoryInterface {

  /**
   * The JobStore Factory service.
   *
   * @var \Drupal\datastore\Storage\ImportJobStoreFactory
   */
  private ImportJobStoreFactory $importJobStoreFactory;

  /**
   * Database table factory service.
   *
   * @var \Drupal\datastore_mysql_import\Storage\MySqlDatabaseTableFactory
   */
  private $databaseTableFactory;

  /**
   * DKAN logger channel service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private LoggerInterface $logger;

  /**
   * Event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private EventDispatcherInterface $eventDispatcher;

  /**
   * Constructor.
   */
  public function __construct(
    ImportJobStoreFactory $importJobStoreFactory,
    MySqlDatabaseTableFactory $databaseTableFactory,
    LoggerInterface $loggerChannel,
    EventDispatcherInterface $eventDispatcher,
  ) {
    $this->importJobStoreFactory = $importJobStoreFactory;
    $this->databaseTableFactory = $databaseTableFactory;
    $this->logger = $loggerChannel;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * {@inheritDoc}
   */
  public function getInstance(string $identifier, array $config = []) {
    $resource = $config['resource'] ?? FALSE;
    if (!$resource) {
      throw new \Exception("config['resource'] is required");
    }

    $importer = new ImportService(
      $resource,
      $this->importJobStoreFactory,
      $this->databaseTableFactory,
      $this->logger,
      $this->eventDispatcher
    );
    $importer->setImporterClass(MysqlImport::class);
    return $importer;
  }

}
