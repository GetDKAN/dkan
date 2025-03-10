<?php

namespace Drupal\Tests\metastore\Kernel\Reference;

use ColinODell\PsrTestLogger\TestLogger;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * @covers \Drupal\metastore\Reference\Referencer
 * @coversDefaultClass \Drupal\metastore\Reference\Referencer
 *
 * @group dkan
 * @group metastore
 * @group kernel
 */
class ReferencerTest extends KernelTestBase {

  protected $strictConfigSchema = FALSE;

  protected static $modules = [
    'common',
    'metastore',
  ];

  /**
   * @covers ::getLocalMimeType
   */
  public function testGetLocalMimeTypeLogging() {
    // Test logger we can assert against.
    $logger = new TestLogger();
    $this->container->set('dkan.common.logger_channel', $logger);

    // The guesser service always returns NULL.
    $guesser = $this->getMockBuilder(MimeTypeGuesserInterface::class)
      ->onlyMethods(['guessMimeType'])
      ->getMockForAbstractClass();
    $guesser->expects($this->any())
      ->method('guessMimeType')
      ->willReturn(NULL);
    $this->container->set('file.mime_type.guesser.extension', $guesser);

    $referencer = $this->container->get('dkan.metastore.referencer');

    $ref_get_local_mime_type = new \ReflectionMethod($referencer, 'getLocalMimeType');
    $ref_get_local_mime_type->setAccessible(TRUE);

    $this->assertNull(
      $ref_get_local_mime_type->invokeArgs($referencer, ['download/url/file.txt'])
    );
    $this->assertTrue(
      $logger->hasErrorThatContains('Unable to determine mime type of file with name')
    );
  }

}
