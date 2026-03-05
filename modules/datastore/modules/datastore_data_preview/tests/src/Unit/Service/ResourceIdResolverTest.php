<?php

namespace Drupal\Tests\datastore_data_preview\Unit\Service;

use Drupal\datastore_data_preview\Service\ResourceIdResolver;
use Drupal\metastore\MetastoreService;
use Drupal\node\NodeInterface;
use PHPUnit\Framework\TestCase;
use RootedData\RootedJsonData;

/**
 * @coversDefaultClass \Drupal\datastore_data_preview\Service\ResourceIdResolver
 * @group datastore_data_preview
 */
class ResourceIdResolverTest extends TestCase {

  /**
   * @covers ::resolve
   */
  public function testPassthroughIdentifierVersion(): void {
    $resolver = $this->createResolver();
    $this->assertSame('abc123__1', $resolver->resolve('abc123__1'));
  }

  /**
   * @covers ::resolve
   */
  public function testResolveDistributionUuid(): void {
    $json = json_encode([
      'data' => [
        '%Ref:downloadURL' => [
          ['data' => ['identifier' => 'file_hash', 'version' => '1234567890']],
        ],
      ],
    ]);

    $rootedData = $this->createMock(RootedJsonData::class);
    $rootedData->method('__toString')->willReturn($json);

    $metastore = $this->createMock(MetastoreService::class);
    $metastore->method('get')
      ->with('distribution', 'dist-uuid-123')
      ->willReturn($rootedData);

    $resolver = $this->createResolver($metastore);
    $this->assertSame('file_hash__1234567890', $resolver->resolve('dist-uuid-123'));
  }

  /**
   * @covers ::resolve
   */
  public function testResolveReturnsNullOnException(): void {
    $metastore = $this->createMock(MetastoreService::class);
    $metastore->method('get')
      ->willThrowException(new \Exception('Not found'));

    $resolver = $this->createResolver($metastore);
    $this->assertNull($resolver->resolve('nonexistent-uuid'));
  }

  /**
   * @covers ::resolve
   */
  public function testResolveReturnsNullWhenNoRef(): void {
    $json = json_encode(['data' => ['title' => 'Test']]);
    $rootedData = $this->createMock(RootedJsonData::class);
    $rootedData->method('__toString')->willReturn($json);

    $metastore = $this->createMock(MetastoreService::class);
    $metastore->method('get')->willReturn($rootedData);

    $resolver = $this->createResolver($metastore);
    $this->assertNull($resolver->resolve('uuid-no-ref'));
  }

  /**
   * @covers ::resolveFromNode
   */
  public function testResolveFromNodeValid(): void {
    $metadata = (object) [
      'data' => (object) [
        '%Ref:downloadURL' => [
          (object) [
            'data' => (object) [
              'identifier' => '3a187a87dc6cd47c48b6b4c4785224b7',
              'version' => '1763473949',
            ],
          ],
        ],
      ],
    ];

    $node = $this->createMockNode(json_encode($metadata));
    $resolver = $this->createResolver();
    $this->assertSame('3a187a87dc6cd47c48b6b4c4785224b7__1763473949', $resolver->resolveFromNode($node));
  }

  /**
   * @covers ::resolveFromNode
   */
  public function testResolveFromNodeEmptyMetadata(): void {
    $node = $this->createMockNode('');
    $resolver = $this->createResolver();
    $this->assertNull($resolver->resolveFromNode($node));
  }

  /**
   * @covers ::resolveFromNode
   */
  public function testResolveFromNodeMalformedJson(): void {
    $node = $this->createMockNode('{not valid json');
    $resolver = $this->createResolver();
    $this->assertNull($resolver->resolveFromNode($node));
  }

  /**
   * @covers ::resolveFromNode
   */
  public function testResolveFromNodeMissingRef(): void {
    $metadata = (object) ['data' => (object) ['title' => 'Some distribution']];
    $node = $this->createMockNode(json_encode($metadata));
    $resolver = $this->createResolver();
    $this->assertNull($resolver->resolveFromNode($node));
  }

  /**
   * @covers ::resolveFromNode
   */
  public function testResolveFromNodeMissingIdentifier(): void {
    $metadata = (object) [
      'data' => (object) [
        '%Ref:downloadURL' => [
          (object) ['data' => (object) ['version' => '1763473949']],
        ],
      ],
    ];

    $node = $this->createMockNode(json_encode($metadata));
    $resolver = $this->createResolver();
    $this->assertNull($resolver->resolveFromNode($node));
  }

  /**
   * Create a resolver instance with mocked dependencies.
   */
  protected function createResolver(?MetastoreService $metastore = NULL): ResourceIdResolver {
    $metastore = $metastore ?? $this->createMock(MetastoreService::class);
    return new ResourceIdResolver($metastore);
  }

  /**
   * Creates a mock node with the given field_json_metadata value.
   */
  protected function createMockNode(?string $jsonValue): NodeInterface {
    $fieldItem = new \stdClass();
    $fieldItem->value = $jsonValue;

    $node = $this->createMock(NodeInterface::class);
    $node->method('get')
      ->with('field_json_metadata')
      ->willReturn($fieldItem);

    return $node;
  }

}
