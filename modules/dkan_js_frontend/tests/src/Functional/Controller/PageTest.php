<?php

declare(strict_types=1);

namespace Drupal\Tests\dkan_js_frontend\Functional\Controller;

use Drupal\Tests\BrowserTestBase;

/**
 * @covers \Drupal\dkan_js_frontend\Controller\Page
 * @coversDefaultClass \Drupal\dkan_js_frontend\Controller\Page
 *
 * @group dkan
 * @group dkan_js_frontend
 * @group functional
 */
class PageTest extends BrowserTestBase {

  protected $defaultTheme = 'stark';

  protected static $modules = [
    'dkan_js_frontend',
    'metastore',
    'node',
    'field',
  ];

  /**
   * Test 200 for existing route, and 404 for non-existent datasets.
   */
  public function test() {
    $this->drupalGet('home');
    $session = $this->getSession();
    $this->assertEquals(200, $session->getStatusCode(), $session->getPage()->getHtml());

    $this->drupalGet('dataset/123');
    $session = $this->getSession();
    $this->assertEquals(404, $session->getStatusCode(), $session->getPage()->getHtml());
  }

}
