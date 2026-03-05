<?php

namespace Drupal\Tests\datastore_data_preview\Unit\Plugin\Block;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\datastore_data_preview\DataSource\ApiDataSource;
use Drupal\datastore_data_preview\DataSource\DatabaseDataSource;
use Drupal\datastore_data_preview\Plugin\Block\DataPreviewBlock;
use Drupal\datastore_data_preview\Service\DataPreviewBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \Drupal\datastore_data_preview\Plugin\Block\DataPreviewBlock
 * @group datastore_data_preview
 */
class DataPreviewBlockTest extends TestCase {

  /**
   * @covers ::build
   */
  public function testBuildWithNoResourceId(): void {
    $block = $this->createBlock(['resource_id' => '']);
    $build = $block->build();

    $this->assertArrayHasKey('#markup', $build);
    $this->assertStringContainsString('No resource ID configured', (string) $build['#markup']);
  }

  /**
   * @covers ::build
   */
  public function testBuildSelectsDatabaseSource(): void {
    $expectedBuild = ['#theme' => 'test'];
    $databaseSource = $this->createMock(DatabaseDataSource::class);

    $previewBuilder = $this->createMock(DataPreviewBuilder::class);
    $previewBuilder->expects($this->once())
      ->method('build')
      ->with(
        $this->identicalTo($databaseSource),
        'test-resource',
        $this->anything(),
      )
      ->willReturn($expectedBuild);

    $block = $this->createBlock([
      'resource_id' => 'test-resource',
      'data_source' => 'database',
      'columns' => '',
      'default_page_size' => 25,
      'default_sort' => '',
      'default_sort_direction' => 'asc',
    ], $previewBuilder, $databaseSource);

    $build = $block->build();
    $this->assertEquals($expectedBuild, $build);
  }

  /**
   * @covers ::build
   */
  public function testBuildSelectsApiSource(): void {
    $expectedBuild = ['#theme' => 'test'];
    $apiSource = $this->createMock(ApiDataSource::class);

    $previewBuilder = $this->createMock(DataPreviewBuilder::class);
    $previewBuilder->expects($this->once())
      ->method('build')
      ->with(
        $this->identicalTo($apiSource),
        'test-resource',
        $this->anything(),
      )
      ->willReturn($expectedBuild);

    $block = $this->createBlock([
      'resource_id' => 'test-resource',
      'data_source' => 'api',
      'columns' => '',
      'default_page_size' => 25,
      'default_sort' => '',
      'default_sort_direction' => 'asc',
    ], $previewBuilder, NULL, $apiSource);

    $build = $block->build();
    $this->assertEquals($expectedBuild, $build);
  }

  /**
   * @covers ::build
   */
  public function testBuildDefaultsToDatabaseSource(): void {
    $databaseSource = $this->createMock(DatabaseDataSource::class);

    $previewBuilder = $this->createMock(DataPreviewBuilder::class);
    $previewBuilder->expects($this->once())
      ->method('build')
      ->with(
        $this->identicalTo($databaseSource),
        'test-resource',
        $this->anything(),
      )
      ->willReturn(['#theme' => 'test']);

    $block = $this->createBlock([
      'resource_id' => 'test-resource',
      'data_source' => 'unknown_value',
      'columns' => '',
      'default_page_size' => 25,
      'default_sort' => '',
      'default_sort_direction' => 'asc',
    ], $previewBuilder, $databaseSource);

    $block->build();
  }

  /**
   * @covers ::build
   */
  public function testColumnParsing(): void {
    $previewBuilder = $this->createMock(DataPreviewBuilder::class);
    $previewBuilder->expects($this->once())
      ->method('build')
      ->with(
        $this->anything(),
        $this->anything(),
        $this->callback(function ($options) {
          return $options['columns'] === ['name', 'age', 'city'];
        }),
      )
      ->willReturn(['#theme' => 'test']);

    $block = $this->createBlock([
      'resource_id' => 'test-resource',
      'data_source' => 'database',
      'columns' => 'name, age , city',
      'default_page_size' => 25,
      'default_sort' => '',
      'default_sort_direction' => 'asc',
    ], $previewBuilder);

    $block->build();
  }

  /**
   * @covers ::build
   */
  public function testColumnParsingEmpty(): void {
    $previewBuilder = $this->createMock(DataPreviewBuilder::class);
    $previewBuilder->expects($this->once())
      ->method('build')
      ->with(
        $this->anything(),
        $this->anything(),
        $this->callback(function ($options) {
          return $options['columns'] === [];
        }),
      )
      ->willReturn(['#theme' => 'test']);

    $block = $this->createBlock([
      'resource_id' => 'test-resource',
      'data_source' => 'database',
      'columns' => '',
      'default_page_size' => 25,
      'default_sort' => '',
      'default_sort_direction' => 'asc',
    ], $previewBuilder);

    $block->build();
  }

  /**
   * @covers ::build
   */
  public function testColumnParsingFiltersBlanks(): void {
    $previewBuilder = $this->createMock(DataPreviewBuilder::class);
    $previewBuilder->expects($this->once())
      ->method('build')
      ->with(
        $this->anything(),
        $this->anything(),
        $this->callback(function ($options) {
          return array_values($options['columns']) === ['name', 'city'];
        }),
      )
      ->willReturn(['#theme' => 'test']);

    $block = $this->createBlock([
      'resource_id' => 'test-resource',
      'data_source' => 'database',
      'columns' => 'name,,city,',
      'default_page_size' => 25,
      'default_sort' => '',
      'default_sort_direction' => 'asc',
    ], $previewBuilder);

    $block->build();
  }

  /**
   * @covers ::build
   */
  public function testBuildCatchesException(): void {
    $previewBuilder = $this->createMock(DataPreviewBuilder::class);
    $previewBuilder->method('build')
      ->willThrowException(new \RuntimeException('Storage not found'));

    $block = $this->createBlock([
      'resource_id' => 'test-resource',
      'data_source' => 'database',
      'columns' => '',
      'default_page_size' => 25,
      'default_sort' => '',
      'default_sort_direction' => 'asc',
    ], $previewBuilder);

    $build = $block->build();
    $this->assertArrayHasKey('#markup', $build);
    $this->assertStringContainsString('Unable to load data preview', (string) $build['#markup']);
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration(): void {
    $block = $this->createBlock([]);
    $defaults = $block->defaultConfiguration();

    $this->assertEquals('', $defaults['resource_id']);
    $this->assertEquals('database', $defaults['data_source']);
    $this->assertEquals('', $defaults['columns']);
    $this->assertEquals(25, $defaults['default_page_size']);
    $this->assertEquals('', $defaults['default_sort']);
    $this->assertEquals('asc', $defaults['default_sort_direction']);
  }

  /**
   * @covers ::build
   */
  public function testDefaultSortEmptyStringBecomesNull(): void {
    $previewBuilder = $this->createMock(DataPreviewBuilder::class);
    $previewBuilder->expects($this->once())
      ->method('build')
      ->with(
        $this->anything(),
        $this->anything(),
        $this->callback(function ($options) {
          return $options['default_sort'] === NULL;
        }),
      )
      ->willReturn(['#theme' => 'test']);

    $block = $this->createBlock([
      'resource_id' => 'test-resource',
      'data_source' => 'database',
      'columns' => '',
      'default_page_size' => 25,
      'default_sort' => '',
      'default_sort_direction' => 'asc',
    ], $previewBuilder);

    $block->build();
  }

  /**
   * @covers ::build
   */
  public function testBuildWithExternalApiUrl(): void {
    $calls = [];
    $apiSource = $this->createMock(ApiDataSource::class);
    $apiSource->method('setBaseUrl')
      ->willReturnCallback(function ($url) use (&$calls, $apiSource) {
        $calls[] = $url;
        return $apiSource;
      });

    $previewBuilder = $this->createMock(DataPreviewBuilder::class);
    $previewBuilder->method('build')->willReturn(['#theme' => 'test']);

    $block = $this->createBlock([
      'resource_id' => 'test-resource',
      'data_source' => 'api',
      'api_base_url' => 'https://external.example.com',
      'columns' => '',
      'default_page_size' => 25,
      'default_sort' => '',
      'default_sort_direction' => 'asc',
    ], $previewBuilder, NULL, $apiSource);

    $block->build();

    $this->assertContains('https://external.example.com', $calls);
  }

  /**
   * @covers ::build
   */
  public function testApiBaseUrlResetAfterBuild(): void {
    $calls = [];
    $apiSource = $this->createMock(ApiDataSource::class);
    $apiSource->method('setBaseUrl')
      ->willReturnCallback(function ($url) use (&$calls, $apiSource) {
        $calls[] = $url;
        return $apiSource;
      });

    $previewBuilder = $this->createMock(DataPreviewBuilder::class);
    $previewBuilder->method('build')->willReturn(['#theme' => 'test']);

    $block = $this->createBlock([
      'resource_id' => 'test-resource',
      'data_source' => 'api',
      'api_base_url' => 'https://external.example.com',
      'columns' => '',
      'default_page_size' => 25,
      'default_sort' => '',
      'default_sort_direction' => 'asc',
    ], $previewBuilder, NULL, $apiSource);

    $block->build();

    $this->assertEquals('https://external.example.com', $calls[0]);
    $this->assertNull($calls[1]);
  }

  /**
   * @covers ::build
   */
  public function testApiBaseUrlNotSetForDatabaseSource(): void {
    $apiSource = $this->createMock(ApiDataSource::class);
    $apiSource->expects($this->never())->method('setBaseUrl');

    $previewBuilder = $this->createMock(DataPreviewBuilder::class);
    $previewBuilder->method('build')->willReturn(['#theme' => 'test']);

    $block = $this->createBlock([
      'resource_id' => 'test-resource',
      'data_source' => 'database',
      'api_base_url' => 'https://external.example.com',
      'columns' => '',
      'default_page_size' => 25,
      'default_sort' => '',
      'default_sort_direction' => 'asc',
    ], $previewBuilder, NULL, $apiSource);

    $block->build();
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfigurationIncludesApiBaseUrl(): void {
    $block = $this->createBlock([]);
    $defaults = $block->defaultConfiguration();

    $this->assertArrayHasKey('api_base_url', $defaults);
    $this->assertEquals('', $defaults['api_base_url']);
  }

  /**
   * Create a DataPreviewBlock with mocked dependencies.
   */
  protected function createBlock(
    array $config,
    ?DataPreviewBuilder $previewBuilder = NULL,
    ?DatabaseDataSource $databaseDataSource = NULL,
    ?ApiDataSource $apiDataSource = NULL,
    ?LoggerInterface $logger = NULL,
  ): DataPreviewBlock {
    $previewBuilder = $previewBuilder ?? $this->createMock(DataPreviewBuilder::class);
    $databaseDataSource = $databaseDataSource ?? $this->createMock(DatabaseDataSource::class);
    $apiDataSource = $apiDataSource ?? $this->createMock(ApiDataSource::class);
    $logger = $logger ?? $this->createMock(LoggerInterface::class);

    $block = new DataPreviewBlock(
      $config,
      'datastore_data_preview',
      ['provider' => 'datastore_data_preview'],
      $previewBuilder,
      $databaseDataSource,
      $apiDataSource,
      $logger,
    );

    $block->setStringTranslation($this->getStringTranslationStub());

    return $block;
  }

  /**
   * Returns a stub translation service.
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
