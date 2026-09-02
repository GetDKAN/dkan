<?php

namespace Drupal\Tests\dkan_datastore_preview\Unit\Service;

use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\dkan_datastore_preview\DataSource\DataSourceInterface;
use Drupal\dkan_datastore_preview\DataSource\DataSourceResult;
use Drupal\dkan_datastore_preview\Service\DataPreviewBuilder;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @covers \Drupal\dkan_datastore_preview\Service\DataPreviewBuilder
 * @coversDefaultClass \Drupal\dkan_datastore_preview\Service\DataPreviewBuilder
 *
 * @group dkan
 * @group dkan_datastore_preview
 * @group unit
 */
class DataPreviewBuilderTest extends UnitTestCase {

  /**
   * Calls recorded by the fake data source.
   *
   * @var array
   */
  public array $fetchCalls = [];

  /**
   * Get a fake data source over an in-memory data set.
   */
  protected function getDataSource(int $rowCount = 30): DataSourceInterface {
    $rows = [];
    for ($i = 1; $i <= $rowCount; $i++) {
      $rows[] = (object) [
        'name' => sprintf('person_%02d', $i),
        'age' => 20 + ($i * 7) % 50,
      ];
    }
    $test = $this;

    return new class ($rows, $test) implements DataSourceInterface {

      public function __construct(protected array $rows, protected $test) {}

      /**
       * {@inheritdoc}
       */
      public function getSchema(string $resource_id): array {
        if ($resource_id === 'missing__1') {
          return [];
        }
        return [
          'fields' => [
            'name' => ['type' => 'text', 'description' => 'Name'],
            'age' => ['type' => 'int'],
          ],
        ];
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
        $this->test->fetchCalls[] = [
          'limit' => $limit,
          'offset' => $offset,
          'sort_field' => $sort_field,
          'sort_direction' => $sort_direction,
        ];
        $rows = $this->rows;
        if ($sort_field) {
          usort($rows, fn ($a, $b) => $sort_direction === 'desc'
            ? $b->{$sort_field} <=> $a->{$sort_field}
            : $a->{$sort_field} <=> $b->{$sort_field});
        }
        return new DataSourceResult(array_slice($rows, $offset, $limit), count($this->rows));
      }

    };
  }

  /**
   * Get a builder whose request contains the given query parameters.
   */
  protected function getBuilder(array $query = []): DataPreviewBuilder {
    $requestStack = new RequestStack();
    $requestStack->push(Request::create('/node/1', 'GET', $query));
    $builder = new DataPreviewBuilder($requestStack, $this->createMock(PagerManagerInterface::class));
    $builder->setStringTranslation($this->getStringTranslationStub());
    return $builder;
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->fetchCalls = [];
  }

  /**
   * An empty schema yields NULL so the caller can render a status message.
   */
  public function testEmptySchemaReturnsNull(): void {
    $build = $this->getBuilder()->build($this->getDataSource(), 'missing__1');
    $this->assertNull($build);
  }

  /**
   * Basic render array structure and defaults.
   */
  public function testBuildDefaults(): void {
    $build = $this->getBuilder()->build($this->getDataSource(), 'abc__1', ['query_prefix' => 'dp0_']);

    $this->assertSame('dkan_datastore_preview', $build['#theme']);
    $this->assertCount(25, $build['#table']['#rows']);
    $this->assertSame('person_01', $build['#table']['#rows'][0][0]);
    $this->assertSame(
      [
        'url.query_args:dp0_order',
        'url.query_args:dp0_sort',
        'url.query_args:dp0_page_size',
        'url.query_args:page',
      ],
      $build['#cache']['contexts']
    );
    $this->assertSame(0, $build['#pager']['#element']);
    $this->assertSame(['dp0_page_size' => 25], $build['#pager']['#parameters']);
    $this->assertSame([['limit' => 25, 'offset' => 0, 'sort_field' => NULL, 'sort_direction' => 'asc']], $this->fetchCalls);
  }

  /**
   * Header cells link to prefixed sort parameters with the page reset.
   */
  public function testHeaderSortLinks(): void {
    $build = $this->getBuilder(['page' => '2,3'])
      ->build($this->getDataSource(), 'abc__1', ['query_prefix' => 'dp1_', 'pager_element' => 1]);

    $header = $build['#table']['#header'];
    $this->assertCount(2, $header);

    // Labels come from the schema description, falling back to machine name.
    $this->assertSame('Name', $header[0]['data']['link']['#title']);
    $this->assertSame('age', $header[1]['data']['link']['#title']);

    $url = $header[0]['data']['link']['#url'];
    $query = $url->getOption('query');
    $this->assertSame('name', $query['dp1_order']);
    $this->assertSame('asc', $query['dp1_sort']);
    // Only this table's page position resets; table 0 keeps page 2.
    $this->assertSame('2,0', $query['page']);
    // Links are path-based, not route-based, so they work without a route.
    $this->assertFalse($url->isRouted());
    $this->assertSame('base:node/1', $url->getUri());
    $this->assertSame(
      'Sort by Name, ascending',
      (string) $header[0]['data']['link']['#attributes']['aria-label']
    );
  }

  /**
   * Caption and select id are derived from the options.
   */
  public function testCaptionAndSelectId(): void {
    $build = $this->getBuilder()
      ->build($this->getDataSource(), 'abc__1', ['query_prefix' => 'dp2_', 'caption' => 'Preview: a.csv']);
    $this->assertSame('Preview: a.csv', $build['#table']['#caption']);
    $this->assertSame('dkan-datastore-preview-dp2-page-size', $build['#page_size_form']['id']);
  }

  /**
   * The active column toggles direction and gets sort indicators.
   */
  public function testActiveSortColumn(): void {
    $build = $this->getBuilder(['dp0_order' => 'age', 'dp0_sort' => 'desc'])
      ->build($this->getDataSource(), 'abc__1', ['query_prefix' => 'dp0_']);

    $header = $build['#table']['#header'];
    $this->assertSame('descending', $header[1]['aria-sort']);
    $this->assertSame(['is-active'], $header[1]['class']);
    $this->assertSame('desc', $header[1]['data']['indicator']['#style']);
    // Clicking the active desc column toggles back to asc.
    $this->assertSame('asc', $header[1]['data']['link']['#url']->getOption('query')['dp0_sort']);
    // The inactive column is unmarked and links to asc.
    $this->assertArrayNotHasKey('aria-sort', $header[0]);
    $this->assertSame('asc', $header[0]['data']['link']['#url']->getOption('query')['dp0_sort']);

    $this->assertSame('desc', $this->fetchCalls[0]['sort_direction']);
    $this->assertSame('age', $this->fetchCalls[0]['sort_field']);
  }

  /**
   * Another table's sort parameters do not leak into this table.
   */
  public function testSortIsolationBetweenTables(): void {
    $this->getBuilder(['dp1_order' => 'age', 'dp1_sort' => 'desc'])
      ->build($this->getDataSource(), 'abc__1', ['query_prefix' => 'dp0_']);

    $this->assertNull($this->fetchCalls[0]['sort_field']);
  }

  /**
   * Sort parameters not matching a schema column fall back to defaults.
   */
  public function testInvalidSortFallsBack(): void {
    $this->getBuilder(['dp0_order' => 'evil_column', 'dp0_sort' => 'desc'])
      ->build($this->getDataSource(), 'abc__1', ['query_prefix' => 'dp0_']);
    $this->assertNull($this->fetchCalls[0]['sort_field']);

    $this->fetchCalls = [];
    $this->getBuilder(['dp0_order' => 'evil_column'])
      ->build($this->getDataSource(), 'abc__1', [
        'query_prefix' => 'dp0_',
        'default_sort' => 'name',
        'default_sort_direction' => 'desc',
      ]);
    $this->assertSame('name', $this->fetchCalls[0]['sort_field']);
    $this->assertSame('desc', $this->fetchCalls[0]['sort_direction']);
  }

  /**
   * Invalid page sizes fall back to the default.
   */
  public function testInvalidPageSizeFallsBack(): void {
    $this->getBuilder(['dp0_page_size' => '9999'])
      ->build($this->getDataSource(), 'abc__1', ['query_prefix' => 'dp0_']);
    $this->assertSame(25, $this->fetchCalls[0]['limit']);
  }

  /**
   * A valid page size from the query is applied.
   */
  public function testPageSizeFromQuery(): void {
    $this->getBuilder(['dp0_page_size' => '10'])
      ->build($this->getDataSource(), 'abc__1', ['query_prefix' => 'dp0_']);
    $this->assertSame(10, $this->fetchCalls[0]['limit']);
  }

  /**
   * The comma-separated page parameter is resolved per pager element.
   */
  public function testPagePerElement(): void {
    $this->getBuilder(['page' => '2,1', 'dp1_page_size' => '10'])
      ->build($this->getDataSource(), 'abc__1', ['query_prefix' => 'dp1_', 'pager_element' => 1]);
    $this->assertSame(10, $this->fetchCalls[0]['offset']);
  }

  /**
   * An out-of-range page is clamped to the last page with a refetch.
   */
  public function testPageClamping(): void {
    $build = $this->getBuilder(['page' => '9'])
      ->build($this->getDataSource(5), 'abc__1', ['query_prefix' => 'dp0_']);

    $this->assertCount(2, $this->fetchCalls);
    $this->assertSame(225, $this->fetchCalls[0]['offset']);
    $this->assertSame(0, $this->fetchCalls[1]['offset']);
    $this->assertCount(5, $build['#table']['#rows']);
    $this->assertSame('Showing 1-5 of 5 results', (string) $build['#result_summary']['#value']);
  }

  /**
   * Result summary reports the window; empty results say "No results".
   */
  public function testResultSummary(): void {
    $build = $this->getBuilder(['page' => '1'])
      ->build($this->getDataSource(), 'abc__1', ['query_prefix' => 'dp0_']);
    $this->assertSame('Showing 26-30 of 30 results', (string) $build['#result_summary']['#value']);

    $build = $this->getBuilder()->build($this->getDataSource(0), 'abc__1');
    $this->assertSame('No results', (string) $build['#result_summary']['#value']);
  }

  /**
   * The page size form carries foreign params but not its own.
   */
  public function testPageSizeFormHiddenParams(): void {
    $build = $this->getBuilder([
      'dp0_page_size' => '10',
      'dp1_order' => 'age',
      'page' => '1,2',
      'foo' => 'bar',
    ])->build($this->getDataSource(), 'abc__1', ['query_prefix' => 'dp0_']);

    $form = $build['#page_size_form'];
    $this->assertSame('dp0_page_size', $form['param_name']);
    $this->assertSame(10, $form['current']);
    $this->assertSame('/node/1', $form['action']);
    $this->assertArrayNotHasKey('dp0_page_size', $form['hidden']);
    $this->assertSame('age', $form['hidden']['dp1_order']);
    $this->assertSame('bar', $form['hidden']['foo']);
    // Own page position reset, other table's preserved.
    $this->assertSame('0,2', $form['hidden']['page']);
  }

  /**
   * The columns option restricts headers and cells.
   */
  public function testColumnsOption(): void {
    $build = $this->getBuilder()
      ->build($this->getDataSource(), 'abc__1', ['columns' => ['age']]);
    $this->assertCount(1, $build['#table']['#header']);
    $this->assertSame('age', $build['#table']['#header'][0]['data']['link']['#title']);
  }

}
