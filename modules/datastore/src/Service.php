<?php

namespace Drupal\datastore;

use Drupal\datastore\Storage\JobStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\datastore\Service\Factory\Resource;
use Drupal\datastore\Service\Factory\Import;
use Drupal\datastore\Service\ImporterList\ImporterList;
use Dkan\Datastore\Importer;

/**
 * Main services for the datastore.
 */
class Service implements ContainerInjectionInterface {

  private $resourceServiceFactory;
  private $importServiceFactory;
  private $queue;
  private $jobStoreFactory;

  /**
   * Inherited.
   *
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new Service(
      $container->get('datastore.service.factory.resource'),
      $container->get('datastore.service.factory.import'),
      $container->get('queue'),
      $container->get('datastore.job_store_factory')
    );
  }

  /**
   * Constructor for datastore service.
   */
  public function __construct(Resource $resourceServiceFactory, Import $importServiceFactory, QueueFactory $queue, JobStoreFactory $jobStoreFactory) {
    $this->queue = $queue->get('datastore_import');
    $this->resourceServiceFactory = $resourceServiceFactory;
    $this->importServiceFactory = $importServiceFactory;
    $this->jobStoreFactory = $jobStoreFactory;
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

    // If we passed $deferred, immediately add to the queue for later.
    if (!empty($deferred)) {
      // This creates a filefetcher job.
      $resourceService->get(TRUE, FALSE);
      $queueId = $this->queueImport($uuid);
      return [
        'message' => "Resource {$uuid} has been queued to be imported.",
        'queue_id' => $queueId,
      ];
    }

    /* @var $resource \Dkan\Datastore\Resource */
    $resource = $resourceService->get(TRUE);
    if (!$resource) {
      $name = substr(strrchr(get_class($resourceService), "\\"), 1);
      return [$name => $resourceService->getResult()];
    }

    $importService = $this->importServiceFactory->getInstance($resource->getId(), ['resource' => $resource]);
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
    $storage = $this->getStorage($uuid);
    if ($storage) {
      $storage->destroy();
    }

    /* @var $resourceService \Drupal\datastore\Service\Resource */
    $resourceService = $this->resourceServiceFactory->getInstance($uuid);
    $resourceService->remove();

    /* @var $resource \Dkan\Datastore\Resource */
    $resource = $resourceService->get();
    $this->jobStoreFactory->getInstance(Importer::class)->remove($resource->getId());

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
   * @return \Drupal\datastore\Service\ImporterList\ImporterList
   *   The importer list object.
   */
  public function list() {
    return ImporterList::getList($this->jobStoreFactory, $this->resourceServiceFactory, $this->importServiceFactory);
  }

  /**
   * Get Storage.
   *
   * @param string $uuid
   *   The unique identifier of a resource.
   */
  public function getStorage(string $uuid) {
    $resourceService = $this->resourceServiceFactory->getInstance($uuid);

    /* @var $resource \Dkan\Datastore\Resource */
    $resource = $resourceService->get();
    if ($resource) {
      $importService = $this->importServiceFactory->getInstance($resource->getId(),
        ['resource' => $resource]);
      return $importService->getStorage();
    }
    return NULL;
  }

}
