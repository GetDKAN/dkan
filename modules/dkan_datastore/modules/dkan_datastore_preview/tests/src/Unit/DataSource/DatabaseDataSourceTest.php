<?php

namespace Drupal\Tests\dkan_datastore_preview\Unit\DataSource;

use Drupal\dkan_common\Storage\DatabaseTableInterface;
use Drupal\dkan_datastore\DatastoreService;
use Drupal\dkan_datastore_preview\DataSource\DatabaseDataSource;
use Drupal\Tests\UnitTestCase;

/**
 * @covers \Drupal\dkan_datastore_preview\DataSource\DatabaseDataSource
 * @coversDefaultClass \Drupal\dkan_datastore_preview\DataSource\DatabaseDataSource
 *
 * @group dkan
 * @group dkan_datastore_preview
 * @group unit
 */
class DatabaseDataSourceTest extends UnitTestCase {

  /**
   * Get a data source wired to a mocked storage.
   */
  protected function getDataSource($storage, array &$getStorageCalls = []): DatabaseDataSource {
    $datastoreService = $this->createMock(DatastoreService::class);
    $datastoreService->method('getStorage')
      ->willReturnCallback(function ($identifier, $version) use ($storage, &$getStorageCalls) {
        $getStorageCalls[] = [$identifier, $version];
        if ($storage instanceof \Exception) {
          throw $storage;
        }
        return $storage;
      });
    return new DatabaseDataSource($datastoreService);
  }

  /**
   * Schema excludes record_number and splits identifier__version.
   */
  public function testGetSchema(): void {
    $storage = $this->createMock(DatabaseTableInterface::class);
    $storage->method('getSchema')->willReturn([
      'fields' => [
        'record_number' => ['type' => 'serial'],
        'name' => ['type' => 'text', 'description' => 'Name'],
        'age' => ['type' => 'int'],
      ],
    ]);

    $calls = [];
    $dataSource = $this->getDataSource($storage, $calls);
    $schema = $dataSource->getSchema('abc__123');

    $this->assertSame(['name', 'age'], array_keys($schema['fields']));
    $this->assertSame([['abc', '123']], $calls);
  }

  /**
   * A resource id without a version passes NULL as the version.
   */
  public function testGetSchemaNoVersion(): void {
    $storage = $this->createMock(DatabaseTableInterface::class);
    $storage->method('getSchema')->willReturn(['fields' => ['name' => []]]);

    $calls = [];
    $this->getDataSource($storage, $calls)->getSchema('abc');
    $this->assertSame([['abc', NULL]], $calls);
  }

  /**
   * An unknown resource yields an empty schema instead of an exception.
   */
  public function testGetSchemaMissingStorage(): void {
    $dataSource = $this->getDataSource(new \InvalidArgumentException('No datastore storage found.'));
    $this->assertSame([], $dataSource->getSchema('missing__1'));
  }

  /**
   * An existing table with no fields yields an empty schema.
   */
  public function testGetSchemaEmptyFields(): void {
    $storage = $this->createMock(DatabaseTableInterface::class);
    $storage->method('getSchema')->willReturn(['fields' => []]);
    $this->assertSame([], $this->getDataSource($storage)->getSchema('abc__1'));
  }

  /**
   * FetchData issues exactly one data query and one count query.
   */
  public function testFetchDataQueries(): void {
    $queries = [];
    $rows = [(object) ['name' => 'a'], (object) ['name' => 'b']];

    $storage = $this->createMock(DatabaseTableInterface::class);
    $storage->method('query')->willReturnCallback(function ($query) use (&$queries, $rows) {
      $queries[] = $query;
      return $query->count ? [(object) ['expression' => '42']] : $rows;
    });

    $dataSource = $this->getDataSource($storage);
    $result = $dataSource->fetchData(
      'abc__123',
      10,
      20,
      'age',
      'desc',
      [['property' => 'city', 'value' => 'Denver']],
      ['name', 'age'],
    );

    $this->assertCount(2, $queries);
    $this->assertSame($rows, $result->rows);
    $this->assertSame(42, $result->totalCount);

    // Data query.
    $dataQuery = $queries[0];
    $this->assertSame(10, $dataQuery->limit);
    $this->assertSame(20, $dataQuery->offset);
    $this->assertEquals([(object) ['property' => 'age', 'order' => 'desc']], $dataQuery->sorts);
    $this->assertEquals(
      [(object) ['property' => 'city', 'value' => 'Denver', 'operator' => '=']],
      $dataQuery->conditions
    );
    $this->assertSame(['name', 'age'], $dataQuery->properties);
    $this->assertFalse($dataQuery->count);

    // Count query keeps conditions but drops limit, sort, and properties.
    $countQuery = $queries[1];
    $this->assertTrue($countQuery->count);
    $this->assertEquals(
      [(object) ['property' => 'city', 'value' => 'Denver', 'operator' => '=']],
      $countQuery->conditions
    );
    $this->assertSame([], $countQuery->sorts);
    $this->assertSame([], $countQuery->properties);
  }

  /**
   * Ascending sort and zero limit/offset are handled.
   */
  public function testFetchDataAscendingNoLimit(): void {
    $queries = [];
    $storage = $this->createMock(DatabaseTableInterface::class);
    $storage->method('query')->willReturnCallback(function ($query) use (&$queries) {
      $queries[] = $query;
      return $query->count ? [(object) ['expression' => '0']] : [];
    });

    $result = $this->getDataSource($storage)->fetchData('abc__1', 0, 0, 'name', 'asc');

    $this->assertNull($queries[0]->limit);
    $this->assertSame(0, $queries[0]->offset);
    $this->assertEquals([(object) ['property' => 'name', 'order' => 'asc']], $queries[0]->sorts);
    $this->assertSame(0, $result->totalCount);
  }

  /**
   * FetchData for an unknown resource returns an empty result.
   */
  public function testFetchDataMissingStorage(): void {
    $dataSource = $this->getDataSource(new \InvalidArgumentException('No datastore storage found.'));
    $result = $dataSource->fetchData('missing__1', 10, 0, NULL, 'asc');
    $this->assertSame([], $result->rows);
    $this->assertSame(0, $result->totalCount);
  }

  /**
   * Storage lookups are memoized per resource id.
   */
  public function testStorageMemoization(): void {
    $storage = $this->createMock(DatabaseTableInterface::class);
    $storage->method('getSchema')->willReturn(['fields' => ['name' => []]]);
    $storage->method('query')->willReturnCallback(
      fn ($query) => $query->count ? [(object) ['expression' => '1']] : []
    );

    $calls = [];
    $dataSource = $this->getDataSource($storage, $calls);
    $dataSource->getSchema('abc__1');
    $dataSource->fetchData('abc__1', 10, 0, NULL, 'asc');

    $this->assertCount(1, $calls);
  }

}
