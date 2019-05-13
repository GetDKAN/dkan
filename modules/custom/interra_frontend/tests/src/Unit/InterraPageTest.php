<?php

namespace Drupal\Tests\interra_frontend\Unit;

use Drupal\interra_frontend\InterraPage;
use Drupal\dkan_common\Tests\DkanTestBase;
use org\bovigo\vfs\vfsStream;

/**
 * Tests Drupal\interra_frontend\InterraPage.
 *
 * @coversDefaultClass Drupal\interra_frontend\InterraPage
 * @group interra_frontend
 */
class InterraPageTest extends DkanTestBase {

  /**
   * Tests build().
   */
  public function testBuild() {
    // Setup.
    $mock = $this->getMockBuilder(InterraPage::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $expected = '<html>something like that</html>';
    $vfs      = vfsStream::setup('root', NULL, [
      'data-catalog-frontend' => [
        'build' => [
          'index.html' => $expected,
        ],
      ],
    ]);
    $appRoot  = $vfs->url();

    $this->setActualContainer([
      'app.root' => $appRoot,
    ]);

    // Assert.
    $this->assertEquals($expected, $mock->build());
  }

  /**
   * Tests build() on fail conditions.
   */
  public function testBuildNotFound() {
    // Setup.
    $mock = $this->getMockBuilder(InterraPage::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $expected = FALSE;
    $vfs      = vfsStream::setup('root');
    $appRoot  = $vfs->url();

    $this->setActualContainer([
      'app.root' => $appRoot,
    ]);

    // Assert.
    $this->assertEquals($expected, $mock->build());
  }

}
