<?php

namespace Drupal\Tests\common\Kernel\StreamWrapper;

use Drupal\KernelTests\KernelTestBase;

/**
 * @group dkan
 * @group common
 * @group kernel
 */
class DkanStreamWrapperTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'metastore',
  ];

  public function testPublicScheme() {
    $uri = 'dkan://metastore';
    $api = json_decode(file_get_contents('dkan://metastore'));
    $this->assertEquals('API Documentation', $api->info->title);

    /** @var \Drupal\Core\StreamWrapper\StreamWrapperManager $manager */
    $manager = $this->container->get('stream_wrapper_manager');
    $scheme = $manager->getScheme($uri);
    $this->assertEquals('dkan', $scheme);
    $descriptions = $manager->getDescriptions();
    $this->assertStringContainsString('Simple way to request DKAN', $descriptions[$scheme]);
    $names = $manager->getNames();
    $this->assertEquals('DKAN documents', $names[$scheme]);

    $path = $manager->getViaScheme($scheme)->getDirectoryPath();
    $this->assertStringContainsString('/api/1', $path);
    $base_url = $this->container->get('request_stack')
      ->getCurrentRequest()
      ->getSchemeAndHttpHost();
    $ext = $manager->getViaScheme($scheme)->getExternalUrl();
    $this->assertStringContainsString($base_url . '/api/1', $ext);
  }

}
