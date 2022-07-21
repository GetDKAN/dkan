<?php

namespace Drupal\datastore;

use Dkan\Datastore\Importer;
use Drupal\common\Resource;
use Drupal\common\Storage\JobStoreFactory;
use Procrastinator\Result;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\datastore\Service\Factory\ImportFactoryInterface;
use Drupal\datastore\Service\Info\ImportInfoList;
use FileFetcher\FileFetcher;

/**
 * Main services for the datastore.
 */
class Service implements ContainerInjectionInterface {

  /**
   * Resource localizer for handling remote resource URLs.
   *
   * @var \Drupal\datastore\Service\ResourceLocalizer
   */
  private $resourceLocalizer;

  /**
   * Datastore import factory class.
   *
   * @var \Drupal\datastore\Service\Factory\ImportFactoryInterface
   */
  private $importServiceFactory;

  /**
   * Drupal queue.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  private $queue;

  /**
   * JobStore factory object.
   *
   * @var \Drupal\common\Storage\JobStoreFactory
   */
  private $jobStoreFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new Service(
      $container->get('dkan.datastore.service.resource_localizer'),
      $container->get('dkan.datastore.service.factory.import'),
      $container->get('queue'),
      $container->get('dkan.common.job_store'),
      $container->get('dkan.datastore.import_info_list'),
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\datastore\Service\ResourceLocalizer $resourceLocalizer
   *   Resource localizer service.
   * @param \Drupal\datastore\Service\Factory\ImportFactoryInterface $importServiceFactory
   *   Import factory service.
   * @param \Drupal\Core\Queue\QueueFactory $queue
   *   Queue factory service.
   * @param \Drupal\common\Storage\JobStoreFactory $jobStoreFactory
   *   Jobstore factory service.
   * @param \Drupal\datastore\Service\Info\ImportInfoList $importInfoList
   *   Import info list service.
   */
  public function __construct(
    ResourceLocalizer $resourceLocalizer,
    ImportFactoryInterface $importServiceFactory,
    QueueFactory $queue,
    JobStoreFactory $jobStoreFactory,
    ImportInfoList $importInfoList
  ) {
    $this->queue = $queue;
    $this->resourceLocalizer = $resourceLocalizer;
    $this->importServiceFactory = $importServiceFactory;
    $this->jobStoreFactory = $jobStoreFactory;
    $this->importInfoList = $importInfoList;
  }

  /**
   * Start import process for a resource, provided by UUID.
   *
   * @param string $identifier
   *   A resource identifier.
   * @param bool $deferred
   *   Send to the queue for later? Will import immediately if FALSE..
   * @param string|null $version
   *   A resource's version.
   *
   * @return array
   *   Response.
   */
  public function import(string $identifier, bool $deferred = FALSE, $version = NULL): array {

    // If we passed $deferred, immediately add to the queue for later.
    if ($deferred == TRUE) {
      // Attempt to fetch the file in a queue so as to not block user.
      $queueId = $this->queue->get('datastore_import')
        ->createItem(['identifier' => $identifier, 'version' => $version]);

      if ($queueId === FALSE) {
        throw new \RuntimeException("Failed to create file fetcher queue for {$identifier}:{$version}");
      }

      return [
        'message' => "Resource {$identifier}:{$version} has been queued to be imported.",
      ];
    }

    $resource = NULL;
    $result = NULL;
    list($resource, $result) = $this->getResource($identifier, $version);

    if (!$resource) {
      return $result;
    }

    $result2 = $this->doImport($resource);

    return array_merge($result, $result2);
  }

  /**
   * Private.
   */
  private function doImport($resource) {
    $importService = $this->getImportService($resource);
    $importService->import();
    return [$this->getLabelFromObject($importService) => $importService->getResult()];
  }

  /**
   * Private.
   */
  private function getLabelFromObject($object) {
    return substr(strrchr(get_class($object), "\\"), 1);
  }

  /**
   * Private.
   */
  private function getResource($identifier, $version) {
    $label = $this->getLabelFromObject($this->resourceLocalizer);
    $resource = $this->resourceLocalizer->get($identifier, $version);

    if ($resource) {
      $result = [
        $label => $this->resourceLocalizer->getResult($identifier, $version),
      ];
      return [$resource, $result];
    }

    // @todo we should not do this, we need a filefetcher queue worker.
    $result = [
      $label => $this->resourceLocalizer->localize($identifier, $version),
    ];

    if (isset($result[$label]) && $result[$label]->getStatus() == Result::DONE) {
      $resource = $this->resourceLocalizer->get($identifier, $version);
    }

    return [$resource, $result];
  }

  /**
   * Getter.
   */
  public function getImportService(Resource $resource) {
    return $this->importServiceFactory->getInstance($resource->getUniqueIdentifier(), ['resource' => $resource]);
  }

  /**
   * Drop a resources datastore.
   *
   * @param string $identifier
   *   A resource's identifier.
   * @param string|null $version
   *   A resource's version.
   * @param bool $local_resource
   *   Whether to remove the local resource. If false, just drop the db table.
   */
  public function drop(string $identifier, ?string $version = NULL, bool $local_resource = TRUE) {
    $storage = $this->getStorage($identifier, $version);
    $resource_id = $this->resourceLocalizer->get($identifier, $version)->getUniqueIdentifier();

    if ($storage) {
      $storage->destruct();
      $this->jobStoreFactory
        ->getInstance(Importer::class)
        ->remove(md5($resource_id));
    }

    if ($local_resource) {
      $this->resourceLocalizer->remove($identifier, $version);
      $this->jobStoreFactory
        ->getInstance(FileFetcher::class)
        ->remove(substr(str_replace('__', '_', $resource_id), 0, -11));
    }
  }

  /**
   * Get a list of all stored importers and filefetchers, and their status.
   *
   * @return array
   *   The importer list object.
   */
  public function list() {
    return $this->importInfoList->buildList();
  }

  /**
   * Summary.
   */
  public function summary($identifier) {
    $id = NULL;
    $version = NULL;
    [$id, $version] = Resource::getIdentifierAndVersion($identifier);
    $storage = $this->getStorage($id, $version);

    if ($storage) {
      $data = $storage->getSummary();
      return $data;
    }
    throw new \Exception("no storage");
  }

  /**
   * Get Storage.
   *
   * @param string $identifier
   *   The unique identifier of a resource.
   * @param string|null $version
   *   The version of the resource.
   *
   * @return \Drupal\datastore\Storage\DatabaseTable
   *   Storage object.
   *
   * @throws \Exception
   */
  public function getStorage(string $identifier, $version = NULL) {
    $resource = $this->resourceLocalizer->get($identifier, $version);
    if ($resource) {
      $importService = $this->getImportService($resource);
      return $importService->getStorage();
    }
    throw new \InvalidArgumentException("No datastore storage found for {$identifier}:{$version}.");
  }

  /**
   * Return the resource localizer.
   *
   * @return \Drupal\datastore\Service\ResourceLocalizer
   *   Resource localizer.
   */
  public function getResourceLocalizer() : ResourceLocalizer {
    return $this->resourceLocalizer;
  }

  /**
   * Return the queue factory.
   *
   * @return \Drupal\Core\Queue\QueueFactory
   *   Queue factory.
   */
  public function getQueueFactory(): QueueFactory {
    return $this->queue;
  }

}
