<?php

declare(strict_types=1);

namespace Drupal\Tests\dkan_js_frontend\Kernel;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\dkan_js_frontend\Routing\RouteProvider;
use Drupal\KernelTests\KernelTestBase;
use Drupal\dkan_js_frontend\Controller\Page;
use Drupal\dkan_metastore\Exception\MissingObjectException;
use Drupal\dkan_metastore\MetastoreService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @covers \Drupal\dkan_js_frontend\Controller\Page
 * @coversDefaultClass \Drupal\dkan_js_frontend\Controller\Page
 *
 * @group dkan
 * @group dkan_js_frontend
 * @group kernel
 */
class PageTest extends KernelTestBase {

  /**
   * @covers ::content
   */
  public function test404OnBadPath() {
    $metastore_service = $this->getMockBuilder(MetastoreService::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();
    $metastore_service->expects($this->once())
      ->method('get')
      ->willThrowException(new MissingObjectException());

    $this->container->set('dkan.metastore.service', $metastore_service);

    $route_match = $this->getMockBuilder(RouteMatchInterface::class)
      ->getMockForAbstractClass();
    $route_match->expects($this->once())
      ->method('getRouteName')
      ->willReturn(RouteProvider::ROUTE_PREFIX . 'dataset');

    $page = Page::create($this->container);
    $this->expectException(NotFoundHttpException::class);
    $page->content($route_match, (new Request()));
  }

  /**
   * @covers ::content
   */
  public function testPathPresent() {
    // Metastore service does not throw MissingObjectException, so the dataset
    // exists.
    $metastore_service = $this->getMockBuilder(MetastoreService::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->container->set('dkan.metastore.service', $metastore_service);

    $route_match = $this->getMockBuilder(RouteMatchInterface::class)
      ->getMockForAbstractClass();
    $route_match->expects($this->once())
      ->method('getRouteName')
      ->willReturn(RouteProvider::ROUTE_PREFIX . 'dataset');
    $route_match->expects($this->once())
      ->method('getRawParameter')
      ->willReturn('123');

    $page = Page::create($this->container);
    $this->assertEquals(
      [
        '#theme' => 'page__dkan_js_frontend',
      ],
      $page->content($route_match, (new Request())),
    );
  }

}
