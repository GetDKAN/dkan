<?php

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Query\QueryFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\frontend\Routing\RouteProvider;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Drupal\Core\Entity\Query\QueryInterface;

/**
 *
 */
class RouteProvider2Test extends TestCase {

  /**
   *
   */
  public function test() {
    /* Test Gatsby Routes */
    $queryFactory = (new Chain($this))
      ->add(QueryFactoryInterface::class, "get", QueryInterface::class)
      ->add(QueryInterface::class, 'condition', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', [])
      ->getMock();

    /* Test React App Routes */
    $reactappRoutes = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['home,/home'])
      ->getMock();

    $gatsbyProvider = new RouteProvider(__DIR__ . "/../../../gatsby", $queryFactory, $reactappRoutes);
    $reactAppProvider = new RouteProvider(__DIR__ . "/../../../cra", $queryFactory, $reactappRoutes);

    /* @var $routes \Symfony\Component\Routing\RouteCollection */
    $gatsbyRoutes = $gatsbyProvider->routes();
    $reactappRoutes = $reactAppProvider->routes();

    /* @var $route \Symfony\Component\Routing\Route */
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

    /* @var $route \Symfony\Component\Routing\Route */
    foreach ($reactappRoutes->all() as $route) {
      $this->assertThat(
        $route->getPath(),
        $this->equalTo("/home")
      );
    }

  }

}
