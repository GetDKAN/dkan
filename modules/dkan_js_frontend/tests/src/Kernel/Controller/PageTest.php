<?php

declare(strict_types=1);

namespace Drupal\Tests\dkan_js_frontend\Kernel;

use Drupal\Core\Path\CurrentPathStack;
use Drupal\dkan_js_frontend\Controller\Page;
use Drupal\KernelTests\KernelTestBase;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\MetastoreService;
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

    $current_path = $this->getMockBuilder(CurrentPathStack::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getPath'])
      ->getMock();
    $current_path->expects($this->once())
      ->method('getPath')
      // Always use leading slash.
      // @see \Symfony\Component\HttpFoundation\Request::getPathInfo()
      ->willReturn('/dataset/123');

    $this->container->set('path.current', $current_path);

    $page = Page::create($this->container);
    $this->expectException(NotFoundHttpException::class);
    $page->content();
  }

  /**
   * @covers ::content
   */
  public function testPathPresent() {
    // Mock
    $metastore_service = $this->getMockBuilder(MetastoreService::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->container->set('dkan.metastore.service', $metastore_service);

    $current_path = $this->getMockBuilder(CurrentPathStack::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getPath'])
      ->getMock();
    $current_path->expects($this->once())
      ->method('getPath')
      // Always use leading slash.
      // @see \Symfony\Component\HttpFoundation\Request::getPathInfo()
      ->willReturn('/dataset/123');

    $this->container->set('path.current', $current_path);

    $page = Page::create($this->container);
    $this->assertEquals(
      [
        '#theme' => 'page__dkan_js_frontend',
      ],
      $page->content(),
    );
  }

}
