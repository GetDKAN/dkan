<?php

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Query\QueryFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\frontend\Routing\RouteProvider;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Drupal\Core\Entity\Query\QueryInterface;

/**
 *
 */
class RouteProvider2Test extends TestCase {

  /**
   *
   */
  public function testGatsby() {
    /* Test Gatsby Routes */
    $queryFactory = (new Chain($this))
      ->add(QueryFactoryInterface::class, "get", QueryInterface::class)
      ->add(QueryInterface::class, 'condition', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', [])
      ->getMock();

    /* Test Gatsby Config Options */
    $options = (new Options())
      ->add('routes', [])
      ->add('build_folder', '/public')
      ->add('frontend_path', '/data-catalog-frontend')
      ->index(0);

    /* Test Gatsby Config Factory */
    $configFactory = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', $options)
      ->getMock();

    $gatsbyProvider = new RouteProvider(__DIR__ . "/../../../gatsby", $queryFactory, $configFactory);

    /** @var \Symfony\Component\Routing\RouteCollection $routes */
    $gatsbyRoutes = $gatsbyProvider->routes();

    /** @var \Symfony\Component\Routing\Route $route */
    foreach ($gatsbyRoutes->all() as $route) {
      $this->assertThat(
        $route->getPath(),
        $this->logicalOr(
          $this->equalTo("/about"),
          $this->equalTo("/dataset"),
          $this->equalTo("/dataset/123"),
          $this->equalTo("/home")
        )
      );
    }

  }

  /**
   *
   */
  public function testCRA() {
    /* Test CRA Routes */
    $queryFactory = (new Chain($this))
      ->add(QueryFactoryInterface::class, "get", QueryInterface::class)
      ->add(QueryInterface::class, 'condition', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', [])
      ->getMock();

    /* Test React App Routes */
    $options = (new Options())
      ->add('routes', ["home,/home"])
      ->add('build_folder', '/build')
      ->add('frontend_path', '/frontend')
      ->index(0);

    /* Test React App Routes */
    $configFactory = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', $options)
      ->getMock();

    $reactAppProvider = new RouteProvider(__DIR__ . "/../../../cra", $queryFactory, $configFactory);

    /** @var \Symfony\Component\Routing\RouteCollection $routes */
    $reactappRoutes = $reactAppProvider->routes();

    /** @var \Symfony\Component\Routing\Route $route */
    foreach ($reactappRoutes->all() as $route) {
      $this->assertThat(
        $route->getPath(),
        $this->equalTo("/home")
      );
    }

  }

}
