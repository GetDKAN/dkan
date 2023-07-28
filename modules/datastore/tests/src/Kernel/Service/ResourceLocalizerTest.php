<?php

namespace Drupal\Tests\datastore\Kernel\Service;

use Drupal\common\DataResource;
use Drupal\KernelTests\KernelTestBase;

/**
 * @covers \Drupal\datastore\Service\ResourceLocalizer
 * @coversDefaultClass \Drupal\datastore\Service\ResourceLocalizer
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class ResourceLocalizerTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
  ];

  /**
   * HTTP host protocol and domain for testing download URL.
   *
   * @var string
   */
  const HOST = 'http://example.com';

  public function testNoResourceFound() {
    $service = $this->container->get('dkan.datastore.service.resource_localizer');

    $resource = new DataResource(self::HOST . '/file.csv', 'text/csv');
    $this->assertNull($service->get($resource->getIdentifier(), $resource->getVersion()));
  }

}
