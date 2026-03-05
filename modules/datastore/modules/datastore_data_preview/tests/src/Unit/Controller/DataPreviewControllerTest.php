<?php

namespace Drupal\Tests\datastore_data_preview\Unit\Controller;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\datastore_data_preview\Controller\DataPreviewController;
use Drupal\datastore_data_preview\Service\ResourceIdResolver;
use Drupal\metastore\MetastoreService;
use PHPUnit\Framework\TestCase;
use RootedData\RootedJsonData;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @coversDefaultClass \Drupal\datastore_data_preview\Controller\DataPreviewController
 * @group datastore_data_preview
 */
class DataPreviewControllerTest extends TestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $container = new ContainerBuilder();
    $translation = $this->createMock(TranslationInterface::class);
    $translation->method('translateString')
      ->willReturnCallback(fn($wrapper) => $wrapper->getUntranslatedString());
    $container->set('string_translation', $translation);
    \Drupal::setContainer($container);
  }

  /**
   * @covers ::preview
   */
  public function testPreviewWithIdentifierVersionFormat(): void {
    $resolver = $this->createMock(ResourceIdResolver::class);
    $resolver->method('resolve')
      ->with('abc123__1')
      ->willReturn('abc123__1');

    $controller = $this->createController(resolver: $resolver);
    $result = $controller->preview('abc123__1');

    $this->assertEquals('data_preview', $result['#type']);
    $this->assertEquals('abc123__1', $result['#resource_id']);
  }

  /**
   * @covers ::preview
   */
  public function testPreviewWithDistributionUuid(): void {
    $resolver = $this->createMock(ResourceIdResolver::class);
    $resolver->method('resolve')
      ->with('dist-uuid-123')
      ->willReturn('file_hash__1234567890');

    $controller = $this->createController(resolver: $resolver);
    $result = $controller->preview('dist-uuid-123');

    $this->assertEquals('data_preview', $result['#type']);
    $this->assertEquals('file_hash__1234567890', $result['#resource_id']);
  }

  /**
   * @covers ::preview
   */
  public function testPreviewThrows404ForInvalidDistribution(): void {
    $resolver = $this->createMock(ResourceIdResolver::class);
    $resolver->method('resolve')->willReturn(NULL);

    $controller = $this->createController(resolver: $resolver);

    $this->expectException(NotFoundHttpException::class);
    $controller->preview('nonexistent-uuid');
  }

  /**
   * @covers ::title
   */
  public function testTitleWithDistributionName(): void {
    $resolver = $this->createMock(ResourceIdResolver::class);
    $resolver->method('resolve')
      ->with('dist-uuid')
      ->willReturn('hash__123');

    $json = json_encode([
      'data' => [
        'title' => 'My Dataset File',
        '%Ref:downloadURL' => [
          ['data' => ['identifier' => 'hash', 'version' => '123']],
        ],
      ],
    ]);
    $rootedData = $this->createMock(RootedJsonData::class);
    $rootedData->method('__toString')->willReturn($json);

    $metastore = $this->createMock(MetastoreService::class);
    $metastore->method('get')
      ->with('distribution', 'dist-uuid')
      ->willReturn($rootedData);

    $controller = $this->createController(resolver: $resolver, metastore: $metastore);
    $title = $controller->title('dist-uuid');

    $this->assertStringContainsString('My Dataset File', (string) $title);
  }

  /**
   * @covers ::title
   */
  public function testTitleFallsBackOnError(): void {
    $resolver = $this->createMock(ResourceIdResolver::class);
    $resolver->method('resolve')->willReturn(NULL);

    $metastore = $this->createMock(MetastoreService::class);
    $metastore->method('get')
      ->willThrowException(new \Exception('Not found'));

    $controller = $this->createController(resolver: $resolver, metastore: $metastore);
    $title = $controller->title('bad-uuid');

    $this->assertEquals('Data Preview', (string) $title);
  }

  /**
   * @covers ::title
   */
  public function testTitleWithIdentifierVersionFormat(): void {
    $resolver = $this->createMock(ResourceIdResolver::class);
    $resolver->method('resolve')->willReturn('abc__1');

    $controller = $this->createController(resolver: $resolver);
    $title = $controller->title('abc__1');

    $this->assertEquals('Data Preview', (string) $title);
  }

  /**
   * Create a controller instance with mocked dependencies.
   */
  protected function createController(
    ?ResourceIdResolver $resolver = NULL,
    ?MetastoreService $metastore = NULL,
  ): DataPreviewController {
    $resolver = $resolver ?? $this->createMock(ResourceIdResolver::class);
    $metastore = $metastore ?? $this->createMock(MetastoreService::class);
    return new DataPreviewController($resolver, $metastore);
  }

}
