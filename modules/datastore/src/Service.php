<?php

namespace Drupal\datastore;

use Drupal\common\Resource;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Service\DatastoreQuery;
use Procrastinator\Result;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\datastore\Service\Factory\Import;
use Drupal\datastore\Service\ImporterList\ImporterList;
use Drupal\common\Storage\Query;
use stdClass;

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
   * @var \Drupal\datastore\Service\Factory\Import
   */
  private $importServiceFactory;

  /**
   * Drupal queue.
   *
   * @var \Drupal\Core\Queue\QueueInterface
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
      $container->get('dkan.common.job_store')
    );
  }

  /**
   * Constructor for datastore service.
   */
  public function __construct(ResourceLocalizer $resourceLocalizer, Import $importServiceFactory, QueueFactory $queueFactory, JobStoreFactory $jobStoreFactory) {
    $this->queue = $queueFactory->get('datastore_import');
    $this->resourceLocalizer = $resourceLocalizer;
    $this->importServiceFactory = $importServiceFactory;
    $this->jobStoreFactory = $jobStoreFactory;
  }

  /**
   * Start import process for a resource, provided by UUID.
   *
   * @param string $identifier
   *   A resource identifier.
   * @param bool $deferred
   *   Send to the queue for later? Will import immediately if FALSE..
   * @param string $version
   *   A resource's version.
   */
  public function import(string $identifier, bool $deferred = FALSE, $version = NULL): array {

    // If we passed $deferred, immediately add to the queue for later.
    if ($deferred == TRUE) {
      $this->queueImport($identifier, $version);
      return [
        'message' => "Resource {$identifier}:{$version} has been queued to be imported.",
      ];
    }

    [$resource, $result] = $this->getResource($identifier, $version);

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

    /* @var $resource \Drupal\common\Resource */
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

    if ($result[$label]->getStatus() == Result::DONE) {
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
   * @param string $version
   *   A resource's version.
   */
  public function drop($identifier, $version = NULL) {
    $storage = $this->getStorage($identifier, $version);

    if ($storage) {
      $storage->destroy();
    }

    $this->resourceLocalizer->remove($identifier, $version);
  }

  /**
   * Queue a resource for import.
   *
   * @param string $identifier
   *   A resource's identifier.
   * @param string $version
   *   A resource's version.
   *
   * @return int
   *   Queue ID for new queued item.
   */
  private function queueImport($identifier, $version) {
    // Attempt to fetch the file in a queue so as to not block user.
    $queueId = $this->queue->createItem(['identifier' => $identifier, 'version' => $version]);

    if ($queueId === FALSE) {
      throw new \RuntimeException("Failed to create file fetcher queue for {$identifier}:{$version}");
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
    return ImporterList::getList(
      $this->jobStoreFactory,
      $this->resourceLocalizer,
      $this->importServiceFactory);
  }

  /**
   * Get Storage.
   *
   * @param string $identifier
   *   The unique identifier of a resource.
   * @param string $version
   *   The version of the resource.
   */
  public function getStorage($identifier, $version = NULL) {
    /* @var $resource \Drupal\common\Resource */
    $resource = $this->resourceLocalizer->get($identifier, $version);
    if ($resource) {
      $importService = $this->getImportService($resource);
      return $importService->getStorage();
    }
    throw new \Exception("No datastore storage found for {$identifier}:{$version}.");
  }

  /**
   * Run query.
   *
   * @param Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   DKAN Datastore Query API object.
   *
   * @return object
   *   Array of row/record objects.
   */
  public function runQuery(DatastoreQuery $datastoreQuery) {

    $return = new stdClass();

    $storageMap = $this->getQueryStorageMap($datastoreQuery);
    $primaryResource = $datastoreQuery->resources[0]->alias;
    $primaryResourceStorage = $storageMap[$primaryResource];

    if ($datastoreQuery->results) {
      $result = $primaryResourceStorage->query($this->resultsQuery($datastoreQuery, $storageMap));

      if ($datastoreQuery->keys === FALSE) {
        $result = array_map(function ($row) {
          $arrayRow = (array) $row;
          foreach ($arrayRow as $value) {
            $newRow[] = $value;
          }
          return $newRow;
        }, $result);
      }
      $return->results = $result;
    }

    if ($datastoreQuery->count) {
      $countQuery = $this->countQuery($datastoreQuery, $storageMap);
      $return->count = array_pop($primaryResourceStorage->query($countQuery))->expression;
    }

    if ($datastoreQuery->schema) {
      foreach ($datastoreQuery->resources as $resource) {
        $storage = $storageMap[$resource->alias];
        $return->schema->{$resource->id} = (object) $storage->getSchema();
      }
    }

    $return->query = $datastoreQuery;

    return $return;
  }

  private function getQueryStorageMap($datastoreQuery) {
    $storageMap = [];
    foreach ($datastoreQuery->resources as $resource) {
      [$identifier, $version] = Resource::getIdentifierAndVersion($resource->id);
      $storage = $this->getStorage($identifier, $version);
      $storageMap[$resource->alias] = $storage;
    }
    return $storageMap;
  }

  private function resultsQuery($datastoreQuery, $storageMap) {
    if ($this->results = FALSE) {
      throw new \Exception("Results query requested on non-results datastore query.");
    }
    return $this->populateQuery($datastoreQuery, $storageMap);
  }

  private function countQuery($datastoreQuery, $storageMap) {
    if ($this->count = FALSE) {
      throw new \Exception("Results query requested on non-results datastore query.");
    }
    $query = $this->populateQuery($datastoreQuery, $storageMap);
    unset($query->limit, $query->offset);
    $query->count();
    return $query;
  }

  private function populateQuery($datastoreQuery, $storageMap) {
    $query = new Query();
    $query->properties = $datastoreQuery->properties;
    $query->conditions = $datastoreQuery->conditions;
    $this->populateQueryJoins($query, $datastoreQuery, $storageMap);
    $this->populateQuerySort($query, $datastoreQuery);
    $query->limit = $datastoreQuery->limit;
    $query->offset = $datastoreQuery->offset;
    $query->showDbColumns = TRUE;
    return $query;
  }

  private function populateQuerySort($query, $datastoreQuery) {
    foreach ($datastoreQuery->sort->desc as $desc) {
      $query->sort["desc"][] = (object) [
        "collection" => $desc->resource,
        "property" => $desc->property,
      ];
    }
    foreach ($datastoreQuery->sort->asc as $asc) {
      $query->sort["asc"][] = (object) [
        "collection" => $asc->resource,
        "property" => $asc->property,
      ];
    }
  }

  private function populateQueryJoins($query, $datastoreQuery, $storageMap) {
    foreach ($datastoreQuery->joins as $join) {
      $storage = $storageMap[$join->resource];
      $queryJoin = new stdClass();
      $queryJoin->collection = $storage->getTableName();
      $queryJoin->alias = $join->resource;
      foreach ($join->on as $on) {
        $queryJoin->on[] = (object) ["collection" => $on->resource, "property" => $on->property];
      }
      $query->joins[] = $queryJoin;
    }
  }
}
