<?php

namespace Drupal\Tests\dkan_metastore\Unit\LifeCycle;

use Drupal\dkan_metastore\LifeCycle\ResourceDiscovery\DatasetResourceDiscovery;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\dkan_metastore\LifeCycle\ResourceDiscovery\DatasetResourceDiscovery
 */
class DatasetResourceDiscoveryTest extends TestCase {

  /**
   * @covers ::discoverResources
   */
  public function testDiscoverResources() {
    $discovery = new DatasetResourceDiscovery();

    $metadata = (object) [
      'identifier' => '550e8400-e29b-41d4-a716-446655440000',
      'title' => 'Test Dataset',
      'distributions' => [
        (object) [
          'title' => 'Resource 1',
          'downloadURL' => 'http://example.com/resource-1.csv',
          'mediaType' => 'text/csv',
          'format' => 'csv',
        ],
        (object) [
          'title' => 'Resource 2',
          'downloadURL' => 'http://example.com/resource-2.tar',
          'mediaType' => 'application/x-tar',
          'format' => 'tar',
        ],
      ],
    ];

    $result = $discovery->discoverResources($metadata);

    $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $result->datasetId);
    $this->assertEquals(1, count($result->getDiscoveredResources()));
    $this->assertEquals(1, count($result->getSkippedEntries()));
  }

}
