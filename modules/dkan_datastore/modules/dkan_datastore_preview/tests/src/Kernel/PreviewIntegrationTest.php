<?php

namespace Drupal\Tests\dkan_datastore_preview\Kernel;

use Drupal\dkan_common\DataResource;
use Drupal\dkan_datastore\Service\ResourceLocalizer;
use Drupal\KernelTests\KernelTestBase;
use Procrastinator\Result;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the preview pipeline against a real imported datastore table.
 *
 * @covers \Drupal\dkan_datastore_preview\DataSource\DatabaseDataSource
 * @covers \Drupal\dkan_datastore_preview\Element\DataPreview
 * @covers \Drupal\dkan_datastore_preview\Service\DataPreviewBuilder
 *
 * @group dkan
 * @group dkan_datastore_preview
 * @group kernel
 */
class PreviewIntegrationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'node',
    'user',
    'dkan_common',
    'dkan_datastore',
    'dkan_metastore',
    'dkan_datastore_preview',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('resource_mapping');
  }

  /**
   * Register, localize, and import the fixture CSV; return its resource id.
   */
  protected function importFixture(): string {
    $path = 'file://' . dirname(__DIR__, 2) . '/data/preview_sample.csv';
    $resource = new DataResource($path, 'text/csv', DataResource::DEFAULT_SOURCE_PERSPECTIVE);

    /** @var \Drupal\dkan_metastore\ResourceMapper $mapper */
    $mapper = $this->container->get('dkan.metastore.resource_mapper');
    $mapper->register($resource);

    $identifier = $resource->getIdentifier();
    $version = $resource->getVersion();

    /** @var \Drupal\dkan_datastore\Service\ResourceLocalizer $localizer */
    $localizer = $this->container->get('dkan.datastore.service.resource_localizer');
    $result = $localizer->localizeTask($identifier, $version, FALSE);
    $this->assertEquals(Result::DONE, $result->getStatus(), $result->getError() ?? '');

    // Run the import job directly (as DatabaseTableTest does) instead of
    // DatastoreService::import(), whose cache invalidation needs metastore
    // node fields this kernel environment doesn't install.
    $local = $localizer->get($identifier, $version, ResourceLocalizer::LOCAL_FILE_PERSPECTIVE);
    $importJob = $this->container->get('dkan.datastore.service.factory.import')
      ->getInstance($local->getUniqueIdentifier(), ['resource' => $local])
      ->getImporter();
    $importResult = $importJob->run();
    $this->assertEquals(Result::DONE, $importResult->getStatus(), $importResult->getError() ?? '');

    return $identifier . '__' . $version;
  }

  /**
   * Push a request for the given path and query.
   */
  protected function pushRequest(string $uri, array $query = []): void {
    $request = Request::create($uri, 'GET', $query);
    $request->setSession($this->container->get('request_stack')->getCurrentRequest()->getSession());
    $this->container->get('request_stack')->push($request);
  }

  /**
   * The database data source reads schema and data from a real table.
   */
  public function testDatabaseDataSource(): void {
    $resourceId = $this->importFixture();

    /** @var \Drupal\dkan_datastore_preview\DataSource\DatabaseDataSource $dataSource */
    $dataSource = $this->container->get('dkan.datastore_preview.data_source.database');

    $schema = $dataSource->getSchema($resourceId);
    $this->assertSame(['name', 'age', 'city'], array_keys($schema['fields']));

    // Descending sort by age returns the maximum age first.
    $result = $dataSource->fetchData($resourceId, 10, 0, 'age', 'desc');
    $this->assertCount(10, $result->rows);
    $this->assertSame(30, $result->totalCount);
    $this->assertSame(69, (int) $result->rows[0]->age);

    // Offset paging with ascending name sort.
    $result = $dataSource->fetchData($resourceId, 10, 10, 'name', 'asc');
    $this->assertSame('person_11', $result->rows[0]->name);

    // Unknown resources yield empty schema and results, not exceptions.
    $this->assertSame([], $dataSource->getSchema('missing__123'));
    $this->assertSame(0, $dataSource->fetchData('missing__123', 10, 0, NULL, 'asc')->totalCount);
  }

  /**
   * The render element produces a sortable table for a real resource.
   */
  public function testElementRendersTable(): void {
    $resourceId = $this->importFixture();
    $this->pushRequest('/preview-test');

    $build = [
      '#type' => 'dkan_datastore_preview',
      '#resource_id' => $resourceId,
      '#pager_element' => 0,
      '#query_prefix' => 'dp0_',
      '#caption' => 'Preview: preview_sample.csv',
    ];
    $html = (string) $this->container->get('renderer')->renderInIsolation($build);

    $this->assertStringContainsString('<table', $html);
    $this->assertStringContainsString('<caption>Preview: preview_sample.csv</caption>', $html);
    $this->assertStringContainsString('person_01', $html);
    $this->assertStringContainsString('/preview-test?', $html);
    $this->assertStringContainsString('dp0_order=name', $html);
    $this->assertStringContainsString('aria-label="Sort by name, ascending"', $html);
    $this->assertStringContainsString('method="get"', $html);
    $this->assertStringContainsString('id="dkan-datastore-preview-dp0-page-size"', $html);
    $this->assertStringContainsString('Showing 1-25 of 30 results', $html);
  }

  /**
   * Sort and page size query parameters change the rendered table.
   */
  public function testElementRespectsQueryParameters(): void {
    $resourceId = $this->importFixture();
    $this->pushRequest('/preview-test', [
      'dp0_order' => 'age',
      'dp0_sort' => 'desc',
      'dp0_page_size' => '10',
    ]);

    $build = [
      '#type' => 'dkan_datastore_preview',
      '#resource_id' => $resourceId,
      '#pager_element' => 0,
      '#query_prefix' => 'dp0_',
    ];
    $html = (string) $this->container->get('renderer')->renderInIsolation($build);

    $this->assertStringContainsString('Showing 1-10 of 30 results', $html);
    $this->assertStringContainsString('aria-sort="descending"', $html);
    // The maximum age in the fixture sorts first.
    $this->assertMatchesRegularExpression('/<tbody[^>]*>.*?<td>69<\/td>/s', $html);
  }

  /**
   * A resource without a datastore table renders a status message.
   */
  public function testUnavailableResourceRendersMessage(): void {
    $this->pushRequest('/preview-test');

    $build = [
      '#type' => 'dkan_datastore_preview',
      '#resource_id' => 'missing__123',
    ];
    $html = (string) $this->container->get('renderer')->renderInIsolation($build);

    // An unknown resource gets the generic message, not "processing".
    $this->assertStringContainsString('Data preview is not yet available.', $html);
    $this->assertStringNotContainsString('still being processed', $html);
    $this->assertStringContainsString('dkan-datastore-preview__message', $html);
    $this->assertStringNotContainsString('<table', $html);
  }

}
