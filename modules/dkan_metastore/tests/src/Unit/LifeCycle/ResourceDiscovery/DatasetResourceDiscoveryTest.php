<?php

namespace Drupal\Tests\dkan_metastore\Unit\LifeCycle\ResourceDiscovery;

use Drupal\dkan_metastore\LifeCycle\ResourceDiscovery\DatasetResourceDiscovery;
use Drupal\dkan_metastore\LifeCycle\ResourceDiscovery\SkippedEntryReasons;
use PHPUnit\Framework\TestCase;

/**
 * Test class for DatasetResourceDiscovery.
 *
 * @group unit
 * @group dkan_metastore
 *
 * @coversDefaultClass \Drupal\dkan_metastore\LifeCycle\ResourceDiscovery\DatasetResourceDiscovery
 */
class DatasetResourceDiscoveryTest extends TestCase {

  /**
   * @covers ::discoverResources
   * @covers ::processDistribution
   */
  public function testDiscoverResources() {
    $discovery = new DatasetResourceDiscovery();

    $metadata = (object) [
      'identifier' => '550e8400-e29b-41d4-a716-446655440000',
      'title' => 'Test Dataset',
      'distribution' => [
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
          'format' => 'csv',
        ],
      ],
    ];

    $result = $discovery->discoverResources($metadata);

    $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $result->datasetId);
    $this->assertEquals(1, count($result->getDiscoveredResources()));
    $this->assertEquals($metadata->distribution[0]->downloadURL, $result->getDiscoveredResources()[0]->getFilePath());
    $this->assertEquals($metadata->distribution[0]->mediaType, $result->getDiscoveredResources()[0]->getMimeType());

    $this->assertEquals(1, count($result->getSkippedEntries()));
    $this->assertEquals(SkippedEntryReasons::UnsupportedType, $result->getSkippedEntries()[0]->reason);
    $this->assertEquals($metadata->distribution[1]->downloadURL, $result->getSkippedEntries()[0]->filePath);
    $this->assertEquals($metadata->distribution[1]->mediaType, $result->getSkippedEntries()[0]->mimeType);
  }

  public function testDiscoverResourcesWithMissingIdentifier() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Metadata object must contain an identifier property.');

    $discovery = new DatasetResourceDiscovery();

    $metadata = (object) [
      'title' => 'Test Dataset',
      'distribution' => [],
    ];

    $discovery->discoverResources($metadata);
  }

  /**
   * Test discoverResources with various valid URLs.
   *
   * @dataProvider validUrlProvider
   *
   * @covers ::discoverResources
   * @covers ::processDistribution
   */
  public function testDiscoverResourcesWithValidUrl(string $url) {
    $discovery = new DatasetResourceDiscovery();

    $metadata = (object) [
      'identifier' => '550e8400-e29b-41d4-a716-446655440000',
      'title' => 'Test Dataset',
      'distribution' => [
        (object) [
          'title' => 'Resource 1',
          'downloadURL' => $url,
          'mediaType' => 'text/csv',
          'format' => 'csv',
        ],
      ],
    ];

    $result = $discovery->discoverResources($metadata);

    $this->assertEquals(1, count($result->getDiscoveredResources()));
    $this->assertEquals(0, count($result->getSkippedEntries()));
  }

  /**
   * Data provider for valid URL scenarios.
   */
  public static function validUrlProvider(): array {
    return [
      ['public://something.csv'],
      ['file:///path/to/file.csv'],
      ['http://example.com/resource-1.csv'],
      ['ftp://example.com/resource-1.csv'],
    ];
  }

  /**
   * Test various invalid URL scenarios.
   *
   * @dataProvider invalidUrlProvider
   *
   * @covers ::discoverResources
   * @covers ::processDistribution
   */
  public function testDiscoverResourcesSkipped(?string $url) {
    $reason = SkippedEntryReasons::InvalidUrl;
    $discovery = new DatasetResourceDiscovery();

    $metadata = (object) [
      'identifier' => '550e8400-e29b-41d4-a716-446655440000',
      'title' => 'Test Dataset',
      'distribution' => [
        (object) [
          'title' => 'Resource 1',
          'downloadURL' => $url,
          'mediaType' => 'text/csv',
          'format' => 'csv',
        ],
      ],
    ];

    // downloadURL would never be NULL, the property would be absent.
    if ($url === NULL) {
      unset($metadata->distribution[0]->downloadURL);
      $reason = SkippedEntryReasons::MissingUrl;
    }

    $result = $discovery->discoverResources($metadata);

    $this->assertEquals(0, count($result->getDiscoveredResources()));
    $this->assertEquals(1, count($result->getSkippedEntries()));
    $this->assertSame($reason, $result->getSkippedEntries()[0]->reason);
  }

  /**
   * Data provider for invalid URL scenarios.
   */
  public static function invalidUrlProvider(): array {
    return [
      ['invalid-url'],
      ['http://'],
      ['/something.csv'],
      [NULL],
    ];
  }

  /**
   * @covers ::discoverResources
   * @covers ::processDistribution
   */
  public function testDiscoverResourcesOtherTopLevelKey() {
    $discovery = new DatasetResourceDiscovery();

    // When we use a different key, we expect no error but no discovered
    // resources or skipped entries.
    $metadata = (object) [
      'identifier' => '550e8400-e29b-41d4-a716-446655440000',
      'title' => 'Test Dataset',
      'resources' => [
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
          'format' => 'csv',
        ],
      ],
    ];

    $result = $discovery->discoverResources($metadata);

    $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $result->datasetId);
    $this->assertEquals(0, count($result->getDiscoveredResources()));
    $this->assertEquals(0, count($result->getSkippedEntries()));
  }

}
