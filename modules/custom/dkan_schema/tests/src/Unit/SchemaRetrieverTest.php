<?php

namespace Drupal\Tests\dkan_schema\Unit;

use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_schema\SchemaRetriever;
use org\bovigo\vfs\vfsStream;
use Drupal\Core\Extension\ExtensionList;

/**
 * Tests Drupal\dkan_schema\SchemaRetriever.
 *
 * @coversDefaultClass Drupal\dkan_schema\SchemaRetriever
 * @group dkan_harvest
 */
class SchemaRetrieverTest extends DkanTestBase {

  /**
   * Tests __construct().
   */
  public function testConstruct() {
    // Setup.
    $mock = $this->getMockBuilder(SchemaRetriever::class)
      ->setMethods(['findSchemaDirectory'])
      ->disableOriginalConstructor()
      ->getMock();

    // Expect.
    $mock->expects($this->once())
      ->method('findSchemaDirectory');

    // Assert.
    $mock->__construct();
  }

  /**
   * Tests getAllIds().
   */
  public function testGetAllIds() {
    // Setup.
    $mock = $this->getMockBuilder(SchemaRetriever::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();
    // Assert.
    $this->assertEquals($mock->getAllIds(), [
      'dataset',
    ]);
  }

  /**
   * Tests getSchemaDirectory().
   */
  public function testGetSchemaDirectory() {
    // Setup.
    $mock = $this->getMockBuilder(SchemaRetriever::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $expected = '/foo/bar';
    $this->writeProtectedProperty($mock, 'directory', $expected);
    // Assert.
    $this->assertEquals($expected, $mock->getSchemaDirectory());
  }

  /**
   * Data provider for testRetrieveException.
   *
   * @return array Arguments.
   */
  public function dataTestRetrieveException() {

    return [
        // Not valid id.
        [
          'foo-id-not-valid',
            [],
          'directory',
          NULL,
            [],
        ],
        // Not readable.
        [
          'foo-not-readable',
            ['foo-not-readable'],
          'directory',
          NULL,
            [],
        ],
    ];
  }

  /**
   * Tests retrieve() for exception conditions.
   *
   * @dataProvider dataTestRetrieveException
   *
   * @param string $id
   * @param array $allIds
   * @param string directory
   * @param int $vfsPermissions
   * @param array $vfsStructure
   *   filesystem definition as used by vfsstream.
   */
  public function testRetrieveException(string $id, array $allIds, string $directory, $vfsPermissions, array $vfsStructure) {
    // Setup.
    $mock = $this->getMockBuilder(SchemaRetriever::class)
      ->setMethods([
        'getSchemaDirectory',
        'getAllIds',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $vfs = vfsStream::setup('root', $vfsPermissions, $vfsStructure);

    // Expect.
    $mock->expects($this->once())
      ->method('getSchemaDirectory')
      ->willReturn($vfs->url() . '/' . $directory);

    $mock->expects($this->once())
      ->method('getAllIds')
      ->willReturn($allIds);

    $this->setExpectedException(\Exception::class, "Schema {$id} not found.");

    // Assert.
    $mock->retrieve($id);
  }

  /**
   * Tests retrieve().
   */
  public function testRetrieve() {
    // Setup.
    $mock = $this->getMockBuilder(SchemaRetriever::class)
      ->setMethods([
        'getSchemaDirectory',
        'getAllIds',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $id = uniqid('id');

    $expected       = '{foobar contents}';
    $allIds         = [$id];
    $vfsPermissions = 0777;
    $vfsStructure   = [
        // Need to trim off `/` for vfs.
      'foo' => [
        'collections' => [
          "{$id}.json" => $expected,
        ],
      ],
    ];

    $vfs       = vfsStream::setup('root', $vfsPermissions, $vfsStructure);
    $directory = $vfs->url() . '/foo';
    // Expect.
    $mock->expects($this->once())
      ->method('getSchemaDirectory')
      ->willReturn($directory);

    $mock->expects($this->once())
      ->method('getAllIds')
      ->willReturn($allIds);

    // Assert.
    $actual = $mock->retrieve($id);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests findSchemaDirectory when schema dir is in drupal root.
   */
  public function testFindSchemDirectorySchemaInDrupalRoot() {
    // Setup.
    $mock = $this->getMockBuilder(SchemaRetriever::class)
      ->setMethods(['getDefaultSchemaDirectory'])
      ->disableOriginalConstructor()
      ->getMock();

    $vfs = vfsStream::setup(uniqid('vfs'), NULL, [
      'schema' => [],
    ]);

    $this->setActualContainer([
      'app.root' => $vfs->url(),
    ]);

    $expected = $vfs->url() . '/schema';

    // Expect.
    $mock->expects($this->never())
      ->method('getDefaultSchemaDirectory');

    // Assert.
    $this->invokeProtectedMethod($mock, 'findSchemaDirectory');
    $this->assertEquals($expected, $this->readAttribute($mock, 'directory'));
  }

  /**
   * Tests findSchemaDirectory() when using fallback schema.
   */
  public function testFindSchemDirectoryUseDefaultFallback() {
    // Setup.
    $mock = $this->getMockBuilder(SchemaRetriever::class)
      ->setMethods(['getDefaultSchemaDirectory'])
      ->disableOriginalConstructor()
      ->getMock();

    $vfs = vfsStream::setup(uniqid('vfs'), NULL, [
      'schema' => [],
    ]);

    $this->setActualContainer([
      'app.root' => uniqid('/foo-this-is-not-valid'),
    ]);

    $expected = $vfs->url();

    // Expect.
    $mock->expects($this->once())
      ->method('getDefaultSchemaDirectory')
      ->willReturn($expected);

    // Assert.
    $this->invokeProtectedMethod($mock, 'findSchemaDirectory');
    $this->assertEquals($expected, $this->readAttribute($mock, 'directory'));
  }

  /**
   * Tests findSchemaDirectory() for exception condition.
   */
  public function testFindSchemDirectoryException() {
    // Setup.
    $mock = $this->getMockBuilder(SchemaRetriever::class)
      ->setMethods(['getDefaultSchemaDirectory'])
      ->disableOriginalConstructor()
      ->getMock();

    $vfs = vfsStream::setup(uniqid('vfs'), NULL, [
      'schema' => [],
    ]);

    $this->setActualContainer([
      'app.root' => uniqid('/foo-this-is-not-valid'),
    ]);

    $this->setExpectedException(\Exception::class, "No schema directory found.");

    // Expect.
    $mock->expects($this->once())
      ->method('getDefaultSchemaDirectory')
      ->willReturn(uniqid('/foo-this-is-not-valid-either'));

    // Assert.
    $this->invokeProtectedMethod($mock, 'findSchemaDirectory');
  }

  /**
   * Tests getDefaultSchemaDirectory().
   */
  public function testGetDefaultSchemaDirectory() {

    $mock = $this->getMockBuilder(SchemaRetriever::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $infoFile = '/foo/bar/dkan2.yml';
    $expected = '/foo/bar/schema';

    $mockExtensionList = $this->getMockBuilder(ExtensionList::class)
      ->setMethods(['getPathname'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $this->setActualContainer([
      'extension.list.profile' => $mockExtensionList,
    ]);

    // Expects.
    $mockExtensionList->expects($this->once())
      ->method('getPathname')
      ->with('dkan2')
      ->willReturn($infoFile);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getDefaultSchemaDirectory');
    $this->assertEquals($expected, $actual);
  }

}
