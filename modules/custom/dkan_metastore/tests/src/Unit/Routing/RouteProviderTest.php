<?php

/**
 *
 */
use Drupal\dkan_metastore\Routing\RouteProvider;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Config\ConfigFactoryInterface;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class RouteProviderTest extends TestCase {

  /**
   *
   */
  public function test() {
    $config_factory = $this->getMockBuilder(ConfigFactoryInterface::class)
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMockForAbstractClass();

    $config = $this->getMockBuilder(ImmutableConfig::class)
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMock();

    $config->method('get')->willReturn(['theme']);

    $config_factory->method('get')->willReturn($config);

    $provider = new RouteProvider($config_factory);

    /* @var $routes \Symfony\Component\Routing\RouteCollection */
    $routes = $provider->routes();

    /* @var $route \Symfony\Component\Routing\Route */
    foreach ($routes->all() as $route) {
      $this->assertThat(
        $route->getPath(),
        $this->logicalOr(
          $this->equalTo("/api/v1/dataset/{uuid}"),
          $this->equalTo("/api/v1/dataset/{uuid}/resources"),
          $this->equalTo("/api/v1/dataset"),
          $this->equalTo("/api/v1/theme/{uuid}"),
          $this->equalTo("/api/v1/theme")
        )
      );
    }
  }

}
