<?php

namespace Drupal\Tests\datastore_data_preview\Unit\DataSource;

use Drupal\common\Storage\Query;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore_data_preview\DataSource\DatabaseDataSource;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\datastore_data_preview\DataSource\DatabaseDataSource
 * @group datastore_data_preview
 */
class DatabaseDataSourceTest extends TestCase {

  /**
   * Test basic data fetch.
   */
  public function testFetchData() {
    $schema = [
      'fields' => [
        'record_number' => ['type' => 'serial'],
        'name' => ['type' => 'text', 'description' => 'Name'],
        'age' => ['type' => 'int', 'description' => 'Age'],
      ],
    ];

    $rows = [
      (object) ['name' => 'Alice', 'age' => 30],
      (object) ['name' => 'Bob', 'age' => 25],
    ];

    $countResult = [(object) ['expression' => 42]];

    // Mock directly since query is called twice (data + count).
    $storage = $this->createMock(DatabaseTable::class);
    $storage->method('getSchema')->willReturn($schema);
    $storage->method('query')->willReturnOnConsecutiveCalls($rows, $countResult);

    $datastoreService = $this->createMock(DatastoreService::class);
    $datastoreService->method('getStorage')->willReturn($storage);

    $dataSource = new DatabaseDataSource($datastoreService);
    $result = $dataSource->fetchData('test-id', 10, 0, 'name', 'asc');

    $this->assertEquals(42, $result->totalCount);
    $this->assertCount(2, $result->rows);
    $this->assertEquals('Alice', $result->rows[0]->name);
    $this->assertArrayNotHasKey('record_number', $result->schema['fields']);
    $this->assertArrayHasKey('name', $result->schema['fields']);
    $this->assertArrayHasKey('age', $result->schema['fields']);
  }

  /**
   * Test composite identifier__version parsing.
   */
  public function testCompositeResourceId() {
    $schema = ['fields' => ['col1' => ['type' => 'text', 'description' => 'Col 1']]];
    $rows = [];
    $countResult = [(object) ['expression' => 0]];

    $datastoreService = $this->createMock(DatastoreService::class);
    $datastoreService->expects($this->once())
      ->method('getStorage')
      ->with('my-identifier', 'my-version')
      ->willReturn($this->createStorageMock($schema, $rows, $countResult));

    $dataSource = new DatabaseDataSource($datastoreService);
    $result = $dataSource->fetchData('my-identifier__my-version', 10, 0, NULL, 'asc');

    $this->assertEquals(0, $result->totalCount);
  }

  /**
   * Test that conditions are passed through to the query.
   */
  public function testConditionsPassthrough() {
    $schema = ['fields' => ['state' => ['type' => 'text', 'description' => 'State']]];
    $rows = [(object) ['state' => 'VA']];
    $countResult = [(object) ['expression' => 1]];

    $storage = $this->createMock(DatabaseTable::class);
    $storage->method('getSchema')->willReturn($schema);
    $storage->method('query')
      ->willReturnCallback(function (Query $query) use ($rows, $countResult) {
        if ($query->count) {
          return $countResult;
        }
        // Verify conditions were set.
        $this->assertCount(1, $query->conditions);
        $this->assertEquals('state', $query->conditions[0]->property);
        $this->assertEquals('VA', $query->conditions[0]->value);
        $this->assertEquals('=', $query->conditions[0]->operator);
        return $rows;
      });

    $datastoreService = $this->createMock(DatastoreService::class);
    $datastoreService->method('getStorage')->willReturn($storage);

    $dataSource = new DatabaseDataSource($datastoreService);
    $result = $dataSource->fetchData('test-id', 10, 0, NULL, 'asc', [
      ['property' => 'state', 'value' => 'VA', 'operator' => '='],
    ]);

    $this->assertCount(1, $result->rows);
  }

  /**
   * Test sort direction handling.
   */
  public function testSortDescending() {
    $schema = ['fields' => ['name' => ['type' => 'text', 'description' => 'Name']]];
    $rows = [];
    $countResult = [(object) ['expression' => 0]];

    $storage = $this->createMock(DatabaseTable::class);
    $storage->method('getSchema')->willReturn($schema);
    $storage->method('query')
      ->willReturnCallback(function (Query $query) use ($rows, $countResult) {
        if ($query->count) {
          return $countResult;
        }
        // Verify descending sort.
        $this->assertCount(1, $query->sorts);
        $this->assertEquals('name', $query->sorts[0]->property);
        $this->assertEquals('desc', $query->sorts[0]->order);
        return $rows;
      });

    $datastoreService = $this->createMock(DatastoreService::class);
    $datastoreService->method('getStorage')->willReturn($storage);

    $dataSource = new DatabaseDataSource($datastoreService);
    $dataSource->fetchData('test-id', 10, 0, 'name', 'desc');
  }

  /**
   * Test simple resource ID without version separator.
   */
  public function testSimpleResourceIdNoVersion() {
    $schema = ['fields' => ['col1' => ['type' => 'text', 'description' => 'Col 1']]];
    $rows = [];
    $countResult = [(object) ['expression' => 0]];

    $datastoreService = $this->createMock(DatastoreService::class);
    $datastoreService->expects($this->once())
      ->method('getStorage')
      ->with('simple-id', NULL)
      ->willReturn($this->createStorageMock($schema, $rows, $countResult));

    $dataSource = new DatabaseDataSource($datastoreService);
    $dataSource->fetchData('simple-id', 10, 0, NULL, 'asc');
  }

  /**
   * Test properties array calls filterByProperty for each property.
   */
  public function testPropertyPassthrough() {
    $schema = [
      'fields' => [
        'name' => ['type' => 'text', 'description' => 'Name'],
        'age' => ['type' => 'int', 'description' => 'Age'],
      ],
    ];
    $rows = [];
    $countResult = [(object) ['expression' => 0]];

    $storage = $this->createMock(DatabaseTable::class);
    $storage->method('getSchema')->willReturn($schema);
    $storage->method('query')
      ->willReturnCallback(function (Query $query) use ($rows, $countResult) {
        if ($query->count) {
          return $countResult;
        }
        // The data query should have 'name' in its properties.
        $this->assertContains('name', $query->properties);
        return $rows;
      });

    $datastoreService = $this->createMock(DatastoreService::class);
    $datastoreService->method('getStorage')->willReturn($storage);

    $dataSource = new DatabaseDataSource($datastoreService);
    $dataSource->fetchData('test-id', 10, 0, NULL, 'asc', [], ['name']);
  }

  /**
   * Test no sort added when sort_field is NULL.
   */
  public function testNoSortWhenNull() {
    $schema = ['fields' => ['name' => ['type' => 'text', 'description' => 'Name']]];
    $rows = [];
    $countResult = [(object) ['expression' => 0]];

    $storage = $this->createMock(DatabaseTable::class);
    $storage->method('getSchema')->willReturn($schema);
    $storage->method('query')
      ->willReturnCallback(function (Query $query) use ($rows, $countResult) {
        if ($query->count) {
          return $countResult;
        }
        $this->assertEmpty($query->sorts);
        return $rows;
      });

    $datastoreService = $this->createMock(DatastoreService::class);
    $datastoreService->method('getStorage')->willReturn($storage);

    $dataSource = new DatabaseDataSource($datastoreService);
    $dataSource->fetchData('test-id', 10, 0, NULL, 'asc');
  }

  /**
   * Test schema without record_number doesn't error.
   */
  public function testSchemaWithoutRecordNumber() {
    $schema = [
      'fields' => [
        'name' => ['type' => 'text', 'description' => 'Name'],
      ],
    ];
    $rows = [];
    $countResult = [(object) ['expression' => 0]];

    $datastoreService = $this->createMock(DatastoreService::class);
    $datastoreService->method('getStorage')
      ->willReturn($this->createStorageMock($schema, $rows, $countResult));

    $dataSource = new DatabaseDataSource($datastoreService);
    $result = $dataSource->fetchData('test-id', 10, 0, NULL, 'asc');

    $this->assertArrayHasKey('name', $result->schema['fields']);
    $this->assertArrayNotHasKey('record_number', $result->schema['fields']);
  }

  /**
   * Test offset and limit values in query.
   */
  public function testOffsetAndLimitInQuery() {
    $schema = ['fields' => ['name' => ['type' => 'text', 'description' => 'Name']]];
    $rows = [];
    $countResult = [(object) ['expression' => 0]];

    $storage = $this->createMock(DatabaseTable::class);
    $storage->method('getSchema')->willReturn($schema);
    $storage->method('query')
      ->willReturnCallback(function (Query $query) use ($rows, $countResult) {
        if ($query->count) {
          return $countResult;
        }
        $this->assertEquals(50, $query->limit);
        $this->assertEquals(100, $query->offset);
        return $rows;
      });

    $datastoreService = $this->createMock(DatastoreService::class);
    $datastoreService->method('getStorage')->willReturn($storage);

    $dataSource = new DatabaseDataSource($datastoreService);
    $dataSource->fetchData('test-id', 50, 100, NULL, 'asc');
  }

  /**
   * Test zero limit does not call limitTo.
   */
  public function testZeroLimitSkipsLimitTo() {
    $schema = ['fields' => ['name' => ['type' => 'text', 'description' => 'Name']]];
    $rows = [];
    $countResult = [(object) ['expression' => 0]];

    $storage = $this->createMock(DatabaseTable::class);
    $storage->method('getSchema')->willReturn($schema);
    $storage->method('query')
      ->willReturnCallback(function (Query $query) use ($rows, $countResult) {
        if ($query->count) {
          return $countResult;
        }
        // Limit should not be set when limit=0.
        $this->assertNull($query->limit);
        return $rows;
      });

    $datastoreService = $this->createMock(DatastoreService::class);
    $datastoreService->method('getStorage')->willReturn($storage);

    $dataSource = new DatabaseDataSource($datastoreService);
    $dataSource->fetchData('test-id', 0, 0, NULL, 'asc');
  }

  /**
   * Helper to create a storage mock.
   */
  protected function createStorageMock(array $schema, array $rows, array $countResult) {
    $storage = $this->createMock(DatabaseTable::class);
    $storage->method('getSchema')->willReturn($schema);
    $storage->method('query')->willReturnOnConsecutiveCalls($rows, $countResult);
    return $storage;
  }

}
