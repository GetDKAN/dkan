<?php

namespace Drupal\Tests\dkan_js_frontend\Unit\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Query\QueryFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\dkan_js_frontend\Routing\RouteProvider;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Drupal\Core\Entity\Query\QueryInterface;

/**
 *
 */
class RouteProviderJSTest extends TestCase {
  /**
   *
   */
  public function testCRA() {
    $possible_routes = ["/home", "/about"];
    /* Test CRA Routes */
    $queryFactory = (new Chain($this))
      ->add(QueryFactoryInterface::class, "get", QueryInterface::class)
      ->add(QueryInterface::class, 'condition', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', [])
      ->getMock();

    /* Test React App Routes */
    $options = (new Options())
      ->add('routes', ["home,/home", "about,/about"])
      ->add('css_folder', '/frontend/build/static/css/')
      ->add('js_folder', '/frontend/build/static/js/')
      ->index(0);

    /* Test React App Routes */
    $configFactory = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', $options)
      ->getMock();

    $reactAppProvider = new RouteProvider($configFactory);

    /** @var \Symfony\Component\Routing\RouteCollection $routes */
    $reactappRoutes = $reactAppProvider->routes();

    /** @var \Symfony\Component\Routing\Route $route */
    foreach ($reactappRoutes->all() as $route) {
      $this->assertContains($route->getPath(), $possible_routes);
    }

  }

}
