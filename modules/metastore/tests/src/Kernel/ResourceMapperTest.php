<?php

namespace Drupal\Tests\metastore\Kernel;

use Drupal\common\DataResource;
use Drupal\KernelTests\KernelTestBase;
use Drupal\metastore\ResourceMapper;

/**
 * @covers \Drupal\metastore\ResourceMapper
 * @coversDefaultClass \Drupal\metastore\ResourceMapper
 *
 * @group dkan
 * @group metastore
 * @group kernel
 */
class ResourceMapperTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'metastore',
  ];

  const SOURCE_URL = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';

  public function testMapperUpdate() {
    $resource = new DataResource(
      self::SOURCE_URL,
      'text/csv',
      DataResource::DEFAULT_SOURCE_PERSPECTIVE
    );
    /** @var \Drupal\metastore\ResourceMapper $mapper */
    $this->assertInstanceOf(
      ResourceMapper::class,
      $mapper = $this->container->get('dkan.metastore.resource_mapper')
    );
    // Can we actually update?
    $mapper->update($resource);
    $this->assertNotNull(
      $resource = $mapper->get($resource->getIdentifier())
    );
    $this->assertEquals(self::SOURCE_URL, $resource->getFilePath());
  }

}
