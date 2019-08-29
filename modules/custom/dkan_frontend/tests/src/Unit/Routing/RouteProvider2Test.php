<?php

/**
 *
 */use Drupal\dkan_frontend\Routing\RouteProvider;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class RouteProvider2Test extends TestCase {

  /**
   *
   */
  public function test() {
    $provider = new RouteProvider(__DIR__ . "/../../../app");

    /* @var $routes \Symfony\Component\Routing\RouteCollection */
    $routes = $provider->routes();

    /* @var $route \Symfony\Component\Routing\Route */
    /* @var $route \Symfony\Component\Routing\Route */
    foreach ($routes->all() as $route) {
      $this->assertThat(
        $route->getPath(),
        $this->logicalOr(
          $this->equalTo("/dataset/123"),
          $this->equalTo("/home")
        )
      );
    }

  }

}
