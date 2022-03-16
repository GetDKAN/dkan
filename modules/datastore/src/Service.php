<?php

namespace Drupal\datastore;

use Drupal\common\Resource;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Service\DatastoreQuery;
use Procrastinator\Result;
use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\datastore\Service\Factory\ImportFactoryInterface;
use Drupal\datastore\Service\Info\ImportInfoList;
use Drupal\datastore\Storage\QueryFactory;

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
   */
  public function drop(string $identifier, $version = NULL) {
    $storage = $this->getStorage($identifier, $version);

    if ($storage) {
      $storage->destruct();
    }

    $this->resourceLocalizer->remove($identifier, $version);
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
   * Run query.
   *
   * @param \Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   DKAN Datastore Query API object.
   *
   * @return \RootedData\RootedJsonData
   *   Array of row/record objects.
   */
  public function runQuery(DatastoreQuery $datastoreQuery) {
    $return = (object) [];
    if ($datastoreQuery->{"$.results"} !== FALSE) {
      $return->results = $this->runResultsQuery($datastoreQuery);
    }

    if ($datastoreQuery->{"$.count"} !== FALSE) {
      $return->count = $this->runCountQuery($datastoreQuery);
    }

    if ($datastoreQuery->{"$.schema"} !== FALSE) {
      $return->schema = $this->getSchema($datastoreQuery);
    }

    $return->query = $datastoreQuery->{"$"};
    return new RootedJsonData(json_encode($return));
  }

  /**
   * Get an a schema for each resource.
   *
   * @param \Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   DKAN Datastore Query API object.
   *
   * @return array
   *   An assoc array containing a table schema for each resource.
   */
  private function getSchema(DatastoreQuery $datastoreQuery) {
    $storageMap = $this->getQueryStorageMap($datastoreQuery);
    if (!$datastoreQuery->{"$.resources"}) {
      return [];
    }
    $schema = [];
    foreach ($datastoreQuery->{"$.resources"} as $resource) {
      $storage = $storageMap[$resource["alias"]];
      $schemaItem = $storage->getSchema();
      if (empty($datastoreQuery->{"$.rowIds"})) {
        $schemaItem = $this->filterSchemaFields($schemaItem, $storage->primaryKey());
      }
      $schema[$resource["id"]] = $schemaItem;
    }
    return $schema;
  }

  /**
   * Retrieve storage objects for all resources, and map to their aliases.
   *
   * @param \Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   DatastoreQuery object.
   *
   * @return array
   *   Array of storage objects, keyed to resource aliases.
   */
  public function getQueryStorageMap(DatastoreQuery $datastoreQuery) {
    $storageMap = [];
    foreach ($datastoreQuery->{"$.resources"} as $resource) {
      list($identifier, $version) = Resource::getIdentifierAndVersion($resource["id"]);
      $storage = $this->getStorage($identifier, $version);
      $storageMap[$resource["alias"]] = $storage;
    }
    return $storageMap;
  }

  /**
   * Build query object for main "results query" for datastore.
   *
   * @param \Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   DatastoreQuery object.
   * @param bool $fetch
   *   Perform fetchAll and return array if true, else just statement (cursor).
   *
   * @return array|\Drupal\Core\Database\StatementInterface
   *   Array of result objects or result statement of $fetch is false.
   */
  public function runResultsQuery(DatastoreQuery $datastoreQuery, $fetch = TRUE) {
    $primaryAlias = $datastoreQuery->{"$.resources[0].alias"};
    if (!$primaryAlias) {
      return [];
    }

    $storageMap = $this->getQueryStorageMap($datastoreQuery);

    $storage = $storageMap[$primaryAlias];

    if (empty($datastoreQuery->{"$.rowIds"}) && empty($datastoreQuery->{"$.properties"}) && $storage->getSchema()) {
      $schema = $this->filterSchemaFields($storage->getSchema(), $storage->primaryKey());
      $datastoreQuery->{"$.properties"} = array_keys($schema['fields']);
    }

    $query = QueryFactory::create($datastoreQuery, $storageMap);

    $result = $storageMap[$primaryAlias]->query($query, $primaryAlias, $fetch);

    if ($datastoreQuery->{"$.keys"} === FALSE && is_array($result)) {
      $result = array_map([$this, 'stripRowKeys'], $result);
    }
    return $result;

  }

  /**
   * Remove the primary key from the schema field list.
   *
   * @param array $schema
   *   Schema array, should contain a key "fields".
   * @param string $primaryKey
   *   The name of the primary key field to filter out.
   *
   * @return array
   *   Filtered schema fields.
   */
  private function filterSchemaFields(array $schema, string $primaryKey) : array {
    // Hide identifier field by default.
    if (isset($schema["primary key"][0]) && $schema["primary key"][0] == $primaryKey) {
      unset($schema['fields'][$primaryKey], $schema['primary key'][0]);
    }
    return array_filter($schema);
  }

  /**
   * Strip keys from results row, convert to array.
   *
   * @param object $row
   *   Query result row.
   *
   * @return array
   *   Values only array.
   */
  private function stripRowKeys($row) {
    $arrayRow = (array) $row;
    $newRow = [];
    foreach ($arrayRow as $value) {
      $newRow[] = $value;
    }
    return $newRow;
  }

  /**
   * Build count query object for datastore.
   *
   * @param \Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   DatastoreQuery object.
   */
  private function runCountQuery(DatastoreQuery $datastoreQuery) {
    $primaryAlias = $datastoreQuery->{"$.resources[0].alias"};
    if (!$primaryAlias) {
      return 0;
    }

    $storageMap = $this->getQueryStorageMap($datastoreQuery);
    $query = QueryFactory::create($datastoreQuery, $storageMap);

    unset($query->limit, $query->offset);
    $query->count();
    return (int) $storageMap[$primaryAlias]->query($query, $primaryAlias)[0]->expression;
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
