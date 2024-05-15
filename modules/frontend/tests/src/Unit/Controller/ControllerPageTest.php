<?php

use \Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\frontend\Controller\Page as PageController;
use Drupal\frontend\Page;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass Drupal\frontend\Controller\Page
 */
class ControllerPageTest extends TestCase {

  /**
   * Cache max age config value.
   *
   * @var int
   */
  private $cacheMaxAge;

  protected function setUp(): void {
    parent::setUp();
    $this->cacheMaxAge = 0;
  }

  /**
   * Getter.
   */
  public function getContainer() {

    $container = $this->getMockBuilder(ContainerInterface::class)
      ->onlyMethods(['get', 'has'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $container->method('get')
      ->with(
        $this->logicalOr(
          $this->equalTo('frontend.page'),
          $this->equalTo('config.factory')
        )
      )
      ->will($this->returnCallback([$this, 'containerGet']));

    $container->method('has')
      ->with('config.factory')
      ->willReturn(TRUE);

    return $container;
  }

  /**
   *
   */
  public function containerGet($input) {
    switch ($input) {
      case 'frontend.page':
        $pageBuilder = $this->getMockBuilder(Page::class)
          ->disableOriginalConstructor()
          ->onlyMethods(['build'])
          ->getMock();
        $pageBuilder->method('build')->willReturn("<h1>Hello World!!!</h1>\n");
        return $pageBuilder;

        break;
      case 'config.factory':
        $immutableConfig = $this->getMockBuilder(ImmutableConfig::class)
          ->disableOriginalConstructor()
          ->onlyMethods(["get"])
          ->getMock();
        $immutableConfig->method('get')->willReturn($this->cacheMaxAge);

        $configFactory = $this->getMockBuilder(ConfigFactory::class)
          ->disableOriginalConstructor()
          ->onlyMethods(["get"])
          ->getMock();
        $configFactory->method('get')->willReturn($immutableConfig);
        return $configFactory;

        break;
    }
  }

  /**
   *
   */
  public function test() {
    $controller = PageController::create($this->getContainer());
    /** @var \Symfony\Component\HttpFoundation\Response $response */
    $response = $controller->page('home');
    $this->assertEquals("<h1>Hello World!!!</h1>\n", $response->getContent());
  }

  /**
   * Test response cache headers.
   */
  public function testCacheHeaders() {
    $container = $this->getContainer();
    \Drupal::setContainer($container);

    // Create controller with caching turned off.
    $controller = PageController::create($container);
    $response = $controller->page('home');
    $headers = $response->headers;

    $this->assertEquals('no-cache, private', $headers->get('cache-control'));
    $this->assertEmpty($headers->get('vary'));
    $this->assertEmpty($headers->get('last-modified'));

    // Turn caching on.
    $this->cacheMaxAge = 600;

    $controller = PageController::create($container);
    $response = $controller->page('home');
    $headers = $response->headers;

    $this->assertEquals('max-age=600, public', $headers->get('cache-control'));
    $this->assertNotEmpty($headers->get('last-modified'));
  }

}
