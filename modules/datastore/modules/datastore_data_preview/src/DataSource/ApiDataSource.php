<?php

namespace Drupal\datastore_data_preview\DataSource;

use GuzzleHttp\ClientInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Data source that queries datastore data via the DKAN REST API.
 */
class ApiDataSource implements DataSourceInterface {

  /**
   * Custom base URL for querying an external DKAN site.
   */
  protected ?string $baseUrl = NULL;

  /**
   * Constructor.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack, used to derive the base URL.
   */
  public function __construct(
    protected ClientInterface $httpClient,
    protected RequestStack $requestStack,
  ) {}

  /**
   * Set a custom base URL for API requests.
   *
   * @param string|null $baseUrl
   *   The base URL (e.g. 'https://data.example.com'), or NULL to reset to
   *   the current request's host.
   *
   * @return static
   */
  public function setBaseUrl(?string $baseUrl): static {
    $this->baseUrl = $baseUrl !== NULL ? rtrim($baseUrl, '/') : NULL;
    return $this;
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
    $body = $this->buildRequestBody($limit, $offset, $sort_field, $sort_direction, $conditions, $properties);
    $baseUrl = $this->getBaseUrl();
    $url = $baseUrl . '/api/1/datastore/query/' . $resource_id;

    $response = $this->httpClient->request('POST', $url, [
      'json' => $body,
      'headers' => ['Content-Type' => 'application/json'],
    ]);

    $data = json_decode((string) $response->getBody(), FALSE);

    return $this->normalizeResponse($data, $resource_id);
  }

  /**
   * Build the JSON request body for the datastore query API.
   */
  protected function buildRequestBody(
    int $limit,
    int $offset,
    ?string $sort_field,
    string $sort_direction,
    array $conditions,
    array $properties,
  ): array {
    $body = [
      'limit' => $limit,
      'offset' => $offset,
      'count' => TRUE,
      'results' => TRUE,
      'schema' => TRUE,
      'keys' => TRUE,
    ];

    if ($sort_field) {
      $body['sorts'] = [
        ['property' => $sort_field, 'order' => strtolower($sort_direction)],
      ];
    }

    if (!empty($conditions)) {
      $body['conditions'] = $conditions;
    }

    if (!empty($properties)) {
      $body['properties'] = $properties;
    }

    return $body;
  }

  /**
   * Get the base URL from the current request.
   */
  protected function getBaseUrl(): string {
    if ($this->baseUrl !== NULL) {
      return $this->baseUrl;
    }
    $request = $this->requestStack->getCurrentRequest();
    return $request ? $request->getSchemeAndHttpHost() : '';
  }

  /**
   * Normalize the API response into a DataSourceResult.
   */
  protected function normalizeResponse(object $data, string $resource_id): DataSourceResult {
    $rows = $data->results ?? [];
    $totalCount = (int) ($data->count ?? 0);

    // The API returns schema keyed by resource ID; flatten to Drupal format.
    $schema = ['fields' => []];
    $rawSchema = $data->schema ?? new \stdClass();

    // Schema may be keyed by resource ID or be flat.
    $schemaFields = $rawSchema->{$resource_id}->fields ?? $rawSchema->fields ?? new \stdClass();

    foreach ($schemaFields as $name => $field) {
      if ($name === DataSourceInterface::HIDDEN_FIELD) {
        continue;
      }
      $schema['fields'][$name] = [
        'type' => $field->type ?? 'text',
        'description' => $field->description ?? $name,
      ];
    }

    return new DataSourceResult($rows, $totalCount, $schema);
  }

}
