<?php

namespace Drupal\datastore_data_preview\DataSource;

/**
 * In-memory data source for arbitrary array data.
 *
 * Accepts rows and optional schema at construction. Handles filtering,
 * sorting, and pagination entirely in PHP — no database or API required.
 */
class ArrayDataSource implements DataSourceInterface {

  /**
   * Column schema in standard format.
   *
   * @var array
   */
  protected array $schema;

  /**
   * Constructs an ArrayDataSource.
   *
   * @param array $rows
   *   Array of associative arrays (e.g. [['name' => 'Alice', 'age' => 30]]).
   * @param array $schema
   *   Optional schema with 'fields' key mapping column names to
   *   type/description arrays. Auto-inferred from first row if omitted.
   */
  public function __construct(
    protected array $rows,
    array $schema = [],
  ) {
    $this->schema = $schema ?: $this->inferSchema($rows);
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
    $data = $this->rows;

    // Apply conditions.
    $data = array_values(array_filter($data, function (array $row) use ($conditions) {
      foreach ($conditions as $condition) {
        $property = $condition['property'];
        $value = $condition['value'];
        $operator = $condition['operator'] ?? '=';
        $cellValue = $row[$property] ?? NULL;

        if (!$this->evaluateCondition($cellValue, $operator, $value)) {
          return FALSE;
        }
      }
      return TRUE;
    }));

    $totalCount = count($data);

    // Apply sorting.
    if ($sort_field && isset($data[0][$sort_field])) {
      usort($data, function ($a, $b) use ($sort_field, $sort_direction) {
        $cmp = $a[$sort_field] <=> $b[$sort_field];
        return strtolower($sort_direction) === 'desc' ? -$cmp : $cmp;
      });
    }

    // Apply limit/offset.
    if ($limit > 0) {
      $data = array_slice($data, $offset, $limit);
    }
    elseif ($offset > 0) {
      $data = array_slice($data, $offset);
    }

    // Determine display columns.
    $allColumns = array_keys($this->schema['fields'] ?? []);
    $displayColumns = !empty($properties) ? array_intersect($allColumns, $properties) : $allColumns;

    // Convert to stdClass objects.
    $rows = array_map(function (array $row) use ($displayColumns) {
      $obj = new \stdClass();
      foreach ($displayColumns as $col) {
        $obj->{$col} = $row[$col] ?? '';
      }
      return $obj;
    }, $data);

    return new DataSourceResult($rows, $totalCount, $this->schema);
  }

  /**
   * Evaluate a single filter condition.
   */
  protected function evaluateCondition(mixed $cellValue, string $operator, mixed $value): bool {
    return match (strtoupper($operator)) {
      '=' => $cellValue == $value,
      '<>', '!=' => $cellValue != $value,
      '>' => $cellValue > $value,
      '>=' => $cellValue >= $value,
      '<' => $cellValue < $value,
      '<=' => $cellValue <= $value,
      'LIKE' => is_string($cellValue) && preg_match('/^' . str_replace(['%', '_'], ['.*', '.'], preg_quote($value, '/')) . '$/i', $cellValue),
      'IN' => is_array($value) && in_array($cellValue, $value),
      'NOT IN' => is_array($value) && !in_array($cellValue, $value),
      default => TRUE,
    };
  }

  /**
   * Infer schema from the first row of data.
   */
  protected function inferSchema(array $rows): array {
    if (empty($rows)) {
      return ['fields' => []];
    }

    $fields = [];
    foreach ($rows[0] as $key => $value) {
      $type = (is_int($value) || is_float($value)) ? 'number' : 'text';
      $description = ucfirst(str_replace('_', ' ', $key));
      $fields[$key] = ['type' => $type, 'description' => $description];
    }

    return ['fields' => $fields];
  }

}
