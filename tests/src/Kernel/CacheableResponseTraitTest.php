<?php

namespace Drupal\Tests\common\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\common\CacheableResponseTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * @coversDefaultClass \Drupal\common\CacheableResponseTrait
 *
 * @group dkan
 * @group common
 * @group kernel
 */
class CacheableResponseTraitTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'system',
  ];

  public function testCacheableResponse() {
    // Check the defaults from both Response and the trait.
    $no_cache_controller = new ControllerUsesCacheableResponseTrait();
    $response = $no_cache_controller->traitAddCacheHeaders(new Response());
    $this->assertStringContainsString('no-cache', $response->headers->get('Cache-Control'));
    $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));

    // Set the max age in config.
    $config_max_age = 999;
    $this->config('system.performance')->set('cache.page.max_age', $config_max_age)->save();
    $config_controller = new ControllerUsesCacheableResponseTrait();
    $response = $config_controller->traitAddCacheHeaders(new Response());
    $this->assertEquals($config_max_age, $response->getMaxAge());
    $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));

    // Set the max age in the controller object. This should override the
    // config.
    $max_time = 23;
    $max_time_controller = new ControllerUsesCacheableResponseTrait();
    $max_time_controller->traitSetMaxAgeProperty($max_time);
    $response = $max_time_controller->traitAddCacheHeaders(new Response());
    $this->assertEquals($max_time, $response->getMaxAge());
    $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
  }

}

/**
 * Make a stub class because it's less cumbersome than using mocking.
 */
class ControllerUsesCacheableResponseTrait {
  use CacheableResponseTrait;

  public function traitSetMaxAgeProperty(int $max_age) {
    $this->cacheMaxAge = $max_age;
  }

  public function traitAddCacheHeaders(Response $response): Response {
    return $this->addCacheHeaders($response);
  }

  public function traitSetCacheMaxAge() {
    $this->setCacheMaxAge();
  }

}
