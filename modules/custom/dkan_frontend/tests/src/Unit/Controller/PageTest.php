<?php

use Drupal\dkan_frontend\Controller\Page as PageController;
use Drupal\dkan_frontend\Page;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class ControllerPageTest extends TestCase {

  /**
   *
   */
  public function getContainer() {

    $container = $this->getMockBuilder(ContainerInterface::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $container->method('get')
      ->with(
        $this->logicalOr(
          $this->equalTo('dkan_frontend.page')
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
      case 'dkan_frontend.page':
        $pageBuilder = $this->getMockBuilder(Page::class)
          ->disableOriginalConstructor()
          ->setMethods(['build', 'buildDataset'])
          ->getMock();
        $pageBuilder->method('build')->willReturn("<h1>Hello World!!!</h1>\n");
        $pageBuilder->method('buildDataset')->willReturn("<h1>Hello World!!!</h1>\n");
        return $pageBuilder;

      break;
    }
  }

  /**
   *
   */
  public function test() {
    $controller = PageController::create($this->getContainer());
    /* @var $response \Symfony\Component\HttpFoundation\Response */
    $response = $controller->page('home');
    $this->assertEquals("<h1>Hello World!!!</h1>\n", $response->getContent());
  }

  /**
   *
   */
  public function testDataset() {
    $controller = PageController::create($this->getContainer());
    /* @var $response \Symfony\Component\HttpFoundation\Response */
    $response = $controller->dataset('123');
    $this->assertEquals("<h1>Hello World!!!</h1>\n", $response->getContent());
  }

}
