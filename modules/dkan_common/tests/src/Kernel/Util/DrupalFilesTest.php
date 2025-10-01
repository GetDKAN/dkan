<?php

namespace Drupal\Tests\common\Kernel\Util;

use Drupal\common\Util\DrupalFiles;
use Drupal\KernelTests\KernelTestBase;

/**
 * @covers \Drupal\common\Util\DrupalFiles
 * @coversDefaultClass \Drupal\common\Util\DrupalFiles
 *
 * @group dkan
 * @group common
 * @group kernel
 */
class DrupalFilesTest extends KernelTestBase {

  protected static $modules = [
    'common',
  ];

  public static function provideExceptions() {
    return [
      ['Only file:// and http(s) urls are supported', 'badscheme://', 'any_destination'],
      ["Only moving files to Drupal's public directory (public://) is supported", 'file://', 'badscheme://'],
    ];
  }

  /**
   * @covers ::retrieveFile
   *
   * @dataProvider provideExceptions
   */
  public function testExceptions($exception_message, $url, $destination) {
    /** @var \Drupal\common\Util\DrupalFiles $drupal_files */
    $drupal_files = $this->container->get('dkan.common.drupal_files');
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage($exception_message);
    $drupal_files->retrieveFile($url, $destination);
  }

  public static function provideRetrieve() {
    return [
      ['http://'],
      ['https://'],
    ];
  }

  /**
   * @covers ::retrieveFile
   *
   * @dataProvider provideRetrieve
   */
  public function testHttpSource($url) {
    // We're checking the internal logic of retrieveFile(), to make sure it
    // calls retrieveRemoteFile() given the inputs, and not testing whether the
    // file is successfully retrieved.
    // Mock a DrupalFiles object so that we can mock retrieveRemoteFile().
    $drupal_files = $this->getMockBuilder(DrupalFiles::class)
      ->setConstructorArgs([
        $this->container->get('file_system'),
        $this->container->get('stream_wrapper_manager'),
        $this->container->get('http_client_factory'),
        $this->container->get('dkan.common.logger_channel'),
      ])
      ->onlyMethods(['retrieveRemoteFile'])
      ->getMock();
    $drupal_files->expects($this->once())
      ->method('retrieveRemoteFile')
      ->willReturn('/your/fake/path');

    $this->assertEquals(
      '/your/fake/path',
      $drupal_files->retrieveFile($url, 'public://')
    );
  }

}
