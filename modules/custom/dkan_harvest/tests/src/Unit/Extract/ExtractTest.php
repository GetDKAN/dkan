<?php

namespace Drupal\Tests\dkan_harvest\Unit\Extract;

use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_harvest\Extract\Extract;
use Drupal\dkan_harvest\Load\IFileHelper;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use org\bovigo\vfs\vfsStream;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;

/**
 * Tests Drupal\dkan_harvest\Extract\DataJson.
 *
 * @coversDefaultClass Drupal\dkan_harvest\Extract\DataJson
 * @group dkan_harvest
 */
class ExtractTest extends DkanTestBase {

  /**
   * Tests __construct().
   */
  public function testConstruct() {
    // Setup.
    $mock = $this->getMockBuilder(Extract::class)
      ->disableOriginalConstructor()
      ->setMethods(['getFileHelper'])
      ->getMockForAbstractClass();

    $mockFileHelper = $this->getMockBuilder(IFileHelper::class)
      ->setMethods(['defaultSchemeDirectory'])
      ->getMockForAbstractClass();

    $harvest_info = (object) [
      'sourceId' => 42,
      'source' => (object) [
        'uri' => 'http://foo.bar',
      ],
    ];
    $schemaDir = '/foo';

    // Expect.
    $mock->expects($this->once())
      ->method('getFileHelper')
      ->willReturn($mockFileHelper);

    $mockFileHelper->expects($this->once())
      ->method('defaultSchemeDirectory')
      ->willReturn($schemaDir);

    // Assert.
    $mock->__construct($harvest_info);
    $this->assertEquals($harvest_info->source->uri, $this->readAttribute($mock, 'uri'));
    $this->assertEquals($schemaDir . '/dkan_harvest/', $this->readAttribute($mock, 'folder'));
    $this->assertEquals($harvest_info->sourceId, $this->readAttribute($mock, 'sourceId'));
  }

  /**
   * Tests httpRequest().
   */
  public function testHttpRequest() {
    // Setup.
    $mock = $this->getMockBuilder(Extract::class)
      ->disableOriginalConstructor()
      ->setMethods(['getHttpClient'])
      ->getMockForAbstractClass();

    $mockHttpClient = $this->getMockBuilder(ClientInterface::class)
      ->setMethods(['get'])
      ->getMockForAbstractClass();

    $mockResponseInterface = $this->getMockBuilder(ResponseInterface::class)
      ->setMethods(['getBody'])
      ->getMockForAbstractClass();

    $uri = 'foo://bar/resource';
    $expected = 'foobar';

    // Expect.
    $mock->expects($this->once())
      ->method('getHttpClient')
      ->willReturn($mockHttpClient);

    $mockHttpClient->expects($this->once())
      ->method('get')
      ->with($uri)
      ->willReturn($mockResponseInterface);

    $mockResponseInterface->expects($this->once())
      ->method('getBody')
      ->willReturn($expected);
    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'httpRequest', $uri);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests httpRequest() with exception condition.
   */
  public function testHttpRequestException() {
    // Setup.
    $mock = $this->getMockBuilder(Extract::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'getHttpClient',
        'log',
      ])
      ->getMockForAbstractClass();

    $mockHttpClient = $this->getMockBuilder(ClientInterface::class)
      ->setMethods(['get'])
      ->getMockForAbstractClass();
    $uri = 'foo://bar/resource';

    $mockRequestException = new RequestException(
            'message irrelevant',
            $this->createMock(RequestInterface::class)
    );

    // Expect.
    $mock->expects($this->once())
      ->method('getHttpClient')
      ->willReturn($mockHttpClient);

    $mockHttpClient->expects($this->once())
      ->method('get')
      ->with($uri)
      ->willThrowException($mockRequestException);

    $mock->expects($this->once())
      ->method('log')
      ->with('ERROR', 'Extract', 'Error reading ' . $uri);
    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'httpRequest', $uri);
  }

  /**
   * Tests writeToFile().
   */
  public function testWriteToFile() {
    // Setup.
    // Init vfsstream.
    $fileSystem = vfsStream::setup('x');

    $mock = $this->getMockBuilder(Extract::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $sourceId = 'foobar';
    $this->writeProtectedProperty($mock, 'folder', $fileSystem->url());
    $this->writeProtectedProperty($mock, 'sourceId', $sourceId);

    $id = 42;
    $item = '{"test":"data"}';

    $expectedDir = $fileSystem->url() . '/' . $sourceId;
    $expectedFile = $expectedDir . '/' . $id . '.json';

    // Assert.
    $this->invokeProtectedMethod($mock, 'writeToFile', $id, $item);
    $this->assertDirectoryExists($expectedDir);
    $this->assertDirectoryIsReadable($expectedDir);
    $this->assertDirectoryIsWritable($expectedDir);

    $this->assertFileExists($expectedFile);
    $this->assertEquals($item, file_get_contents($expectedFile));
  }

  /**
   * Tests writeToFile().
   */
  public function testWriteToFileException() {
    // Setup.
    // init vfsstream
    // make it non writable.
    $fileSystem = vfsStream::setup('x', 0444);

    $mock = $this->getMockBuilder(Extract::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $sourceId = 'foobar';
    $this->writeProtectedProperty($mock, 'folder', $fileSystem->url());
    $this->writeProtectedProperty($mock, 'sourceId', $sourceId);

    $id = 42;
    $item = '{"test":"data"}';

    $expectedDir = $fileSystem->url() . '/' . $sourceId;
    $expectedFile = $expectedDir . '/' . $id . '.json';

    // Assert.
    $this->invokeProtectedMethod($mock, 'writeToFile', $id, $item);
    $this->assertDirectoryNotExists($expectedDir);
    $this->assertFileNotExists($expectedFile);
  }

}
