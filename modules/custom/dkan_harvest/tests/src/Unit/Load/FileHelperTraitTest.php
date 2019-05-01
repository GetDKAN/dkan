<?php

namespace Drupal\Tests\dkan_harvest\Unit\Extract;

use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_harvest\Load\FileHelperTrait;
use Drupal\dkan_harvest\Load\IFileHelper;

/**
 * Tests Drupal\dkan_harvest\Load\FileHelperTrait.
 *
 * @coversDefaultClass Drupal\dkan_harvest\Load\FileHelperTrait
 * @group dkan_harvest
 */
class FileHelperTraitTest extends DkanTestBase {

  /**
   * Tests getFileHelper().
   */
  public function testGetFileHelper() {
    // Setup.
    $mock = $this->getMockBuilder(FileHelperTrait::class)
      ->setMethods(NULL)
      ->getMockForTrait();

    $mockFileHelper = $this->createMock(IFileHelper::class);

    $this->setActualContainer([
      'dkan_harvest.file_helper' => $mockFileHelper,
    ]);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getFileHelper');

    $this->assertSame($mockFileHelper, $actual);
  }

}
