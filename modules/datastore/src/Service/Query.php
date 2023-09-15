<?php

namespace Drupal\datastore\Service;

use Drupal\common\DataResource;
use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\Storage\QueryFactory;

/**
 * Datastore query service.
 */
class Query implements ContainerInjectionInterface {

  /**
   * The datastore service.
   *
   * @var \Drupal\datastore\DatastoreService
   */
  private $datastore;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.datastore.service'),
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\datastore\DatastoreService $datastore
   *   Main datastore service.
   */
  public function __construct(DatastoreService $datastore) {
    $this->datastore = $datastore;
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
      [$identifier, $version] = DataResource::getIdentifierAndVersion($resource["id"]);
      $storage = $this->datastore->getStorage($identifier, $version);
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
   * @param bool $csv
   *   Flag for csv downloads.
   *
   * @return array|\Drupal\Core\Database\StatementInterface
   *   Array of result objects or result statement of $fetch is false.
   */
  public function runResultsQuery(DatastoreQuery $datastoreQuery, $fetch = TRUE, $csv = FALSE) {
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
    // Get data dictionary fields.
    $meta_data = $csv != FALSE ? $this->getDatastoreService()->getDataDictionaryFields() : NULL;
    // Pass the data dictionary metadata to the query.
    $query->dataDictionaryFields = $csv && $meta_data ? $meta_data : NULL;

    $result = $storageMap[$primaryAlias]->query($query, $primaryAlias, $fetch);

    if ($datastoreQuery->{"$.keys"} === FALSE && is_array($result)) {
      $result = array_map([$this, 'stripRowKeys'], $result);
    }
    return $result;

  }

  /**
   * Return the datastore service.
   *
   * @return \Drupal\datastore\DatastoreService
   *   Datastore Service.
   */
  protected function getDatastoreService() {
    return $this->datastore;
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

}
