<?php

namespace Drupal\Tests\datastore_data_preview\Unit\DataSource;

use Drupal\datastore_data_preview\DataSource\ArrayDataSource;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\datastore_data_preview\DataSource\ArrayDataSource
 * @group datastore_data_preview
 */
class ArrayDataSourceTest extends TestCase {

  /**
   * Sample rows used across tests.
   */
  protected function getSampleRows(): array {
    return [
      ['name' => 'Alice', 'age' => 30, 'city' => 'Denver'],
      ['name' => 'Bob', 'age' => 25, 'city' => 'Austin'],
      ['name' => 'Carol', 'age' => 35, 'city' => 'Denver'],
      ['name' => 'Dave', 'age' => 28, 'city' => 'Boston'],
      ['name' => 'Eve', 'age' => 22, 'city' => 'Austin'],
    ];
  }

  /**
   * @covers ::fetchData
   */
  public function testBasicFetchReturnsAllRows(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('ignored', 0, 0, NULL, 'asc');

    $this->assertCount(5, $result->rows);
    $this->assertEquals(5, $result->totalCount);
    $this->assertEquals('Alice', $result->rows[0]->name);
  }

  /**
   * @covers ::fetchData
   */
  public function testLimitAndOffset(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 2, 1, NULL, 'asc');

    $this->assertCount(2, $result->rows);
    $this->assertEquals(5, $result->totalCount);
    $this->assertEquals('Bob', $result->rows[0]->name);
    $this->assertEquals('Carol', $result->rows[1]->name);
  }

  /**
   * @covers ::fetchData
   */
  public function testOffsetOnly(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 3, NULL, 'asc');

    $this->assertCount(2, $result->rows);
    $this->assertEquals('Dave', $result->rows[0]->name);
  }

  /**
   * @covers ::fetchData
   */
  public function testSortAsc(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, 'age', 'asc');

    $this->assertEquals('Eve', $result->rows[0]->name);
    $this->assertEquals('Carol', $result->rows[4]->name);
  }

  /**
   * @covers ::fetchData
   */
  public function testSortDesc(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, 'age', 'desc');

    $this->assertEquals('Carol', $result->rows[0]->name);
    $this->assertEquals('Eve', $result->rows[4]->name);
  }

  /**
   * @covers ::fetchData
   */
  public function testSortNullFieldPreservesOrder(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, NULL, 'asc');

    $this->assertEquals('Alice', $result->rows[0]->name);
    $this->assertEquals('Eve', $result->rows[4]->name);
  }

  /**
   * @covers ::fetchData
   * @covers ::evaluateCondition
   */
  public function testConditionEquals(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, NULL, 'asc', [
      ['property' => 'city', 'value' => 'Denver'],
    ]);

    $this->assertCount(2, $result->rows);
    $this->assertEquals(2, $result->totalCount);
    $this->assertEquals('Alice', $result->rows[0]->name);
    $this->assertEquals('Carol', $result->rows[1]->name);
  }

  /**
   * @covers ::evaluateCondition
   */
  public function testConditionGreaterThan(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, NULL, 'asc', [
      ['property' => 'age', 'value' => 28, 'operator' => '>'],
    ]);

    $this->assertCount(2, $result->rows);
    $names = array_map(fn($r) => $r->name, $result->rows);
    $this->assertContains('Alice', $names);
    $this->assertContains('Carol', $names);
  }

  /**
   * @covers ::evaluateCondition
   */
  public function testConditionNotEqual(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, NULL, 'asc', [
      ['property' => 'city', 'value' => 'Denver', 'operator' => '<>'],
    ]);

    $this->assertCount(3, $result->rows);
  }

  /**
   * @covers ::evaluateCondition
   */
  public function testConditionIn(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, NULL, 'asc', [
      ['property' => 'city', 'value' => ['Austin', 'Boston'], 'operator' => 'IN'],
    ]);

    $this->assertCount(3, $result->rows);
    $this->assertEquals(3, $result->totalCount);
  }

  /**
   * @covers ::evaluateCondition
   */
  public function testConditionNotIn(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, NULL, 'asc', [
      ['property' => 'city', 'value' => ['Austin', 'Boston'], 'operator' => 'NOT IN'],
    ]);

    $this->assertCount(2, $result->rows);
  }

  /**
   * @covers ::evaluateCondition
   */
  public function testConditionLike(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, NULL, 'asc', [
      ['property' => 'name', 'value' => '%a%', 'operator' => 'LIKE'],
    ]);

    // Case insensitive: Alice, Carol, Dave all contain 'a'.
    $names = array_map(fn($r) => $r->name, $result->rows);
    $this->assertContains('Alice', $names);
    $this->assertContains('Carol', $names);
    $this->assertContains('Dave', $names);
  }

  /**
   * @covers ::evaluateCondition
   */
  public function testConditionLessThanOrEqual(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, NULL, 'asc', [
      ['property' => 'age', 'value' => 25, 'operator' => '<='],
    ]);

    $this->assertCount(2, $result->rows);
    $names = array_map(fn($r) => $r->name, $result->rows);
    $this->assertContains('Bob', $names);
    $this->assertContains('Eve', $names);
  }

  /**
   * @covers ::evaluateCondition
   */
  public function testMultipleConditions(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, NULL, 'asc', [
      ['property' => 'city', 'value' => 'Austin'],
      ['property' => 'age', 'value' => 25, 'operator' => '<='],
    ]);

    $this->assertCount(2, $result->rows);
  }

  /**
   * @covers ::fetchData
   */
  public function testPropertyFiltering(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, NULL, 'asc', [], ['name', 'age']);

    $this->assertCount(5, $result->rows);
    $first = $result->rows[0];
    $this->assertTrue(property_exists($first, 'name'));
    $this->assertTrue(property_exists($first, 'age'));
    $this->assertFalse(property_exists($first, 'city'));
  }

  /**
   * @covers ::inferSchema
   */
  public function testSchemaAutoInference(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 1, 0, NULL, 'asc');

    $schema = $result->schema;
    $this->assertArrayHasKey('fields', $schema);
    $this->assertEquals('text', $schema['fields']['name']['type']);
    $this->assertEquals('number', $schema['fields']['age']['type']);
    $this->assertEquals('text', $schema['fields']['city']['type']);
    $this->assertEquals('Name', $schema['fields']['name']['description']);
    $this->assertEquals('Age', $schema['fields']['age']['description']);
  }

  /**
   * @covers ::__construct
   */
  public function testExplicitSchemaPassthrough(): void {
    $schema = [
      'fields' => [
        'name' => ['type' => 'text', 'description' => 'Full Name'],
        'age' => ['type' => 'integer', 'description' => 'Years Old'],
        'city' => ['type' => 'text', 'description' => 'Hometown'],
      ],
    ];
    $source = new ArrayDataSource($this->getSampleRows(), $schema);
    $result = $source->fetchData('x', 1, 0, NULL, 'asc');

    $this->assertEquals('Full Name', $result->schema['fields']['name']['description']);
    $this->assertEquals('integer', $result->schema['fields']['age']['type']);
  }

  /**
   * @covers ::fetchData
   */
  public function testEmptyRows(): void {
    $source = new ArrayDataSource([]);
    $result = $source->fetchData('x', 0, 0, NULL, 'asc');

    $this->assertCount(0, $result->rows);
    $this->assertEquals(0, $result->totalCount);
    $this->assertEquals(['fields' => []], $result->schema);
  }

  /**
   * @covers ::fetchData
   */
  public function testTotalCountReflectsFilteredNotPaged(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    // Filter to Denver (2 rows), then page to 1 row.
    $result = $source->fetchData('x', 1, 0, NULL, 'asc', [
      ['property' => 'city', 'value' => 'Denver'],
    ]);

    $this->assertCount(1, $result->rows);
    $this->assertEquals(2, $result->totalCount);
  }

  /**
   * @covers ::inferSchema
   */
  public function testSchemaInfersUnderscoreDescription(): void {
    $rows = [['first_name' => 'Alice', 'total_score' => 95]];
    $source = new ArrayDataSource($rows);
    $result = $source->fetchData('x', 0, 0, NULL, 'asc');

    $this->assertEquals('First name', $result->schema['fields']['first_name']['description']);
    $this->assertEquals('Total score', $result->schema['fields']['total_score']['description']);
  }

  /**
   * @covers ::fetchData
   */
  public function testSortOnNonExistentField(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, 'nonexistent', 'asc');

    // Order should be unchanged (original insertion order).
    $this->assertEquals('Alice', $result->rows[0]->name);
    $this->assertEquals('Eve', $result->rows[4]->name);
  }

  /**
   * @covers ::evaluateCondition
   */
  public function testConditionOnMissingProperty(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, NULL, 'asc', [
      ['property' => 'nonexistent', 'value' => NULL, 'operator' => '='],
    ]);

    // NULL == NULL is true, so all rows match.
    $this->assertCount(5, $result->rows);
  }

  /**
   * @covers ::evaluateCondition
   */
  public function testConditionUnknownOperator(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, NULL, 'asc', [
      ['property' => 'age', 'value' => 25, 'operator' => 'BETWEEN'],
    ]);

    // Unknown operator defaults to TRUE — no rows filtered.
    $this->assertCount(5, $result->rows);
  }

  /**
   * @covers ::evaluateCondition
   */
  public function testConditionNotEqualBang(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $resultBang = $source->fetchData('x', 0, 0, NULL, 'asc', [
      ['property' => 'city', 'value' => 'Denver', 'operator' => '!='],
    ]);
    $resultAngle = $source->fetchData('x', 0, 0, NULL, 'asc', [
      ['property' => 'city', 'value' => 'Denver', 'operator' => '<>'],
    ]);

    $this->assertCount(3, $resultBang->rows);
    $this->assertEquals($resultAngle->totalCount, $resultBang->totalCount);
  }

  /**
   * @covers ::evaluateCondition
   */
  public function testConditionInWithEmptyArray(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, NULL, 'asc', [
      ['property' => 'city', 'value' => [], 'operator' => 'IN'],
    ]);

    $this->assertCount(0, $result->rows);
    $this->assertEquals(0, $result->totalCount);
  }

  /**
   * @covers ::evaluateCondition
   */
  public function testConditionNotInWithEmptyArray(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, NULL, 'asc', [
      ['property' => 'city', 'value' => [], 'operator' => 'NOT IN'],
    ]);

    $this->assertCount(5, $result->rows);
  }

  /**
   * @covers ::evaluateCondition
   */
  public function testLikeWithNoWildcard(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, NULL, 'asc', [
      ['property' => 'name', 'value' => 'Alice', 'operator' => 'LIKE'],
    ]);

    $this->assertCount(1, $result->rows);
    $this->assertEquals('Alice', $result->rows[0]->name);
  }

  /**
   * @covers ::fetchData
   */
  public function testOffsetBeyondTotal(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 10, 100, NULL, 'asc');

    $this->assertCount(0, $result->rows);
    $this->assertEquals(5, $result->totalCount);
  }

  /**
   * @covers ::inferSchema
   */
  public function testSchemaInferenceNullValue(): void {
    $rows = [['name' => NULL, 'age' => 30]];
    $source = new ArrayDataSource($rows);
    $result = $source->fetchData('x', 0, 0, NULL, 'asc');

    // NULL is not int/float, so type should be 'text'.
    $this->assertEquals('text', $result->schema['fields']['name']['type']);
    $this->assertEquals('number', $result->schema['fields']['age']['type']);
  }

  /**
   * @covers ::inferSchema
   */
  public function testSchemaInferenceMixedTypes(): void {
    $rows = [
      ['value' => 'hello'],
      ['value' => 42],
    ];
    $source = new ArrayDataSource($rows);
    $result = $source->fetchData('x', 0, 0, NULL, 'asc');

    // Only first row is inspected — string → text.
    $this->assertEquals('text', $result->schema['fields']['value']['type']);
  }

  /**
   * @covers ::fetchData
   */
  public function testPropertyFilteringNonExistentColumn(): void {
    $source = new ArrayDataSource($this->getSampleRows());
    $result = $source->fetchData('x', 0, 0, NULL, 'asc', [], ['name', 'nonexistent']);

    $first = $result->rows[0];
    $this->assertTrue(property_exists($first, 'name'));
    // 'nonexistent' is not in schema, so it's excluded by array_intersect.
    $this->assertFalse(property_exists($first, 'nonexistent'));
    $this->assertFalse(property_exists($first, 'age'));
    $this->assertFalse(property_exists($first, 'city'));
  }

}
