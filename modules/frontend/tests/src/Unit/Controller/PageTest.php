<?php

use Drupal\frontend\Controller\Page as PageController;
use Drupal\frontend\Page;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class ControllerPageTest extends TestCase {

  /**
   * Getter.
   */
  public function getContainer() {

    $container = $this->getMockBuilder(ContainerInterface::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $container->method('get')
      ->with(
        $this->logicalOr(
          $this->equalTo('frontend.page')
        )
      )
      ->will($this->returnCallback([$this, 'containerGet']));

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
          ->setMethods(['build'])
          ->getMock();
        $pageBuilder->method('build')->willReturn("<h1>Hello World!!!</h1>\n");
        return $pageBuilder;

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

}
