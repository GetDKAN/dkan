<?php

namespace Drupal\Tests\datastore\Unit;

use Drupal\datastore\DatastoreResource;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * @covers \Drupal\datastore\DatastoreResource
 * @coversDefaultClass \Drupal\datastore\DatastoreResource
 */
class DatastoreResourceTest extends TestCase {

  public function provideGetEolToken() {
    return [
      [NULL, "\n", 'no_line_ending'],
      ['\r\n', "\r\n", "ending\r\n"],
      ['\r', "\r", "ending\r"],
      ['\n', "\n", "ending\n"],
    ];
  }

  /**
   * @covers ::getEolToken
   * @covers ::getEol
   * @dataProvider provideGetEolToken
   */
  public function testGetEolToken($expected_token, $expected_eol, $string) {
    $resource = $this->getMockBuilder(DatastoreResource::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getColsFromFile'])
      ->getMock();
    $resource->method('getColsFromFile')
      ->willReturn(['unused', $string]);

    $this->assertSame($expected_token, $resource->getEolToken());
    $this->assertSame($expected_eol, $resource->getEol());
  }

  public function testGetColsFromFileBadFile() {
    $this->expectException(FileException::class);
    $this->expectExceptionMessage('Failed to open resource file "vfs://root/file.csv"');

    // Create an unreadable file in memory.
    $root = vfsStream::setup('root');
    vfsStream::newFile('file.csv', 0000)
      ->at($root)
      ->setContent('yes,no,maybe');

    $resource = $this->getMockBuilder(DatastoreResource::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['realPath'])
      ->getMock();
    $resource->method('realPath')
      // Unreadable realpath.
      ->willReturn(vfsStream::url('root/file.csv'));

    $resource->getColsFromFile();
  }

  public function provideGetColsFromFile() {
    return [
      [['foo', 'bar'], 'foo,bar', 'foo,bar'],
      [['foo', 'bar'], "foo,bar\n", "foo,bar\n"],
    ];
  }

  /**
   * @covers ::getColsFromFile
   * @dataProvider provideGetColsFromFile
   */
  public function testGetColsFromFile($expected_columns, $expected_column_lines, $file_contents) {
    // Create a file in memory.
    $root = vfsStream::setup('root');
    vfsStream::newFile('file.csv')
      ->at($root)
      ->setContent($file_contents);

    $resource = $this->getMockBuilder(DatastoreResource::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['realPath', 'getDelimiter'])
      ->getMock();
    $resource->method('realPath')
      ->willReturn(vfsStream::url('root/file.csv'));
    $resource->method('getDelimiter')
      ->willReturn(',');

    [$columns, $column_lines] = $resource->getColsFromFile();
    $this->assertEquals($expected_columns, $columns);
    $this->assertEquals($expected_column_lines, $column_lines);
  }

  /**
   * @covers ::jsonSerialize
   */
  public function testJsonSerialize() {
    $this->assertEquals(
      '{"filePath":"file_path","id":"id","mimeType":"mime_type"}',
      json_encode(new DatastoreResource('id', 'file_path', 'mime_type'))
    );

  }

}
