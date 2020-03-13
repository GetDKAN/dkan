<?php

use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\dkan_frontend\Routing\RouteProvider;
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

    $queryFactory = (new Chain($this))
      ->add(QueryFactory::class, "get", QueryInterface::class)
      ->add(QueryInterface::class, 'condition', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', [])
      ->getMock();

    $provider = new RouteProvider(__DIR__ . "/../../../app", $queryFactory);

    /* @var $routes \Symfony\Component\Routing\RouteCollection */
    $routes = $provider->routes();

    /* @var $route \Symfony\Component\Routing\Route */
    /* @var $route \Symfony\Component\Routing\Route */
    foreach ($routes->all() as $route) {
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

}
