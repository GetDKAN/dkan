<?php

namespace Drupal\datastore_data_preview\DataSource;

use Drupal\common\Storage\Query;
use Drupal\datastore\DatastoreService;

/**
 * Data source that queries datastore tables directly via the database.
 */
class DatabaseDataSource implements DataSourceInterface {

  /**
   * Constructor.
   *
   * @param \Drupal\datastore\DatastoreService $datastoreService
   *   The DKAN datastore service.
   */
  public function __construct(
    protected DatastoreService $datastoreService,
  ) {}

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
    [$identifier, $version] = $this->parseResourceId($resource_id);
    $storage = $this->datastoreService->getStorage($identifier, $version);

    // Build the data query.
    $query = $this->buildQuery($limit, $offset, $sort_field, $sort_direction, $conditions, $properties);
    $rows = $storage->query($query);

    // Build a separate count query with the same conditions.
    $countQuery = $this->buildQuery(0, 0, NULL, 'asc', $conditions, []);
    $countQuery->count();
    $countResult = $storage->query($countQuery);
    $totalCount = (int) ($countResult[0]->expression ?? 0);

    // Get schema, filtering out record_number.
    $schema = $storage->getSchema();
    if (isset($schema['fields'][DataSourceInterface::HIDDEN_FIELD])) {
      unset($schema['fields'][DataSourceInterface::HIDDEN_FIELD]);
    }

    return new DataSourceResult($rows, $totalCount, $schema);
  }

  /**
   * Parse a resource ID into identifier and version components.
   *
   * @param string $resource_id
   *   Resource ID, optionally in "identifier__version" format.
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
