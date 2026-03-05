<?php

namespace Drupal\Tests\datastore_data_preview\Unit\DataSource;

use Drupal\datastore_data_preview\DataSource\ApiDataSource;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \Drupal\datastore_data_preview\DataSource\ApiDataSource
 * @group datastore_data_preview
 */
class ApiDataSourceTest extends TestCase {

  /**
   * Test that the correct POST body is sent to the API.
   */
  public function testRequestBody() {
    $capturedBody = NULL;

    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->expects($this->once())
      ->method('request')
      ->willReturnCallback(function ($method, $url, $options) use (&$capturedBody) {
        $this->assertEquals('POST', $method);
        $this->assertStringContainsString('/api/1/datastore/query/test-resource', $url);
        $capturedBody = $options['json'];
        return $this->createApiResponse([
          'results' => [],
          'count' => 0,
          'schema' => (object) [],
        ]);
      });

    $requestStack = $this->createRequestStack('https://example.com');
    $dataSource = new ApiDataSource($httpClient, $requestStack);
    $dataSource->fetchData('test-resource', 25, 10, 'name', 'asc', [
      ['property' => 'state', 'value' => 'VA', 'operator' => '='],
    ], ['name', 'state']);

    $this->assertEquals(25, $capturedBody['limit']);
    $this->assertEquals(10, $capturedBody['offset']);
    $this->assertTrue($capturedBody['count']);
    $this->assertTrue($capturedBody['results']);
    $this->assertTrue($capturedBody['schema']);
    $this->assertTrue($capturedBody['keys']);
    $this->assertEquals([['property' => 'name', 'order' => 'asc']], $capturedBody['sorts']);
    $this->assertEquals([['property' => 'state', 'value' => 'VA', 'operator' => '=']], $capturedBody['conditions']);
    $this->assertEquals(['name', 'state'], $capturedBody['properties']);
  }

  /**
   * Test response normalization.
   */
  public function testResponseNormalization() {
    $apiResponse = [
      'results' => [
        (object) ['name' => 'Alice', 'age' => '30'],
        (object) ['name' => 'Bob', 'age' => '25'],
      ],
      'count' => 42,
      'schema' => (object) [
        'test-resource' => (object) [
          'fields' => (object) [
            'record_number' => (object) ['type' => 'serial', 'description' => 'Record Number'],
            'name' => (object) ['type' => 'text', 'description' => 'Full Name'],
            'age' => (object) ['type' => 'int', 'description' => 'Age in Years'],
          ],
        ],
      ],
    ];

    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->method('request')->willReturn($this->createApiResponse($apiResponse));

    $requestStack = $this->createRequestStack('https://example.com');
    $dataSource = new ApiDataSource($httpClient, $requestStack);
    $result = $dataSource->fetchData('test-resource', 25, 0, NULL, 'asc');

    $this->assertCount(2, $result->rows);
    $this->assertEquals(42, $result->totalCount);
    $this->assertArrayNotHasKey('record_number', $result->schema['fields']);
    $this->assertArrayHasKey('name', $result->schema['fields']);
    $this->assertEquals('Full Name', $result->schema['fields']['name']['description']);
    $this->assertEquals('Age in Years', $result->schema['fields']['age']['description']);
  }

  /**
   * Test no sort when sort_field is null.
   */
  public function testNoSortWhenNull() {
    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->expects($this->once())
      ->method('request')
      ->willReturnCallback(function ($method, $url, $options) {
        $this->assertArrayNotHasKey('sorts', $options['json']);
        return $this->createApiResponse(['results' => [], 'count' => 0, 'schema' => (object) []]);
      });

    $requestStack = $this->createRequestStack('https://example.com');
    $dataSource = new ApiDataSource($httpClient, $requestStack);
    $dataSource->fetchData('test-resource', 10, 0, NULL, 'asc');
  }

  /**
   * Create a mock RequestStack.
   */
  protected function createRequestStack(string $baseUrl): RequestStack {
    $request = $this->createMock(Request::class);
    $request->method('getSchemeAndHttpHost')->willReturn($baseUrl);

    $requestStack = $this->createMock(RequestStack::class);
    $requestStack->method('getCurrentRequest')->willReturn($request);
    return $requestStack;
  }

  /**
   * Test empty conditions array is omitted from request body.
   */
  public function testEmptyConditionsNotIncluded() {
    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->expects($this->once())
      ->method('request')
      ->willReturnCallback(function ($method, $url, $options) {
        $this->assertArrayNotHasKey('conditions', $options['json']);
        return $this->createApiResponse(['results' => [], 'count' => 0, 'schema' => (object) []]);
      });

    $requestStack = $this->createRequestStack('https://example.com');
    $dataSource = new ApiDataSource($httpClient, $requestStack);
    $dataSource->fetchData('test-resource', 10, 0, NULL, 'asc', [], []);
  }

  /**
   * Test empty properties array is omitted from request body.
   */
  public function testEmptyPropertiesNotIncluded() {
    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->expects($this->once())
      ->method('request')
      ->willReturnCallback(function ($method, $url, $options) {
        $this->assertArrayNotHasKey('properties', $options['json']);
        return $this->createApiResponse(['results' => [], 'count' => 0, 'schema' => (object) []]);
      });

    $requestStack = $this->createRequestStack('https://example.com');
    $dataSource = new ApiDataSource($httpClient, $requestStack);
    $dataSource->fetchData('test-resource', 10, 0, NULL, 'asc', [], []);
  }

  /**
   * Test schema fallback to flat fields (not keyed by resource ID).
   */
  public function testSchemaFallbackToFlatSchema() {
    $apiResponse = [
      'results' => [],
      'count' => 0,
      'schema' => (object) [
        'fields' => (object) [
          'name' => (object) ['type' => 'text', 'description' => 'Name'],
        ],
      ],
    ];

    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->method('request')->willReturn($this->createApiResponse($apiResponse));

    $requestStack = $this->createRequestStack('https://example.com');
    $dataSource = new ApiDataSource($httpClient, $requestStack);
    $result = $dataSource->fetchData('test-resource', 10, 0, NULL, 'asc');

    $this->assertArrayHasKey('name', $result->schema['fields']);
    $this->assertEquals('Name', $result->schema['fields']['name']['description']);
  }

  /**
   * Test schema fallback to empty when no schema fields present.
   */
  public function testSchemaFallbackToEmpty() {
    $apiResponse = [
      'results' => [],
      'count' => 0,
      'schema' => (object) [],
    ];

    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->method('request')->willReturn($this->createApiResponse($apiResponse));

    $requestStack = $this->createRequestStack('https://example.com');
    $dataSource = new ApiDataSource($httpClient, $requestStack);
    $result = $dataSource->fetchData('test-resource', 10, 0, NULL, 'asc');

    $this->assertEmpty($result->schema['fields']);
  }

  /**
   * Test missing count in response defaults to zero.
   */
  public function testMissingCountDefaultsToZero() {
    $apiResponse = [
      'results' => [(object) ['name' => 'Alice']],
      'schema' => (object) [],
    ];

    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->method('request')->willReturn($this->createApiResponse($apiResponse));

    $requestStack = $this->createRequestStack('https://example.com');
    $dataSource = new ApiDataSource($httpClient, $requestStack);
    $result = $dataSource->fetchData('test-resource', 10, 0, NULL, 'asc');

    $this->assertEquals(0, $result->totalCount);
  }

  /**
   * Test missing results in response defaults to empty.
   */
  public function testMissingResultsDefaultsToEmpty() {
    $apiResponse = [
      'count' => 5,
      'schema' => (object) [],
    ];

    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->method('request')->willReturn($this->createApiResponse($apiResponse));

    $requestStack = $this->createRequestStack('https://example.com');
    $dataSource = new ApiDataSource($httpClient, $requestStack);
    $result = $dataSource->fetchData('test-resource', 10, 0, NULL, 'asc');

    $this->assertEmpty($result->rows);
  }

  /**
   * Test null request (CLI context) returns empty base URL.
   */
  public function testNullRequestReturnsEmptyBaseUrl() {
    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->expects($this->once())
      ->method('request')
      ->willReturnCallback(function ($method, $url, $options) {
        // Base URL should be empty, so URL starts with /api.
        $this->assertStringStartsWith('/api/1/datastore/query/', $url);
        return $this->createApiResponse(['results' => [], 'count' => 0, 'schema' => (object) []]);
      });

    $requestStack = $this->createMock(RequestStack::class);
    $requestStack->method('getCurrentRequest')->willReturn(NULL);

    $dataSource = new ApiDataSource($httpClient, $requestStack);
    $dataSource->fetchData('test-resource', 10, 0, NULL, 'asc');
  }

  /**
   * Test schema field missing type defaults to 'text'.
   */
  public function testFieldMissingTypeDefaultsToText() {
    $apiResponse = [
      'results' => [],
      'count' => 0,
      'schema' => (object) [
        'test-resource' => (object) [
          'fields' => (object) [
            'col1' => (object) ['description' => 'Column One'],
          ],
        ],
      ],
    ];

    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->method('request')->willReturn($this->createApiResponse($apiResponse));

    $requestStack = $this->createRequestStack('https://example.com');
    $dataSource = new ApiDataSource($httpClient, $requestStack);
    $result = $dataSource->fetchData('test-resource', 10, 0, NULL, 'asc');

    $this->assertEquals('text', $result->schema['fields']['col1']['type']);
  }

  /**
   * Test schema field missing description defaults to machine name.
   */
  public function testFieldMissingDescriptionDefaultsToName() {
    $apiResponse = [
      'results' => [],
      'count' => 0,
      'schema' => (object) [
        'test-resource' => (object) [
          'fields' => (object) [
            'my_column' => (object) ['type' => 'int'],
          ],
        ],
      ],
    ];

    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->method('request')->willReturn($this->createApiResponse($apiResponse));

    $requestStack = $this->createRequestStack('https://example.com');
    $dataSource = new ApiDataSource($httpClient, $requestStack);
    $result = $dataSource->fetchData('test-resource', 10, 0, NULL, 'asc');

    $this->assertEquals('my_column', $result->schema['fields']['my_column']['description']);
  }

  /**
   * Test custom base URL is used for HTTP requests.
   */
  public function testCustomBaseUrl() {
    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->expects($this->once())
      ->method('request')
      ->willReturnCallback(function ($method, $url, $options) {
        $this->assertStringStartsWith('https://external.example.com/api/1/datastore/query/', $url);
        return $this->createApiResponse(['results' => [], 'count' => 0, 'schema' => (object) []]);
      });

    $requestStack = $this->createRequestStack('https://local.example.com');
    $dataSource = new ApiDataSource($httpClient, $requestStack);
    $dataSource->setBaseUrl('https://external.example.com');
    $dataSource->fetchData('test-resource', 10, 0, NULL, 'asc');
  }

  /**
   * Test resetting base URL falls back to request stack.
   */
  public function testResetBaseUrl() {
    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->expects($this->once())
      ->method('request')
      ->willReturnCallback(function ($method, $url, $options) {
        $this->assertStringStartsWith('https://local.example.com/api/1/datastore/query/', $url);
        return $this->createApiResponse(['results' => [], 'count' => 0, 'schema' => (object) []]);
      });

    $requestStack = $this->createRequestStack('https://local.example.com');
    $dataSource = new ApiDataSource($httpClient, $requestStack);
    $dataSource->setBaseUrl('https://external.example.com');
    $dataSource->setBaseUrl(NULL);
    $dataSource->fetchData('test-resource', 10, 0, NULL, 'asc');
  }

  /**
   * Create a mock API response.
   */
  protected function createApiResponse(array $data): ResponseInterface {
    $body = $this->createMock(StreamInterface::class);
    $body->method('__toString')->willReturn(json_encode($data));

    $response = $this->createMock(ResponseInterface::class);
    $response->method('getBody')->willReturn($body);
    return $response;
  }

}
