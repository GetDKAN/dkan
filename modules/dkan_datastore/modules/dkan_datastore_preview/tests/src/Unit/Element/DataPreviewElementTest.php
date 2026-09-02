<?php

namespace Drupal\Tests\dkan_datastore_preview\Unit\Element;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\dkan_datastore_preview\DataSource\DataSourceInterface;
use Drupal\dkan_datastore_preview\Element\DataPreview;
use Drupal\dkan_datastore_preview\Service\DataPreviewBuilder;
use Drupal\dkan_datastore_preview\Service\ImportStatusMessage;
use Drupal\Tests\UnitTestCase;

/**
 * @covers \Drupal\dkan_datastore_preview\Element\DataPreview
 * @coversDefaultClass \Drupal\dkan_datastore_preview\Element\DataPreview
 *
 * @group dkan
 * @group dkan_datastore_preview
 * @group unit
 */
class DataPreviewElementTest extends UnitTestCase {

  /**
   * The mocked builder.
   *
   * @var \Drupal\dkan_datastore_preview\Service\DataPreviewBuilder|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $builder;

  /**
   * The mocked logger channel.
   */
  protected LoggerChannelInterface $logger;

  /**
   * Options captured from the builder call.
   *
   * @var array|null
   */
  protected ?array $builderOptions = NULL;

  /**
   * Wire the static container the element resolves services from.
   */
  protected function setContainerWithBuilder(callable $buildCallback): void {
    $this->builderOptions = NULL;

    $this->builder = $this->createMock(DataPreviewBuilder::class);
    $this->builder->method('build')
      ->willReturnCallback(function ($dataSource, $resourceId, $options) use ($buildCallback) {
        $this->builderOptions = $options;
        return $buildCallback($dataSource, $resourceId, $options);
      });

    $statusMessage = $this->createMock(ImportStatusMessage::class);
    $statusMessage->method('build')->willReturn([
      '#tag' => 'p',
      '#value' => 'Data preview is not yet available.',
    ]);

    $this->logger = $this->createMock(LoggerChannelInterface::class);
    $loggerFactory = $this->createMock(LoggerChannelFactoryInterface::class);
    $loggerFactory->method('get')->willReturn($this->logger);

    $container = new ContainerBuilder();
    $container->set('dkan.datastore_preview.builder', $this->builder);
    $container->set('dkan.datastore_preview.data_source.database', $this->createMock(DataSourceInterface::class));
    $container->set('dkan.datastore_preview.import_status_message', $statusMessage);
    $container->set('logger.factory', $loggerFactory);
    \Drupal::setContainer($container);
  }

  /**
   * GetInfo declares the per-table isolation properties.
   */
  public function testGetInfo(): void {
    $element = new DataPreview([], 'dkan_datastore_preview', []);
    $info = $element->getInfo();

    $this->assertSame('', $info['#resource_id']);
    $this->assertSame(0, $info['#pager_element']);
    $this->assertSame('', $info['#query_prefix']);
    $this->assertSame(25, $info['#default_page_size']);
    $this->assertSame([[DataPreview::class, 'preRender']], $info['#pre_render']);
  }

  /**
   * An empty resource id renders nothing and calls no services.
   */
  public function testEmptyResourceId(): void {
    $this->setContainerWithBuilder(fn () => ['#theme' => 'dkan_datastore_preview']);
    $element = ['#resource_id' => ''];
    $this->assertSame($element, DataPreview::preRender($element));
    $this->assertNull($this->builderOptions);
  }

  /**
   * Pager element and query prefix reach the builder options.
   */
  public function testIsolationOptionsPassedThrough(): void {
    $this->setContainerWithBuilder(fn () => ['#theme' => 'dkan_datastore_preview']);

    $element = DataPreview::preRender([
      '#resource_id' => 'abc__1',
      '#columns' => 'name, age',
      '#default_page_size' => 10,
      '#default_sort' => 'name',
      '#default_sort_direction' => 'desc',
      '#conditions' => [],
      '#pager_element' => 3,
      '#query_prefix' => 'dp3_',
      '#data_source_instance' => NULL,
    ]);

    $this->assertSame(3, $this->builderOptions['pager_element']);
    $this->assertSame('dp3_', $this->builderOptions['query_prefix']);
    $this->assertSame(['name', 'age'], $this->builderOptions['columns']);
    $this->assertSame(10, $this->builderOptions['default_page_size']);
    $this->assertSame('dkan_datastore_preview', $element['#theme']);
  }

  /**
   * A provided data source instance is used instead of the service.
   */
  public function testDataSourceInstance(): void {
    $instance = $this->createMock(DataSourceInterface::class);
    $seen = NULL;
    $this->setContainerWithBuilder(function ($dataSource) use (&$seen) {
      $seen = $dataSource;
      return ['#theme' => 'dkan_datastore_preview'];
    });

    DataPreview::preRender([
      '#resource_id' => 'abc__1',
      '#columns' => [],
      '#default_page_size' => 25,
      '#default_sort' => NULL,
      '#default_sort_direction' => 'asc',
      '#conditions' => [],
      '#pager_element' => 0,
      '#query_prefix' => '',
      '#data_source_instance' => $instance,
    ]);

    $this->assertSame($instance, $seen);
  }

  /**
   * A NULL build (no table yet) renders the import status message.
   */
  public function testUnavailableRendersMessage(): void {
    $this->setContainerWithBuilder(fn () => NULL);

    $element = DataPreview::preRender([
      '#resource_id' => 'abc__1',
      '#columns' => [],
      '#default_page_size' => 25,
      '#default_sort' => NULL,
      '#default_sort_direction' => 'asc',
      '#conditions' => [],
      '#pager_element' => 0,
      '#query_prefix' => '',
      '#data_source_instance' => NULL,
    ]);

    $this->assertSame('Data preview is not yet available.', $element['message']['#value']);
    $this->assertArrayNotHasKey('#theme', $element);
  }

  /**
   * A throwing builder logs a warning and still renders the message.
   */
  public function testExceptionRendersMessage(): void {
    $this->setContainerWithBuilder(function () {
      throw new \RuntimeException('boom');
    });
    $this->logger->expects($this->once())->method('warning');

    $element = DataPreview::preRender([
      '#resource_id' => 'abc__1',
      '#columns' => [],
      '#default_page_size' => 25,
      '#default_sort' => NULL,
      '#default_sort_direction' => 'asc',
      '#conditions' => [],
      '#pager_element' => 0,
      '#query_prefix' => '',
      '#data_source_instance' => NULL,
    ]);

    $this->assertSame('Data preview is not yet available.', $element['message']['#value']);
  }

}
