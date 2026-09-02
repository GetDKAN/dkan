<?php

namespace Drupal\dkan_datastore_preview\DataSource;

use Drupal\dkan_common\Storage\Query;
use Drupal\dkan_datastore\DatastoreService;

/**
 * Data source that queries datastore tables directly via the database.
 */
class DatabaseDataSource implements DataSourceInterface {

  /**
   * Memoized storage objects, keyed by resource id.
   *
   * @var array
   */
  protected array $storages = [];

  /**
   * Constructor.
   *
   * @param \Drupal\dkan_datastore\DatastoreService $datastoreService
   *   The DKAN datastore service.
   */
  public function __construct(
    protected DatastoreService $datastoreService,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getSchema(string $resource_id): array {
    $storage = $this->getStorage($resource_id);
    if (!$storage) {
      return [];
    }
    $schema = $storage->getSchema();
    if (empty($schema['fields'])) {
      return [];
    }
    unset($schema['fields'][self::HIDDEN_FIELD]);
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchData(
    string $resource_id,
    int $limit,
    int $offset,
    ?string $sort_field,
    string $sort_direction,
    array $conditions = [],
    array $properties = [],
  ): DataSourceResult {
    $storage = $this->getStorage($resource_id);
    if (!$storage) {
      return new DataSourceResult([], 0);
    }

    $query = $this->buildQuery($limit, $offset, $sort_field, $sort_direction, $conditions, $properties);
    $rows = $storage->query($query);

    $countQuery = $this->buildQuery(0, 0, NULL, 'asc', $conditions, []);
    $countQuery->count();
    $countResult = $storage->query($countQuery);
    $totalCount = (int) ($countResult[0]->expression ?? 0);

    return new DataSourceResult($rows, $totalCount);
  }

  /**
   * Get the datastore storage for a resource id, or NULL if none exists.
   *
   * @param string $resource_id
   *   Resource id, optionally in "identifier__version" format.
   *
   * @return \Drupal\dkan_datastore\Storage\DatabaseTable|null
   *   The storage, or NULL when the resource has no datastore table.
   */
  protected function getStorage(string $resource_id) {
    if (!array_key_exists($resource_id, $this->storages)) {
      [$identifier, $version] = $this->parseResourceId($resource_id);
      try {
        $this->storages[$resource_id] = $this->datastoreService->getStorage($identifier, $version);
      }
      catch (\InvalidArgumentException) {
        $this->storages[$resource_id] = NULL;
      }
    }
    return $this->storages[$resource_id];
  }

  /**
   * Parse a resource id into identifier and version components.
   *
   * @param string $resource_id
   *   Resource id, optionally in "identifier__version" format.
   *
   * @return array
   *   [identifier, version|null].
   */
  protected function parseResourceId(string $resource_id): array {
    if (str_contains($resource_id, '__')) {
      $parts = explode('__', $resource_id, 2);
      return [$parts[0], $parts[1]];
    }
    return [$resource_id, NULL];
  }

  /**
   * Build a DKAN Query object.
   */
  protected function buildQuery(
    int $limit,
    int $offset,
    ?string $sort_field,
    string $sort_direction,
    array $conditions,
    array $properties,
  ): Query {
    $query = new Query();

    if ($limit > 0) {
      $query->limitTo($limit);
    }
    if ($offset > 0) {
      $query->offsetBy($offset);
    }

    if ($sort_field) {
      if (strtolower($sort_direction) === 'desc') {
        $query->sortByDescending($sort_field);
      }
      else {
        $query->sortByAscending($sort_field);
      }
    }

    foreach ($conditions as $condition) {
      $query->conditions[] = (object) [
        'property' => $condition['property'],
        'value' => $condition['value'],
        'operator' => $condition['operator'] ?? '=',
      ];
    }

    foreach ($properties as $property) {
      $query->filterByProperty($property);
    }

    return $query;
  }

}
