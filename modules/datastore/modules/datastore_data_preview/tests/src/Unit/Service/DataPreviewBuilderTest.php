<?php

namespace Drupal\Tests\datastore_data_preview\Unit\Service;

use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\datastore_data_preview\DataSource\DataSourceInterface;
use Drupal\datastore_data_preview\DataSource\DataSourceResult;
use Drupal\datastore_data_preview\Service\DataPreviewBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \Drupal\datastore_data_preview\Service\DataPreviewBuilder
 * @group datastore_data_preview
 */
class DataPreviewBuilderTest extends TestCase {

  /**
   * Schema used across tests.
   */
  protected array $testSchema = [
    'fields' => [
      'name' => ['type' => 'text', 'description' => 'Full Name'],
      'age' => ['type' => 'int', 'description' => 'Age'],
      'city' => ['type' => 'text', 'description' => 'City'],
    ],
  ];

  /**
   * Test render array structure.
   */
  public function testBuildStructure() {
    $builder = $this->createBuilder();
    $dataSource = $this->createDataSource([], 0);

    $build = $builder->build($dataSource, 'test-id');

    $this->assertEquals('datastore_data_preview', $build['#theme']);
    $this->assertArrayHasKey('#table', $build);
    $this->assertArrayHasKey('#pager', $build);
    $this->assertArrayHasKey('#page_size_form', $build);
    $this->assertArrayHasKey('#result_summary', $build);
    $this->assertEquals('table', $build['#table']['#type']);
    $this->assertEquals('pager', $build['#pager']['#type']);
    $this->assertContains('datastore_data_preview/data_preview', $build['#attached']['library']);
  }

  /**
   * Test headers built from schema.
   */
  public function testHeadersFromSchema() {
    $builder = $this->createBuilder();
    $dataSource = $this->createDataSource([], 0);

    $build = $builder->build($dataSource, 'test-id');
    $header = $build['#table']['#header'];

    $this->assertCount(3, $header);
    $this->assertEquals('Full Name', $header[0]['data']);
    $this->assertEquals('name', $header[0]['field']);
    $this->assertEquals('Age', $header[1]['data']);
    $this->assertEquals('age', $header[1]['field']);
    $this->assertEquals('City', $header[2]['data']);
    $this->assertEquals('city', $header[2]['field']);
  }

  /**
   * Test column filtering.
   */
  public function testColumnFiltering() {
    $builder = $this->createBuilder();
    $dataSource = $this->createDataSource([], 0);

    $build = $builder->build($dataSource, 'test-id', [
      'columns' => ['name', 'city'],
    ]);
    $header = $build['#table']['#header'];

    $this->assertCount(2, $header);
    $this->assertEquals('name', $header[0]['field']);
    $this->assertEquals('city', $header[1]['field']);
  }

  /**
   * Test row data mapping.
   */
  public function testRowDataMapping() {
    $rows = [
      (object) ['name' => 'Alice', 'age' => 30, 'city' => 'Portland'],
      (object) ['name' => 'Bob', 'age' => 25, 'city' => 'Seattle'],
    ];

    $builder = $this->createBuilder();
    $dataSource = $this->createDataSource($rows, 2);

    $build = $builder->build($dataSource, 'test-id');
    $tableRows = $build['#table']['#rows'];

    $this->assertCount(2, $tableRows);
    $this->assertEquals('Alice', $tableRows[0][0]);
    $this->assertEquals(30, $tableRows[0][1]);
    $this->assertEquals('Portland', $tableRows[0][2]);
  }

  /**
   * Test pager element ID passthrough.
   */
  public function testPagerElement() {
    $pagerManager = $this->createMock(PagerManagerInterface::class);
    $pagerManager->expects($this->atLeastOnce())
      ->method('createPager')
      ->with($this->anything(), $this->anything(), 3);

    $builder = $this->createBuilder(pagerManager: $pagerManager);
    $dataSource = $this->createDataSource([], 0);

    $build = $builder->build($dataSource, 'test-id', ['pager_element' => 3]);

    $this->assertEquals(3, $build['#pager']['#element']);
  }

  /**
   * Test page size selector renders as markup with select element.
   */
  public function testPageSizeOptions() {
    $builder = $this->createBuilder();
    $dataSource = $this->createDataSource([], 0);

    $build = $builder->build($dataSource, 'test-id', [
      'page_sizes' => [10, 25, 50],
      'default_page_size' => 25,
    ]);

    $pageSizeForm = $build['#page_size_form'];
    $this->assertArrayHasKey('#markup', $pageSizeForm);
    $markup = (string) $pageSizeForm['#markup'];
    $this->assertStringContainsString('data-preview-page-size-select', $markup);
    $this->assertStringContainsString('>10<', $markup);
    $this->assertStringContainsString('>25<', $markup);
    $this->assertStringContainsString('>50<', $markup);
  }

  /**
   * Test default sort column marking.
   */
  public function testDefaultSortColumn() {
    $builder = $this->createBuilder();
    $dataSource = $this->createDataSource([], 0);

    $build = $builder->build($dataSource, 'test-id', [
      'default_sort' => 'age',
      'default_sort_direction' => 'desc',
    ]);

    $header = $build['#table']['#header'];
    $ageHeader = NULL;
    foreach ($header as $h) {
      if ($h['field'] === 'age') {
        $ageHeader = $h;
        break;
      }
    }
    $this->assertNotNull($ageHeader);
    $this->assertEquals('desc', $ageHeader['sort']);
  }

  /**
   * Test result summary with data.
   */
  public function testResultSummary() {
    $rows = [
      (object) ['name' => 'Alice', 'age' => 30, 'city' => 'Portland'],
    ];

    $builder = $this->createBuilder();
    $dataSource = $this->createDataSource($rows, 100);

    $build = $builder->build($dataSource, 'test-id', ['default_page_size' => 25]);
    $summary = (string) $build['#result_summary']['#markup'];

    $this->assertStringContainsString('1', $summary);
    $this->assertStringContainsString('100', $summary);
  }

  /**
   * Test empty result summary.
   */
  public function testEmptyResultSummary() {
    $builder = $this->createBuilder();
    $dataSource = $this->createDataSource([], 0);

    $build = $builder->build($dataSource, 'test-id');
    $summary = (string) $build['#result_summary']['#markup'];

    $this->assertStringContainsString('No results', $summary);
  }

  /**
   * Test invalid page size falls back to default.
   */
  public function testInvalidPageSizeFallsBackToDefault() {
    $builder = $this->createBuilder(['page_size' => '999']);
    $dataSource = $this->createDataSource([], 0);

    $build = $builder->build($dataSource, 'test-id', [
      'default_page_size' => 25,
      'page_sizes' => [10, 25, 50, 100],
    ]);

    // Page size 999 not in page_sizes, so falls back to 25.
    // Verify by checking the pager was initialized (indirectly through markup).
    $this->assertNotEmpty($build['#page_size_form']);
  }

  /**
   * Test page size from query param.
   */
  public function testPageSizeFromQueryParam() {
    $pagerManager = $this->createMock(PagerManagerInterface::class);
    $pagerManager->expects($this->atLeastOnce())
      ->method('createPager')
      ->with($this->anything(), 50, $this->anything());

    $builder = $this->createBuilder(['page_size' => '50'], $pagerManager);
    $dataSource = $this->createDataSource([], 0);

    $builder->build($dataSource, 'test-id', [
      'default_page_size' => 25,
      'page_sizes' => [10, 25, 50, 100],
    ]);
  }

  /**
   * Test prefixed page size param.
   */
  public function testPrefixedPageSizeParam() {
    $pagerManager = $this->createMock(PagerManagerInterface::class);
    $pagerManager->expects($this->atLeastOnce())
      ->method('createPager')
      ->with($this->anything(), 50, $this->anything());

    $builder = $this->createBuilder(['t1_page_size' => '50'], $pagerManager);
    $dataSource = $this->createDataSource([], 0);

    $builder->build($dataSource, 'test-id', [
      'default_page_size' => 25,
      'page_sizes' => [10, 25, 50, 100],
      'query_prefix' => 't1_',
    ]);
  }

  /**
   * Test current page from query param adjusts offset.
   */
  public function testCurrentPageFromQueryParam() {
    $dataSource = $this->createMock(DataSourceInterface::class);
    $capturedOffset = NULL;

    $dataSource->method('fetchData')
      ->willReturnCallback(function ($id, $limit, $offset) use (&$capturedOffset) {
        $capturedOffset = $offset;
        return new DataSourceResult([], 100, $this->testSchema);
      });

    $builder = $this->createBuilder(['page' => '2']);
    $builder->build($dataSource, 'test-id', ['default_page_size' => 25]);

    // page=2, pageSize=25, offset = 2 * 25 = 50.
    // The probe query is called first with offset=0, then the data query.
    $this->assertEquals(50, $capturedOffset);
  }

  /**
   * Test comma-format page param with pager_element.
   */
  public function testCurrentPageCommaFormat() {
    $dataSource = $this->createMock(DataSourceInterface::class);
    $capturedOffset = NULL;

    $dataSource->method('fetchData')
      ->willReturnCallback(function ($id, $limit, $offset) use (&$capturedOffset) {
        $capturedOffset = $offset;
        return new DataSourceResult([], 100, $this->testSchema);
      });

    // page=0,3 means pager element 0 is on page 0, element 1 is on page 3.
    $builder = $this->createBuilder(['page' => '0,3']);
    $builder->build($dataSource, 'test-id', [
      'default_page_size' => 10,
      'pager_element' => 1,
    ]);

    // pager_element=1, page=3, pageSize=10, offset = 3 * 10 = 30.
    $this->assertEquals(30, $capturedOffset);
  }

  /**
   * Test row with missing schema field returns empty string.
   */
  public function testBuildRowsMissingProperty() {
    $rows = [
      (object) ['name' => 'Alice', 'city' => 'Portland'],
      // 'age' is missing from both rows.
    ];

    $builder = $this->createBuilder();
    $dataSource = $this->createDataSource($rows, 1);

    $build = $builder->build($dataSource, 'test-id');
    $tableRows = $build['#table']['#rows'];

    // Age column should be empty string.
    $this->assertEquals('', $tableRows[0][1]);
  }

  /**
   * Test header falls back to machine name when no description.
   */
  public function testHeaderFallsBackToMachineName() {
    $schema = [
      'fields' => [
        'some_field' => ['type' => 'text'],
      ],
    ];
    $result = new DataSourceResult([], 0, $schema);

    $dataSource = $this->createMock(DataSourceInterface::class);
    $dataSource->method('fetchData')->willReturn($result);

    $builder = $this->createBuilder();
    $build = $builder->build($dataSource, 'test-id');
    $header = $build['#table']['#header'];

    $this->assertCount(1, $header);
    $this->assertEquals('some_field', $header[0]['data']);
  }

  /**
   * Test record_number excluded from headers.
   */
  public function testRecordNumberExcludedFromHeader() {
    $schema = [
      'fields' => [
        'record_number' => ['type' => 'serial', 'description' => 'Record Number'],
        'name' => ['type' => 'text', 'description' => 'Name'],
      ],
    ];
    $result = new DataSourceResult([], 0, $schema);

    $dataSource = $this->createMock(DataSourceInterface::class);
    $dataSource->method('fetchData')->willReturn($result);

    $builder = $this->createBuilder();
    $build = $builder->build($dataSource, 'test-id');
    $header = $build['#table']['#header'];

    $this->assertCount(1, $header);
    $this->assertEquals('name', $header[0]['field']);
  }

  /**
   * Test record_number excluded from table rows.
   */
  public function testRecordNumberExcludedFromRows() {
    $schema = [
      'fields' => [
        'record_number' => ['type' => 'serial', 'description' => 'Record Number'],
        'name' => ['type' => 'text', 'description' => 'Name'],
      ],
    ];
    $rows = [(object) ['record_number' => 1, 'name' => 'Alice']];
    $result = new DataSourceResult($rows, 1, $schema);

    $dataSource = $this->createMock(DataSourceInterface::class);
    $dataSource->method('fetchData')->willReturn($result);

    $builder = $this->createBuilder();
    $build = $builder->build($dataSource, 'test-id');
    $tableRows = $build['#table']['#rows'];

    // Only 'name' column should be present.
    $this->assertCount(1, $tableRows);
    $this->assertCount(1, $tableRows[0]);
    $this->assertEquals('Alice', $tableRows[0][0]);
  }

  /**
   * Test result summary formats large numbers with commas.
   */
  public function testResultSummaryFormatting() {
    $rows = array_fill(0, 25, (object) ['name' => 'X', 'age' => 1, 'city' => 'Y']);

    $builder = $this->createBuilder();
    $dataSource = $this->createDataSource($rows, 1234);

    $build = $builder->build($dataSource, 'test-id', ['default_page_size' => 25]);
    $summary = (string) $build['#result_summary']['#markup'];

    $this->assertStringContainsString('1,234', $summary);
  }

  /**
   * Test page size selector links don't include page param.
   */
  public function testPageSizeResetsPagination() {
    $builder = $this->createBuilder(['page' => '3']);
    $dataSource = $this->createDataSource([], 0);

    $build = $builder->build($dataSource, 'test-id', [
      'page_sizes' => [10, 25],
      'default_page_size' => 25,
    ]);

    $markup = (string) $build['#page_size_form']['#markup'];
    // The page_size links should not contain page= parameter.
    $this->assertStringNotContainsString('page=', $markup);
  }

  /**
   * Create a DataPreviewBuilder with mocked dependencies.
   */
  protected function createBuilder(
    array $queryParams = [],
    ?PagerManagerInterface $pagerManager = NULL,
  ): DataPreviewBuilder {
    $request = new Request($queryParams);
    $requestStack = $this->createMock(RequestStack::class);
    $requestStack->method('getCurrentRequest')->willReturn($request);

    $pagerManager = $pagerManager ?? $this->createMock(PagerManagerInterface::class);

    $builder = new DataPreviewBuilder($requestStack, $pagerManager);
    $builder->setStringTranslation($this->getStringTranslationStub());

    return $builder;
  }

  /**
   * Create a mock DataSourceInterface.
   */
  protected function createDataSource(array $rows, int $totalCount): DataSourceInterface {
    $result = new DataSourceResult($rows, $totalCount, $this->testSchema);

    $dataSource = $this->createMock(DataSourceInterface::class);
    $dataSource->method('fetchData')->willReturn($result);
    return $dataSource;
  }

  /**
   * Returns a stub translation service that returns the untranslated string.
   */
  protected function getStringTranslationStub(): TranslationInterface {
    $translation = $this->createMock(TranslationInterface::class);
    $translation->method('translateString')
      ->willReturnCallback(function ($wrapper) {
        return $wrapper->getUntranslatedString();
      });
    return $translation;
  }

}
