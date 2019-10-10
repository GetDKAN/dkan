<?php

namespace Drupal\dkan_datastore;

use Drupal\dkan_datastore\Storage\JobStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\dkan_datastore\Service\Factory\Resource;
use Drupal\dkan_datastore\Service\Factory\Import;
use Drupal\dkan_datastore\Service\ImporterList\ImporterList;
use Dkan\Datastore\Importer;
use FileFetcher\FileFetcher;

/**
 * Main services for the datastore.
 */
class Service implements ContainerInjectionInterface {

  private $resourceServiceFactory;
  private $importServiceFactory;
  private $queue;
  private $jobStore;

  /**
   * Inherited.
   *
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new Service(
      $container->get('dkan_datastore.service.factory.resource'),
      $container->get('dkan_datastore.service.factory.import'),
      $container->get('queue'),
      $container->get('dkan_datastore.job_store')
    );
  }

  /**
   * Constructor for datastore service.
   */
  public function __construct(Resource $resourceServiceFactory, Import $importServiceFactory, QueueFactory $queue, JobStore $jobStore) {
    $this->queue = $queue->get('dkan_datastore_import');
    $this->resourceServiceFactory = $resourceServiceFactory;
    $this->importServiceFactory = $importServiceFactory;
    $this->jobStore = $jobStore;
  }

  /**
   * Start import process for a resource, provided by UUID.
   *
   * @param string $uuid
   *   UUID for resource node.
   * @param bool $deferred
   *   Send to the queue for later? Will import immediately if FALSE.
   */
  public function import(string $uuid, bool $deferred = FALSE): array {

    $resourceService = $this->resourceServiceFactory->getInstance($uuid);
    $resource = $resourceService->get(TRUE);

    // If we passed $deferred, immediately add to the queue for later.
    if (!empty($deferred)) {
      $queueId = $this->queueImport($uuid);
      return [
        'message' => "Resource {$uuid} has been queued to be imported.",
        'queue_id' => $queueId,
      ];
    }

    if (!$resource) {
      $name = substr(strrchr(get_class($resourceService), "\\"), 1);
      return [$name => $resourceService->getResult()];
    }

    $importService = $this->importServiceFactory->getInstance(json_encode($resource));
    $importService->import();

    $rname = substr(strrchr(get_class($resourceService), "\\"), 1);
    $iname = substr(strrchr(get_class($importService), "\\"), 1);

    return [
      $rname => $resourceService->getResult(),
      $iname => $importService->getResult(),
    ];
  }

  /**
   * Drop all datastores for a given node.
   *
   * @param string $uuid
   *   UUID for resource or dataset node. If dataset, will drop datastore for
   *   all connected resources.
   */
  public function drop($uuid) {
    $this->getStorage($uuid)->destroy();
    $this->jobStore->remove($uuid, Importer::class);
    $this->jobStore->remove($uuid, FileFetcher::class);
  }

  /**
   * Queue a resource for import.
   *
   * @param string $uuid
   *   Resource node UUID.
   *
   * @return int
   *   Queue ID for new queued item.
   */
  private function queueImport($uuid) {
    // Attempt to fetch the file in a queue so as to not block user.
    $queueId = $this->queue->createItem(['uuid' => $uuid]);

    if ($queueId === FALSE) {
      throw new \RuntimeException("Failed to create file fetcher queue for {$uuid}");
    }

    return $queueId;
  }

  /**
   * Get a list of all stored importers and filefetchers, and their status.
   *
   * @return \Drupal\dkan_datastore\Service\ImporterList\ImporterList
   *   The importer list object.
   */
  public function list() {
    return ImporterList::getList($this->jobStore);
  }

  /**
   * Get Storage.
   *
   * @param string $uuid
   *   The unique identifier of a resource.
   */
  public function getStorage(string $uuid) {
    $resourceService = $this->resourceServiceFactory->getInstance($uuid);
    $resource = $resourceService->get();
    $importService = $this->importServiceFactory->getInstance(json_encode($resource));
    return $importService->getStorage();
  }

}
