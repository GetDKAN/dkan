<?php

namespace Drupal\Tests\dkan_metastore\Unit\LifeCycle;

use Drupal\dkan_metastore\LifeCycle\ResourceDiscovery\ResourceDiscoveryResult;
use PHPUnit\Framework\TestCase;
use Drupal\dkan_common\DataResource;
use Drupal\dkan_metastore\LifeCycle\ResourceDiscovery\SkippedEntry;
use Drupal\dkan_metastore\LifeCycle\ResourceDiscovery\SkippedEntryReasons;

/**
 * Test class for ResourceDiscoveryResult.
 *
 * @coversDefaultClass \Drupal\dkan_metastore\LifeCycle\ResourceDiscovery\ResourceDiscoveryResult
 */
class ResourceDiscoveryResultTest extends TestCase {

  /**
   * @covers ::__construct
   * @covers ::addDiscoveredResource
   * @covers ::addSkippedEntry
   */
  public function testResourceDiscoveryResult() {
    $result = new ResourceDiscoveryResult(
      datasetId: '550e8400-e29b-41d4-a716-446655440000',
    );

    $resource = new DataResource(
      file_path: 'https://example.com/resource/1.csv',
      mimeType: 'text/csv'
    );
    $result->addDiscoveredResource($resource);

    $skippedEntry = new SkippedEntry(
      filePath: 'https://example.com/resource/2.tar',
      mimeType: 'application/x-tar',
      reason: SkippedEntryReasons::UnsupportedType,
    );
    $result->addSkippedEntry($skippedEntry);

    $this->assertCount(1, $result->getDiscoveredResources());
    $this->assertCount(1, $result->getSkippedEntries());
    $this->assertSame($resource, $result->getDiscoveredResources()[0]);
    $this->assertSame($skippedEntry, $result->getSkippedEntries()[0]);
  }

}
