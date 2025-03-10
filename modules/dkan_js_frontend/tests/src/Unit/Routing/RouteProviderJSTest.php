<?php

namespace Drupal\Tests\dkan_js_frontend\Unit\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\dkan_js_frontend\Routing\RouteProvider;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

/**
 * @group dkan
 * @group dkan_js_frontend
 * @group unit
 */
class RouteProviderJSTest extends TestCase {

  public function testCRA() {
    /* Test CRA Routes */
    $possible_routes = ['/home', '/about'];

    /* Test React App Routes */
    $options = (new Options())
      ->add('routes', ['home,/home', 'about,/about'])
      ->add('css_folder', '/frontend/build/static/css/')
      ->add('js_folder', '/frontend/build/static/js/')
      ->index(0);

    /* Test React App Routes */
    $configFactory = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', $options)
      ->getMock();

    $reactAppProvider = new RouteProvider($configFactory);

    $reactappRoutes = $reactAppProvider->routes();

    /** @var \Symfony\Component\Routing\Route $route */
    foreach ($reactappRoutes->all() as $route) {
      $this->assertContains($route->getPath(), $possible_routes);
    }

  }

}
