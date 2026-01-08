<?php

namespace Drupal\dkan_datastore\Service\Factory;

use Drupal\dkan_datastore\Service\ImportService;
use Drupal\dkan_datastore\Storage\DatabaseTableFactory;
use Drupal\dkan_datastore\Storage\ImportJobStoreFactory;
use Drupal\dkan_metastore\Reference\ReferenceLookup;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Create an importer object for a given resource.
 */
class ImportServiceFactory implements ImportFactoryInterface {

  /**
   * Job store factory.
   */
  private ImportJobStoreFactory $importJobStoreFactory;

  /**
   * Database table factory.
   *
   * @var \Drupal\dkan_datastore\Storage\DatabaseTableFactory
   */
  private $databaseTableFactory;

  /**
   * DKAN logger channel service.
   */
  private LoggerInterface $logger;

  /**
   * Event dispatcher service.
   */
  private EventDispatcherInterface $eventDispatcher;

  /**
   * Reference lookup service.
   *
   * @var \Drupal\dkan_metastore\Reference\ReferenceLookup
   */
  protected $referenceLookup;

  /**
   * Constructor.
   */
  public function __construct(
    ImportJobStoreFactory $importJobStoreFactory,
    DatabaseTableFactory $databaseTableFactory,
    LoggerInterface $loggerChannel,
    EventDispatcherInterface $eventDispatcher,
    ReferenceLookup $referenceLookup,
  ) {
    $this->importJobStoreFactory = $importJobStoreFactory;
    $this->databaseTableFactory = $databaseTableFactory;
    $this->logger = $loggerChannel;
    $this->eventDispatcher = $eventDispatcher;
    $this->referenceLookup = $referenceLookup;
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
        $this->referenceLookup,
      );
    }
    throw new \Exception("config['resource'] is required");
  }

}
