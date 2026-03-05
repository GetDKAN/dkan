<?php

namespace Drupal\Tests\datastore_data_preview\Unit\ExtraField;

use Drupal\datastore_data_preview\Service\ResourceIdResolver;
use Drupal\metastore\MetastoreService;
use Drupal\node\NodeInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests ResourceIdResolver::resolveFromNode() for extra field use cases.
 *
 * @group datastore_data_preview
 */
class ResolveResourceIdTest extends TestCase {

  /**
   * The resolver under test.
   *
   * @var \Drupal\datastore_data_preview\Service\ResourceIdResolver
   */
  protected ResourceIdResolver $resolver;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $metastore = $this->createMock(MetastoreService::class);
    $this->resolver = new ResourceIdResolver($metastore);
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

  /**
   * Valid distribution metadata returns resource ID.
   */
  public function testValidDistributionReturnsResourceId() {
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
    $result = $this->resolver->resolveFromNode($node);
    $this->assertSame('3a187a87dc6cd47c48b6b4c4785224b7__1763473949', $result);
  }

  /**
   * Empty metadata returns NULL.
   */
  public function testEmptyMetadataReturnsNull() {
    $node = $this->createMockNode('');
    $this->assertNull($this->resolver->resolveFromNode($node));
  }

  /**
   * Malformed JSON returns NULL.
   */
  public function testMalformedJsonReturnsNull() {
    $node = $this->createMockNode('{not valid json');
    $this->assertNull($this->resolver->resolveFromNode($node));
  }

  /**
   * Metadata without %Ref:downloadURL returns NULL.
   */
  public function testMissingRefDownloadUrlReturnsNull() {
    $metadata = (object) [
      'data' => (object) [
        'title' => 'Some distribution',
      ],
    ];

    $node = $this->createMockNode(json_encode($metadata));
    $this->assertNull($this->resolver->resolveFromNode($node));
  }

  /**
   * Ref data missing identifier returns NULL.
   */
  public function testMissingIdentifierReturnsNull() {
    $metadata = (object) [
      'data' => (object) [
        '%Ref:downloadURL' => [
          (object) [
            'data' => (object) [
              'version' => '1763473949',
            ],
          ],
        ],
      ],
    ];

    $node = $this->createMockNode(json_encode($metadata));
    $this->assertNull($this->resolver->resolveFromNode($node));
  }

}
