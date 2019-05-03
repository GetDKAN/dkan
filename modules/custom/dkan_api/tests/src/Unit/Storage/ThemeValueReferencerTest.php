<?php

namespace Drupal\Tests\dkan_api\Unit\Storage;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_api\Storage\ThemeValueReferencer;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;
use stdClass;

/**
 * Tests Drupal\dkan_api\Storage\ThemeValueReferencer.
 *
 * @coversDefaultClass \Drupal\dkan_api\Storage\ThemeValueReferencer
 * @group dkan_api
 */
class ThemeValueReferencerTest extends DkanTestBase {

  /**
   * Tests the constructor.
   */
  public function testConstruct() {
    // Setup.
    $mock = $this->getMockBuilder(ThemeValueReferencer::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockEntityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $mockUuidInterface     = $this->createMock(UuidInterface::class);
    $mockQueueFactory      = $this->createMock(QueueFactory::class);

    // Assert.
    $mock->__construct($mockEntityTypeManager, $mockUuidInterface, $mockQueueFactory);

    $this->assertSame($mockEntityTypeManager, $this->readAttribute($mock, 'entityTypeManager'));
    $this->assertSame($mockUuidInterface, $this->readAttribute($mock, 'uuidService'));
    $this->assertSame($mockQueueFactory, $this->readAttribute($mock, 'queueService'));
  }

  /**
   * Provides data for testing referenceSingle function.
   */
  public function dataTestReferenceSingle() {
    $mockNode = $this->createMock(NodeInterface::class);
    $expected = uniqid('a-uuid');
    $mockNode->uuid = (object) ['value' => $expected];

    return [
        ['foobar', [$mockNode], $expected],
        ['barfoo', [], NULL],
    ];
  }

  /**
   * Tests the referenceSingle function.
   *
   * @dataProvider dataTestReferenceSingle
   *
   * @param string $theme
   *   Theme uuid.
   * @param array $nodes
   *   Array of node objects with uuid->value properties.
   * @param mixed $expected
   *   Expected result.
   */
  public function testReferenceSingle(string $theme, array $nodes, $expected) {
    // Setup.
    $mock = $this->getMockBuilder(ThemeValueReferencer::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockEntityTypeManager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->setMethods([
        'getStorage',
      ])
      ->getMockForAbstractClass();

    $this->writeProtectedProperty($mock, 'entityTypeManager', $mockEntityTypeManager);

    $mockNodeStorage = $this->getMockBuilder(EntityStorageInterface::class)
      ->setMethods([
        'loadByProperties',
      ])
      ->getMockForAbstractClass();

    // Expect.
    $mockEntityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($mockNodeStorage);

    $mockNodeStorage->expects($this->once())
      ->method('loadByProperties')
      ->with([
        'field_data_type' => "theme",
        'title'           => $theme,
      ])
      ->willReturn($nodes);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'referenceSingle', $theme);

    $this->assertEquals($expected, $actual);
  }

  /**
   * Provides a list of old and new json strings to test themes being removed.
   */
  public function dataTestThemesRemoved() {
    return [
      ['{}', '{}', []],
      ['{"theme":["Theme One"]}', '{"key":"value"}', ['Theme One']],
      ['{"theme":["Theme One", "Theme Two"]}', '{"theme":["Theme One"]}', [1 => 'Theme Two']],
    ];
  }

  /**
   * Tests the themesRemoved function.
   *
   * @dataProvider dataTestThemesRemoved
   *
   * @param string $old
   *   Existing json string.
   * @param string $new
   *   Incoming json string.
   * @param array $expected
   *   Expected array containing themes removed between old and new.
   */
  public function testThemesRemoved($old, $new, array $expected) {
    // Setup.
    $mock = $this->getMockBuilder(ThemeValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();

    // Assert.
    $this->assertEquals($expected, $mock->themesRemoved($old, $new));
  }

  /**
   * Provides test data for function dereferenceSingle.
   */
  public function dataTestDereferenceSingle() {
    $mockNode = $this->createMock(NodeInterface::class);
    $expected = uniqid('a-theme');
    $mockNode->title = (object) ['value' => $expected];

    return [
      ['foobar', [$mockNode], $expected],
      ['a-uuid-that-does-not-exist', [], 'a-uuid-that-does-not-exist'],
    ];
  }

  /**
   * Tests the dereferenceSingle function.
   *
   * @dataProvider dataTestDereferenceSingle
   *
   * @param string $str
   *   The human-readable theme value or a theme uuid.
   * @param array $nodes
   *   An array of node objects with title->value property.
   * @param mixed $expected
   *   Expected result from dereferenceSingle when passed test data.
   */
  public function testDereferenceSingle($str, array $nodes, $expected) {
    $mock = $this->getMockBuilder(ThemeValueReferencer::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockEntityTypeManager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->setMethods([
        'getStorage',
      ])
      ->getMockForAbstractClass();

    $this->writeProtectedProperty($mock, 'entityTypeManager', $mockEntityTypeManager);

    $mockNodeStorage = $this->getMockBuilder(EntityStorageInterface::class)
      ->setMethods([
        'loadByProperties',
      ])
      ->getMockForAbstractClass();

    // Expect.
    $mockEntityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($mockNodeStorage);

    $mockNodeStorage->expects($this->once())
      ->method('loadByProperties')
      ->with([
        'field_data_type' => "theme",
        'uuid'           => $str,
      ])
      ->willReturn($nodes);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'dereferenceSingle', $str);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Provides data without or empty themes to test function dereference.
   */
  public function dataTestDereferenceWithoutThemes() {
    return [
      [
        (object) ["non-theme" => "some value"],
        NULL,
      ],
      [
        (object) ["theme" => "a string"],
        NULL,
      ],
      [
        (object) ["theme" => []],
        NULL,
      ],
    ];
  }

  /**
   * Tests function dereference without valid theme values.
   *
   * @dataProvider dataTestDereferenceWithoutThemes
   *
   * @param \stdClass $data
   *   Object created from json data.
   * @param mixed $expected
   *   Expected result from dereference when passed test data.
   */
  public function testDereferenceWithoutThemes(\stdClass $data, $expected) {
    // Setup.
    $mock = $this->getMockBuilder(ThemeValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();

    // Assert.
    $this->assertEquals($expected, $mock->dereference($data));
  }

  /**
   * Tests function dereference with valid theme values.
   */
  public function testDereferenceWithThemes() {
    // Setup.
    $mock = $this->getMockBuilder(ThemeValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods(['dereferenceSingle'])
      ->getMock();

    $data = (object) ["theme" => [uniqid('theme-with-uuid-')]];
    $expected = 'corresponding-uuid';

    // Expect.
    $mock->expects($this->once())
      ->method('dereferenceSingle')
      ->with($data->theme[0])
      ->willReturn($expected);

    // Assert.
    $this->assertEquals([$expected], $mock->dereference($data));
  }

  /**
   * Provides data without or empty themes to test function reference.
   */
  public function dataTestReferenceWithoutThemes() {
    return [
      [
        (object) ["non-theme" => "some value"],
        NULL,
      ],
      [
        (object) ["theme" => "a string"],
        NULL,
      ],
      [
        (object) ["theme" => []],
        [],
      ],
    ];
  }

  /**
   * Tests function reference without valid themes.
   *
   * @dataProvider dataTestReferenceWithoutThemes
   *
   * @param \stdClass $data
   *   Object created from json data.
   * @param mixed $expected
   *   Expected result from dereference when passed test data.
   */
  public function testReferenceWithoutThemes(\stdClass $data, $expected) {
    // Setup.
    $mock = $this->getMockBuilder(ThemeValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();

    // Assert.
    $this->assertEquals($expected, $mock->reference($data));
  }

  /**
   * Tests function reference with valid theme values.
   */
  public function testReferenceWithExistingThemes() {
    // Setup.
    $mock = $this->getMockBuilder(ThemeValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods(['referenceSingle'])
      ->getMock();

    $data = (object) ["theme" => [uniqid('theme-with-uuid-')]];
    $expect = 'corresponding-uuid';

    // Expect.
    $mock->expects($this->once())
      ->method('referenceSingle')
      ->with($data->theme[0])
      ->willReturn($expect);

    // Assert.
    $this->assertEquals([$expect], $mock->reference($data));
  }

  /**
   * Tests the creation of new theme references.
   */
  public function testReferenceCreatingNewThemeReference() {
    // Setup.
    $mock = $this->getMockBuilder(ThemeValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods(['referenceSingle', 'createThemeReference'])
      ->getMock();

    $data = (object) ["theme" => [uniqid('theme-with-uuid-')]];
    $expect = 'corresponding-uuid';

    // Expect.
    $mock->expects($this->once())
      ->method('referenceSingle')
      ->with($data->theme[0])
      ->willReturn(NULL);
    $mock->expects($this->once())
      ->method('createThemeReference')
      ->with($data->theme[0])
      ->willReturn($expect);

    // Assert.
    $this->assertEquals([$expect], $mock->reference($data));
  }

  /**
   * Tests the outcome of failing to create a new theme reference, resulting
   * in the theme value remaining its human-readable string.
   */
  public function testReferenceFailsAtCreatingNewThemeReference() {
    // Setup.
    $mock = $this->getMockBuilder(ThemeValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods(['referenceSingle', 'createThemeReference'])
      ->getMock();

    $human_readable_value = uniqid('human-readable-theme-value');
    $data = (object) ["theme" => [$human_readable_value]];

    // Expect.
    $mock->expects($this->once())
      ->method('referenceSingle')
      ->with($data->theme[0])
      ->willReturn(NULL);
    $mock->expects($this->once())
      ->method('createThemeReference')
      ->with($data->theme[0])
      ->willReturn(FALSE);

    // Assert.
    $this->assertEquals([$human_readable_value], $mock->reference($data));
  }

  /**
   * Tests the processDeletedThemes function.
   */
  public function testProcessDeletedThemes() {
    // Setup.
    $mock = $this->getMockBuilder(ThemeValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'themesRemoved',
      ])
      ->getMock();
    $mockQueueFactory = $this->getMockBuilder(QueueFactory::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'get',
      ])
      ->getMockForAbstractClass();
    $this->writeProtectedProperty($mock, 'queueService', $mockQueueFactory);

    $mockQueueInterface = $this->getMockBuilder(QueueInterface::class)
      ->setMethods([
        'createItem',
      ])
      ->getMockForAbstractClass();

    $old = '{"theme":["Theme One", "Theme Two", "Theme Three"]}';
    $new = '{"theme":["Theme Two"]}';
    $themes_removed = ["Theme One", "Theme Three"];

    // Expect.
    $mock->expects($this->once())
      ->method('themesRemoved')
      ->with($old, $new)
      ->willReturn($themes_removed);
    $mockQueueFactory->expects($this->atLeastOnce())
      ->method('get')
      ->with('orphan_theme_processor')
      ->willReturn($mockQueueInterface);
    $mockQueueInterface->expects($this->exactly(2))
      ->method('createItem')
      ->withConsecutive([$themes_removed[0]], [$themes_removed[1]])
      ->willReturnOnConsecutiveCalls(TRUE, TRUE);

    $mock->processDeletedThemes($old, $new);
  }

  /**
   * Tests the createThemeReference function.
   */
  public function testCreateThemeReference() {
    // Setup.
    $mock = $this->getMockBuilder(ThemeValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();

    $mockUuidInterface = $this->getMockBuilder(UuidInterface::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'generate',
      ])
      ->getMockForAbstractClass();
    $this->writeProtectedProperty($mock, 'uuidService', $mockUuidInterface);

    $mockEntityTypeManager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->setMethods([
        'getStorage',
      ])
      ->getMockForAbstractClass();
    $this->writeProtectedProperty($mock, 'entityTypeManager', $mockEntityTypeManager);

    $mockNodeStorage = $this->getMockBuilder(EntityStorageInterface::class)
      ->setMethods([
        'create',
      ])
      ->getMockForAbstractClass();

    $mockEntityInterface = $this->getMockBuilder(EntityInterface::class)
      ->setMethods([
        'save',
        'uuid',
      ])
      ->getMockForAbstractClass();

    $theme = uniqid('some-theme-');
    $uuid = uniqid('some-uuid-');
    $today = date('Y-m-d');
    $data = new stdClass();
    $data->title = $theme;
    $data->identifier = $uuid;
    $data->created = $today;
    $data->modified = $today;

    // Expect.
    $mockUuidInterface->expects($this->once())
      ->method('generate')
      ->willReturn($uuid);
    $mockEntityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($mockNodeStorage);
    $mockNodeStorage->expects($this->once())
      ->method('create')
      ->with([
        'title' => $theme,
        'type' => 'data',
        'uuid' => $uuid,
        'field_data_type' => 'theme',
        'field_json_metadata' => json_encode($data),
      ])
      ->willReturn($mockEntityInterface);
    $mockEntityInterface->expects($this->once())
      ->method('save')
      ->willReturn(1);
    $mockEntityInterface->expects($this->once())
      ->method('uuid')
      ->willReturn($uuid);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'createThemeReference', $theme);
    $this->assertEquals($uuid, $actual);
  }

}
