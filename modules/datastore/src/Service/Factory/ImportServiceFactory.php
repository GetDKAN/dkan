<?php

namespace Drupal\datastore\Service\Factory;

use Drupal\datastore\Service\ImportService;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Storage\ImportJobStoreFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Create an importer object for a given resource.
 */
class ImportServiceFactory implements ImportFactoryInterface {

  /**
   * Job store factory.
   *
   * @var \Drupal\datastore\Storage\ImportJobStoreFactory
   */
  private ImportJobStoreFactory $importJobStoreFactory;

  /**
   * Database table factory.
   *
   * @var \Drupal\datastore\Storage\DatabaseTableFactory
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
    DatabaseTableFactory $databaseTableFactory,
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
    if ($resource = $config['resource'] ?? FALSE) {
      return new ImportService(
        $resource,
        $this->importJobStoreFactory,
        $this->databaseTableFactory,
        $this->logger,
        $this->eventDispatcher,
      );
    }
    throw new \Exception("config['resource'] is required");
  }

}
