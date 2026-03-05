# Datastore Data Preview

Renders DKAN datastore data as a paginated, sortable HTML table using Drupal core's `#type => table` with TableSort headers and `#type => pager`. No JavaScript frameworks required.

## Requirements

- DKAN `common` and `datastore` modules enabled
- At least one resource imported into the datastore (for Database and API data sources)

## Installation

```bash
drush en datastore_data_preview
```

## Usage

### Block UI

1. Go to `/admin/structure/block`
2. Place the **Data Preview Table** block (category: DKAN) in any region
3. Configure:
   - **Resource ID** (required) — depends on the data source selected (see below)
   - **Data source** — `Database` for direct DB queries, `API` to query via DKAN's REST API
   - **Columns** — comma-separated column machine names to display (empty = all)
   - **Default page size** — 10, 25, 50, or 100
   - **Default sort column** — column machine name for initial sort
   - **Default sort direction** — ascending or descending

### Dataset Edit Form

When editing a dataset node, inline **Preview data** links appear next to the Download URL field for each distribution with a tabular file type (CSV, TSV). Non-tabular distributions (PDF, ZIP, etc.) are skipped. Links open the admin preview page at `/admin/dkan/data-preview/{resource_id}`.

The datastore dashboard at `/admin/dkan/datastore/status` also shows preview links, but only for importable (tabular) file types.

### Render Element

Use `#type => 'data_preview'` in any render array:

```php
$build['preview'] = [
  '#type' => 'data_preview',
  '#resource_id' => $resource_id,
  '#columns' => ['name', 'date'],
  '#default_page_size' => 50,
  '#conditions' => [
    ['property' => 'state', 'value' => 'VA', 'operator' => '='],
  ],
];
```

#### Element properties

| Property | Type | Default | Description |
|---|---|---|---|
| `#resource_id` | `string` | `''` | Resource identifier (required). |
| `#data_source` | `string` | `'database'` | `'database'` or `'api'`. |
| `#data_source_instance` | `DataSourceInterface\|null` | `null` | Custom data source instance (overrides `#data_source`). |
| `#columns` | `string[]\|string` | `[]` | Columns to display. Array or comma-separated string. Empty = all. |
| `#default_page_size` | `int` | `25` | Initial rows per page. |
| `#default_sort` | `string\|null` | `null` | Column machine name for initial sort. |
| `#default_sort_direction` | `string` | `'asc'` | `'asc'` or `'desc'`. |
| `#conditions` | `array` | `[]` | Filter conditions (see Conditions format). |
| `#api_base_url` | `string` | `''` | Base URL for API source to query an external DKAN site. |

Note: `page_sizes`, `pager_element`, and `query_prefix` are only available via the programmatic builder API, not as element properties.

### Resource ID Resolution

Use `dkan.data_preview.resource_resolver` to convert a distribution UUID or node into the `{identifier}__{version}` format needed by data sources:

```php
$resolver = \Drupal::service('dkan.data_preview.resource_resolver');

// From a distribution UUID:
$resourceId = $resolver->resolve('94fd78d4-95f4-515a-b7c6-496be5368bdb');

// From a distribution node:
$resourceId = $resolver->resolveFromNode($node);

if ($resourceId) {
  $build = \Drupal::service('dkan.data_preview.builder')
    ->build($dataSource, $resourceId);
}
```

Both methods return `NULL` if the resource can't be resolved. `resolve()` also accepts already-formatted `{identifier}__{version}` strings as a passthrough.

### Programmatic

Use the `dkan.data_preview.builder` service from any controller, form, or preprocess function:

```php
$builder = \Drupal::service('dkan.data_preview.builder');
$dataSource = \Drupal::service('dkan.data_preview.datasource.database');

$build['preview'] = $builder->build($dataSource, $resource_id, [
  'columns' => ['name', 'date', 'amount'],
  'default_page_size' => 50,
  'default_sort' => 'date',
  'default_sort_direction' => 'desc',
  'conditions' => [
    ['property' => 'state', 'value' => 'VA', 'operator' => '='],
  ],
]);
```

#### Builder options

| Option | Type | Default | Description |
|---|---|---|---|
| `columns` | `string[]` | `[]` | Column machine names to display. Empty = all. |
| `page_sizes` | `int[]` | `[10, 25, 50, 100]` | Available page size choices. |
| `default_page_size` | `int` | `25` | Initial rows per page. |
| `default_sort` | `string\|null` | `null` | Column machine name for initial sort. |
| `default_sort_direction` | `string` | `'asc'` | `'asc'` or `'desc'`. |
| `conditions` | `array` | `[]` | Filter conditions (see below). |
| `pager_element` | `int` | `0` | Pager ID for multiple tables on one page. |
| `query_prefix` | `string` | `''` | Prefix for `page_size` query param to avoid collisions. |

#### Conditions format

Conditions use the DKAN Query format. Each condition is an associative array:

```php
['property' => 'column_name', 'value' => 'match_value', 'operator' => '=']
```

Database and API sources support all DKAN Query operators: `=`, `<>`, `<`, `<=`, `>`, `>=`, `IN`, `NOT IN`, `BETWEEN`, `LIKE`. `ArrayDataSource` supports these except `BETWEEN` (and also accepts `!=` as an alias for `<>`).

### Data sources

Three built-in data sources are provided:

- **`dkan.data_preview.datasource.database`** — queries datastore tables directly via `DatastoreService::getStorage()`. Fastest option; requires the datastore module's database tables. **Resource ID format:** `{identifier}__{version}` — the resource hash and version from the distribution's `%Ref:downloadURL` data (e.g. `3a187a87dc6cd47c48b6b4c4785224b7__1763473949`).
- **`dkan.data_preview.datasource.api`** — queries via HTTP POST to `/api/1/datastore/query/{resource_id}`. Useful when the database isn't directly accessible, for decoupled setups, or for querying **external DKAN sites**. Use `setBaseUrl()` to target a remote site, or configure the **API base URL** in the block settings. **Resource ID format:** distribution UUID (e.g. `94fd78d4-95f4-515a-b7c6-496be5368bdb`).
- **`ArrayDataSource`** — generic in-memory data source for any array data. Not a service — instantiate directly. Schema is auto-inferred from the first row (numeric values → `number`, otherwise `text`), or pass an explicit schema:

```php
use Drupal\datastore_data_preview\DataSource\ArrayDataSource;

// Schema auto-inferred from data types.
$source = new ArrayDataSource([
  ['name' => 'Alice', 'score' => 95],
  ['name' => 'Bob', 'score' => 87],
]);

// Or with explicit schema.
$source = new ArrayDataSource($rows, [
  'fields' => [
    'name' => ['type' => 'text', 'description' => 'Name'],
    'score' => ['type' => 'number', 'description' => 'Test Score'],
  ],
]);

$builder->build($source, 'my_data', ['default_page_size' => 25]);
```

### Custom data source

For data sources that need custom fetch logic (external APIs, computed data, etc.), implement `DataSourceInterface` directly. For data already in memory as an array, use `ArrayDataSource` instead.

Implement `DataSourceInterface` to provide data from any source:

```php
use Drupal\datastore_data_preview\DataSource\DataSourceInterface;
use Drupal\datastore_data_preview\DataSource\DataSourceResult;

class MyDataSource implements DataSourceInterface {

  public function fetchData(
    string $resource_id,
    int $limit,
    int $offset,
    ?string $sort_field,
    string $sort_direction,
    array $conditions = [],
    array $properties = [],
  ): DataSourceResult {
    // Fetch rows, total count, and schema from your source.
    return new DataSourceResult($rows, $totalCount, [
      'fields' => [
        'col_name' => ['type' => 'text', 'description' => 'Human Label'],
      ],
    ]);
  }

}
```

Register it as a service and pass it to the builder.

#### Querying an external DKAN site

```php
$apiSource = \Drupal::service('dkan.data_preview.datasource.api');
$apiSource->setBaseUrl('https://data.example.com');

try {
  $build['preview'] = $builder->build($apiSource, $distribution_uuid);
}
finally {
  $apiSource->setBaseUrl(NULL);
}
```

## Theming

The module provides the `datastore_data_preview` theme hook. Override `datastore-data-preview.html.twig` in your theme to customize the layout. Available variables:

- `{{ table }}` — the sortable table (rendered by Drupal core's `table.html.twig`)
- `{{ pager }}` — pagination links
- `{{ page_size_form }}` — page size selector
- `{{ result_summary }}` — "Showing 1-25 of 1,542 results" text

The `datastore_data_preview/data_preview` library provides minimal CSS (flexbox controls, overflow-x on the table wrapper). It does not override theme defaults.

## Testing

```bash
phpunit modules/datastore/modules/datastore_data_preview/tests/
```
