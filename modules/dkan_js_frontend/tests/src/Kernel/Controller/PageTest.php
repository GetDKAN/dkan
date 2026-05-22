<?php

declare(strict_types=1);

namespace Drupal\Tests\dkan_js_frontend\Kernel;

use Drupal\Core\Routing\RouteMatch;
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

  protected static $modules = [
    'system',
    'node',
    'user',
    'field',
    'dkan_js_frontend',
    'dkan_metastore',
    'dkan_common',
    'content_moderation',
    'workflows',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig('system');
    $this->installConfig('node');
    $this->installConfig('dkan_common');
    $this->installConfig('dkan_metastore');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('content_moderation_state');
    $this->installConfig('field');
    $this->installEntitySchema('user');
    $this->installEntitySchema('resource_mapping');
  }

  /**
   * @covers ::content
   */
  public function test404OnBadPath() {
    $route_match = $this->getMockBuilder(RouteMatch::class)
      ->disableOriginalConstructor()
      ->getMock();
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
    $route_match = $this->getMockBuilder(RouteMatch::class)
      ->disableOriginalConstructor()
      ->getMock();
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
