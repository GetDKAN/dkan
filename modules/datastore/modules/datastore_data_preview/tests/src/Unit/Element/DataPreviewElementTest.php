<?php

namespace Drupal\Tests\datastore_data_preview\Unit\Element;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\datastore_data_preview\DataSource\ApiDataSource;
use Drupal\datastore_data_preview\DataSource\DatabaseDataSource;
use Drupal\datastore_data_preview\DataSource\DataSourceInterface;
use Drupal\datastore_data_preview\Element\DataPreview;
use Drupal\datastore_data_preview\Service\DataPreviewBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \Drupal\datastore_data_preview\Element\DataPreview
 * @group datastore_data_preview
 */
class DataPreviewElementTest extends TestCase {

  /**
   * @covers ::getInfo
   */
  public function testGetInfoDefaults(): void {
    $element = new DataPreview([], 'data_preview', ['provider' => 'datastore_data_preview']);
    $info = $element->getInfo();

    $this->assertEquals('', $info['#resource_id']);
    $this->assertEquals('database', $info['#data_source']);
    $this->assertNull($info['#data_source_instance']);
    $this->assertEquals([], $info['#columns']);
    $this->assertEquals(25, $info['#default_page_size']);
    $this->assertNull($info['#default_sort']);
    $this->assertEquals('asc', $info['#default_sort_direction']);
    $this->assertEquals([], $info['#conditions']);
    $this->assertEquals('', $info['#api_base_url']);
    $this->assertEquals([[DataPreview::class, 'preRender']], $info['#pre_render']);
  }

  /**
   * @covers ::preRender
   */
  public function testPreRenderEmptyResourceId(): void {
    $element = ['#resource_id' => ''];
    $result = DataPreview::preRender($element);
    $this->assertSame($element, $result);
  }

  /**
   * @covers ::preRender
   */
  public function testPreRenderWithDatabaseSource(): void {
    $expectedBuild = ['#theme' => 'datastore_data_preview', '#table' => []];
    $databaseSource = $this->createMock(DatabaseDataSource::class);

    $builder = $this->createMock(DataPreviewBuilder::class);
    $builder->expects($this->once())
      ->method('build')
      ->with(
        $this->identicalTo($databaseSource),
        'test-resource',
        $this->anything(),
      )
      ->willReturn($expectedBuild);

    $this->setUpContainer($builder, $databaseSource);

    $element = $this->buildElement([
      '#resource_id' => 'test-resource',
      '#data_source' => 'database',
    ]);

    $result = DataPreview::preRender($element);
    $this->assertEquals('datastore_data_preview', $result['#theme']);
  }

  /**
   * @covers ::preRender
   */
  public function testPreRenderWithApiSource(): void {
    $expectedBuild = ['#theme' => 'datastore_data_preview'];
    $apiSource = $this->createMock(ApiDataSource::class);

    $builder = $this->createMock(DataPreviewBuilder::class);
    $builder->expects($this->once())
      ->method('build')
      ->with(
        $this->identicalTo($apiSource),
        'test-resource',
        $this->anything(),
      )
      ->willReturn($expectedBuild);

    $this->setUpContainer($builder, NULL, $apiSource);

    $element = $this->buildElement([
      '#resource_id' => 'test-resource',
      '#data_source' => 'api',
    ]);

    $result = DataPreview::preRender($element);
    $this->assertEquals('datastore_data_preview', $result['#theme']);
  }

  /**
   * @covers ::preRender
   */
  public function testPreRenderWithCustomDataSourceInstance(): void {
    $customSource = $this->createMock(DataSourceInterface::class);
    $expectedBuild = ['#theme' => 'datastore_data_preview'];

    $builder = $this->createMock(DataPreviewBuilder::class);
    $builder->expects($this->once())
      ->method('build')
      ->with(
        $this->identicalTo($customSource),
        'custom-resource',
        $this->anything(),
      )
      ->willReturn($expectedBuild);

    $this->setUpContainer($builder);

    $element = $this->buildElement([
      '#resource_id' => 'custom-resource',
      '#data_source' => 'database',
      '#data_source_instance' => $customSource,
    ]);

    $result = DataPreview::preRender($element);
    $this->assertEquals('datastore_data_preview', $result['#theme']);
  }

  /**
   * @covers ::preRender
   */
  public function testPreRenderColumnsAsString(): void {
    $databaseSource = $this->createMock(DatabaseDataSource::class);

    $builder = $this->createMock(DataPreviewBuilder::class);
    $builder->expects($this->once())
      ->method('build')
      ->with(
        $this->anything(),
        $this->anything(),
        $this->callback(function ($options) {
          return $options['columns'] === ['name', 'date', 'amount'];
        }),
      )
      ->willReturn(['#theme' => 'test']);

    $this->setUpContainer($builder, $databaseSource);

    $element = $this->buildElement([
      '#resource_id' => 'test-resource',
      '#columns' => 'name, date , amount',
    ]);

    DataPreview::preRender($element);
  }

  /**
   * @covers ::preRender
   */
  public function testPreRenderExceptionHandled(): void {
    $databaseSource = $this->createMock(DatabaseDataSource::class);

    $builder = $this->createMock(DataPreviewBuilder::class);
    $builder->method('build')
      ->willThrowException(new \RuntimeException('Storage not found'));

    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())
      ->method('warning')
      ->with($this->stringContains('Data preview render element error'));

    $this->setUpContainer($builder, $databaseSource, NULL, $logger);

    $element = $this->buildElement([
      '#resource_id' => 'test-resource',
    ]);

    $result = DataPreview::preRender($element);

    // Element returned without theme (no preview output).
    $this->assertArrayNotHasKey('#theme', $result);
    $this->assertEquals('test-resource', $result['#resource_id']);
  }

  /**
   * @covers ::preRender
   */
  public function testPreRenderApiBaseUrlResetOnException(): void {
    $calls = [];
    $apiSource = $this->createMock(ApiDataSource::class);
    $apiSource->method('setBaseUrl')
      ->willReturnCallback(function ($url) use (&$calls, $apiSource) {
        $calls[] = $url;
        return $apiSource;
      });

    $builder = $this->createMock(DataPreviewBuilder::class);
    $builder->method('build')
      ->willThrowException(new \RuntimeException('API error'));

    $this->setUpContainer($builder, NULL, $apiSource);

    $element = $this->buildElement([
      '#resource_id' => 'test-resource',
      '#data_source' => 'api',
      '#api_base_url' => 'https://external.example.com',
    ]);

    DataPreview::preRender($element);

    // Base URL was set then reset to NULL even though exception was thrown.
    $this->assertEquals('https://external.example.com', $calls[0]);
    $this->assertNull($calls[1]);
  }

  /**
   * Build an element array with defaults.
   */
  protected function buildElement(array $overrides): array {
    return $overrides + [
      '#resource_id' => '',
      '#data_source' => 'database',
      '#data_source_instance' => NULL,
      '#columns' => [],
      '#default_page_size' => 25,
      '#default_sort' => NULL,
      '#default_sort_direction' => 'asc',
      '#conditions' => [],
      '#api_base_url' => '',
    ];
  }

  /**
   * Set up the Drupal container with mocked services.
   */
  protected function setUpContainer(
    DataPreviewBuilder $builder,
    ?DatabaseDataSource $databaseSource = NULL,
    ?ApiDataSource $apiSource = NULL,
    ?LoggerInterface $logger = NULL,
  ): void {
    $container = new ContainerBuilder();
    $container->set('dkan.data_preview.builder', $builder);

    if ($databaseSource) {
      $container->set('dkan.data_preview.datasource.database', $databaseSource);
    }
    if ($apiSource) {
      $container->set('dkan.data_preview.datasource.api', $apiSource);
    }

    $loggerFactory = $this->createMock(LoggerChannelFactoryInterface::class);
    $loggerFactory->method('get')
      ->willReturn($logger ?? $this->createMock(LoggerInterface::class));
    $container->set('logger.factory', $loggerFactory);

    \Drupal::setContainer($container);
  }

}
