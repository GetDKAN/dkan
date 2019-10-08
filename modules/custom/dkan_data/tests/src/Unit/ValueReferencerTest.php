<?php

namespace Drupal\Tests\dkan_data\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_data\ValueReferencer;
use Drupal\dkan_data\Service\Uuid5;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;
use stdClass;

/**
 * Tests Drupal\dkan_dataValueReferencer.
 *
 * @coversDefaultClass \Drupal\dkan_data\ValueReferencer
 * @group dkan_data
 */
class ValueReferencerTest extends DkanTestBase {

  /**
   * Tests the constructor.
   */
  public function testConstruct() {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockEntityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $mockUuidInterface     = $this->createMock(Uuid5::class);
    $mockConfigInterface   = $this->createMock(ConfigFactoryInterface::class);
    $mockQueueFactory      = $this->createMock(QueueFactory::class);

    // Assert.
    $mock->__construct($mockEntityTypeManager, $mockUuidInterface, $mockConfigInterface, $mockQueueFactory);

    $this->assertSame($mockEntityTypeManager, $this->readAttribute($mock, 'entityTypeManager'));
    $this->assertSame($mockUuidInterface, $this->readAttribute($mock, 'uuidService'));
    $this->assertSame($mockConfigInterface, $this->readAttribute($mock, 'configService'));
    $this->assertSame($mockQueueFactory, $this->readAttribute($mock, 'queueService'));
  }

  /**
   * Provides data for testing checkExistingReference function.
   */
  public function dataTestCheckExistingReference() {
    $mockNode = $this->createMock(NodeInterface::class);
    $expected = uniqid('a-uuid');
    $mockNode->uuid = (object) ['value' => $expected];

    return [
      ['theme', 'Topic One', [$mockNode], $expected],
      ['barfoo', '', [], NULL],
    ];
  }

  /**
   * Tests the checkExistingReference function.
   *
   * @param string $property_id
   *   The property name.
   * @param mixed $data
   *   The json value of the property.
   * @param array $nodes
   *   Array of node objects with uuid->value properties.
   * @param mixed $expected
   *   Expected result.
   *
   * @dataProvider dataTestCheckExistingReference
   */
  public function testCheckExistingReference($property_id, $data, array $nodes, $expected) {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockEntityTypeManager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->setMethods(
              [
                'getStorage',
              ]
          )
      ->getMockForAbstractClass();

    $this->writeProtectedProperty($mock, 'entityTypeManager', $mockEntityTypeManager);

    $mockNodeStorage = $this->getMockBuilder(EntityStorageInterface::class)
      ->setMethods(
              [
                'loadByProperties',
              ]
          )
      ->getMockForAbstractClass();

    // Expect.
    $mockEntityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($mockNodeStorage);

    $mockNodeStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(
              [
                'field_data_type' => $property_id,
                'title'           => md5(json_encode($data)),
              ]
          )
      ->willReturn($nodes);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'checkExistingReference', $property_id, $data);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests the createPropertyReference function.
   */
  public function testCreatePropertyReference() {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();

    $mockUuidInterface = $this->getMockBuilder(Uuid5::class)
      ->disableOriginalConstructor()
      ->setMethods(
              [
                'generate',
              ]
          )
      ->getMockForAbstractClass();
    $this->writeProtectedProperty($mock, 'uuidService', $mockUuidInterface);

    $mockEntityTypeManager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->setMethods(
              [
                'getStorage',
              ]
          )
      ->getMockForAbstractClass();
    $this->writeProtectedProperty($mock, 'entityTypeManager', $mockEntityTypeManager);

    $mockNodeStorage = $this->getMockBuilder(EntityStorageInterface::class)
      ->setMethods(
              [
                'create',
              ]
          )
      ->getMockForAbstractClass();

    $mockEntityInterface = $this->getMockBuilder(EntityInterface::class)
      ->setMethods(
              [
                'save',
                'uuid',
              ]
          )
      ->getMockForAbstractClass();

    $property_id = uniqid('some-property-');
    $value = uniqid('some-value-');
    $uuid = Uuid5::generate($property_id, $value);
    $data = new stdClass();
    $data->identifier = $uuid;
    $data->data = $value;

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
      ->with(
              [
                'title' => md5(json_encode($value)),
                'type' => 'data',
                'uuid' => $uuid,
                'field_data_type' => $property_id,
                'field_json_metadata' => json_encode($data),
              ]
          )
      ->willReturn($mockEntityInterface);
    $mockEntityInterface->expects($this->once())
      ->method('save')
      ->willReturn(1);
    $mockEntityInterface->expects($this->once())
      ->method('uuid')
      ->willReturn($uuid);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'createPropertyReference', $property_id, $value);
    $this->assertEquals($uuid, $actual);
  }

  /**
   * Provides data for testing function referenceSingle.
   */
  public function dataTestReferenceSingle() {
    $property_id = 'some-property';
    $value = 'some-value';
    $uuid = uniqid('existing-reference-uuid-');

    return [
      'found an existing reference' => [
        $property_id, $value, $uuid, NULL, $uuid,
      ],
      'created a new reference' => [
        $property_id, $value, NULL, $uuid, $uuid,
      ],
      'neither found existing nor created new reference' => [
        $property_id, $value, NULL, NULL, $value,
      ],
    ];
  }

  /**
   * Tests function referenceSingle with existing value reference.
   *
   * @param string $property_id
   *   The property name.
   * @param string $value
   *   The json value of the property.
   * @param string|null $checkExisting
   *   The expected value of checkExistingReference.
   * @param string|null $createProperty
   *   The expected value of createPropertyReference.
   * @param string $expected
   *   The expected return value of referenceSingle.
   *
   * @dataProvider dataTestReferenceSingle
   */
  public function testReferenceSingle(string $property_id, $value, $checkExisting, $createProperty, $expected) {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods(['checkExistingReference', 'createPropertyReference'])
      ->getMock();

    // Expect.
    $mock->expects($this->exactly(1))
      ->method('checkExistingReference')
      ->with($property_id, $value)
      ->willReturn($checkExisting);
    $mock->expects($this->any())
      ->method('createPropertyReference')
      ->with($property_id, $value)
      ->willReturn($createProperty);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'referenceSingle', $property_id, $value);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Test function referenceMultiple.
   */
  public function testReferenceMultiple() {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods(['referenceSingle'])
      ->getMock();

    $property_id = 'theme';
    $values = [
      "Topic One",
      "Topic Two",
      "Topic Three",
    ];
    $referenceSingle = [
      "uuid-one",
      "uuid-two",
      "uuid-three",
    ];

    // Expect.
    $mock->expects($this->exactly(3))
      ->method('referenceSingle')
      ->withConsecutive(
              [$property_id, $values[0]],
              [$property_id, $values[1]],
              [$property_id, $values[2]]
          )
      ->willReturnOnConsecutiveCalls(
              $referenceSingle[0],
              $referenceSingle[1],
              $referenceSingle[2]
          );

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'referenceMultiple', $property_id, $values);
    $this->assertEquals($referenceSingle, $actual);
  }

  /**
   * Provides data for testReferenceProperty.
   */
  public function dataTestReferenceProperty() {
    $property_id = 'some-property';
    $uuid = uniqid('existing-reference-uuid-');

    return [
      'data is a string' => [
        $property_id,
        'some-value',
        NULL,
        $uuid,
        $uuid,
      ],
      'data is an object' => [
        $property_id,
        (object) ['some' => 'object'],
        NULL,
        $uuid,
        $uuid,
      ],
      'data is an array' => [
        $property_id,
      ['some value'],
      [$uuid],
        NULL,
      [$uuid],
      ],
    ];
  }

  /**
   * Tests function referenceProperty.
   *
   * @param string $property_id
   *   The property name.
   * @param string|array $data
   *   The json value of the property.
   * @param string|null $refMultiple
   *   The expected value of referenceMultiple.
   * @param string|null $refSingle
   *   The expected value of referenceSingle.
   * @param string|array $expected
   *   The expected return value of referenceProperty.
   *
   * @dataProvider dataTestReferenceProperty
   */
  public function testReferenceProperty(string $property_id, $data, $refMultiple, $refSingle, $expected) {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods(['referenceMultiple', 'referenceSingle'])
      ->getMock();

    // Expect.
    $mock->expects($this->any())
      ->method('referenceMultiple')
      ->with($property_id, $data)
      ->willReturn($refMultiple);
    $mock->expects($this->any())
      ->method('referenceSingle')
      ->with($property_id, $data)
      ->willReturn($refSingle);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'referenceProperty', $property_id, $data);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests function getPropertyList.
   */
  public function testGetPropertyList() {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockConfigService = $this->getMockBuilder(ConfigFactoryInterface::class)
      ->setMethods(['get'])
      ->getMockForAbstractClass();

    $this->writeProtectedProperty($mock, 'configService', $mockConfigService);

    $mockImmutableConfig = $this->getMockBuilder(ImmutableConfig::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMock();

    $this->writeProtectedProperty($mock, 'configService', $mockConfigService);

    $list = [
      "contactPoint" => "contactPoint",
      "theme" => "theme",
      "keyword" => "keyword",
      "other properties" => 0,
      "not interested in" => 0,
    ];
    $expected = [
      "contactPoint",
      "theme",
      "keyword",
    ];

    // Expect.
    $mockConfigService->expects($this->once())
      ->method('get')
      ->with('dkan_data.settings')
      ->willReturn($mockImmutableConfig);

    $mockImmutableConfig->expects($this->once())
      ->method('get')
      ->with('property_list')
      ->willReturn($list);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getPropertyList');
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests function reference.
   */
  public function testReference() {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->setMethods(['getPropertyList', 'referenceProperty'])
      ->disableOriginalConstructor()
      ->getMock();

    $properties_list = [
      'property to be referenced',
      'another to reference',
    ];
    $data = (object) [
      'property to be referenced' => 'Some value',
      'another to reference' => 'Yet another value',
      'other property' => 'Some other value',
    ];
    $uuid1 = uniqid('property1-reference-');
    $uuid2 = uniqid('property2-reference-');
    $expected = (object) [
      'property to be referenced' => $uuid1,
      'another to reference' => $uuid2,
      'other property' => 'Some other value',
    ];

    // Expect.
    $mock->expects($this->once())
      ->method('getPropertyList')
      ->willReturn($properties_list);
    $mock->expects($this->exactly(2))
      ->method('referenceProperty')
      ->withConsecutive(
              [$properties_list[0], $data->{$properties_list[0]}],
              [$properties_list[1], $data->{$properties_list[1]}]
          )
      ->willReturnOnConsecutiveCalls(
              $uuid1,
              $uuid2
          );

    // Assert.
    $this->assertEquals($expected, $mock->reference($data));
  }

  /**
   * Provides data for testDereferenceProperty.
   */
  public function dataTestDereferenceProperty() {
    $property_id = 'some-property';
    $uuid1 = uniqid('property-one-uuid-');
    $value1_retrieved = uniqid('value-one-retrieved-');
    $uuid2 = uniqid('property-two-uuid-');
    $value2_retrieved = uniqid('value-two-retrieved-');

    return [
      'dereferencing a single uuid' => [
        $property_id,
        $uuid1,
        NULL,
        $value1_retrieved,
        $value1_retrieved,
      ],
      'dereferencing an array of uuid' => [
        $property_id,
      [$uuid1, $uuid2],
      [$value1_retrieved, $value2_retrieved],
        NULL,
      [$value1_retrieved, $value2_retrieved],
      ],
    ];
  }

  /**
   * Tests function dereferenceProperty.
   *
   * @param string $property_id
   *   The property name.
   * @param string|array $uuids
   *   One or more uuids from the property's value.
   * @param string $deRefMultiple
   *   The expected value of dereferenceMultiple.
   * @param string $deRefSingle
   *   The expected value of dereferenceSingle.
   * @param string|array $expected
   *   The expected return value of dereferenceProperty.
   *
   * @dataProvider dataTestDereferenceProperty
   */
  public function testDereferenceProperty(string $property_id, $uuids, $deRefMultiple, $deRefSingle, $expected) {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods(['dereferenceMultiple', 'dereferenceSingle'])
      ->getMock();

    // Expect.
    $mock->expects($this->any())
      ->method('dereferenceMultiple')
      ->with($property_id, $uuids)
      ->willReturn($deRefMultiple);
    $mock->expects($this->any())
      ->method('dereferenceSingle')
      ->with($property_id, $uuids)
      ->willReturn($deRefSingle);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'dereferenceProperty', $property_id, $uuids);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests function dereferenceMultiple.
   */
  public function testDereferenceMultiple() {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods(['dereferenceSingle'])
      ->getMock();

    $property_id = 'someProperty';
    $uuids = [
      uniqid('property-uuid1-'),
      uniqid('property-uuid2-'),
      'Third, non-referenced property value',
    ];
    $dereferenceSingle = [
      "First property value",
      "Second property value",
      "Third, non-referenced property value",
    ];

    // Expect.
    $mock->expects($this->exactly(3))
      ->method('dereferenceSingle')
      ->withConsecutive(
              [$property_id, $uuids[0]],
              [$property_id, $uuids[1]],
              [$property_id, $uuids[2]]
          )
      ->willReturnOnConsecutiveCalls(
              $dereferenceSingle[0],
              $dereferenceSingle[1],
              $dereferenceSingle[2]
          );

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'dereferenceMultiple', $property_id, $uuids);
    $this->assertEquals($dereferenceSingle, $actual);
  }

  /**
   * Provides data for testing checkExistingReference function.
   */
  public function datatestDereferenceSingle() {
    $mockNode = $this->createMock(NodeInterface::class);
    $uuid = uniqid('some-property-uuid-');
    $expected = "Some Property Value";
    $mockNode->field_json_metadata = (object) ['value' => '{"uuid": "' . $uuid . '", "data": "Some Property Value"}'];

    return [
      ['someProperty', $uuid, [$mockNode], 1, $expected],
      ['someProperty', $uuid, [$mockNode], 2, (object) ['uuid' => $uuid, 'data' => $expected]],
      ['someProperty', $uuid, [], 0, $uuid],
    ];
  }

  /**
   * Tests function dereferenceSingle.
   *
   * @param string $property_id
   *   The property name.
   * @param string $uuid
   *   The uuid.
   * @param array $nodes
   *   The expected $nodes array internally.
   * @param int $dereferenceMethod
   *   The dereference method, seeking identifier or data.
   * @param string $expected
   *   The expected return value of dereferenceSingle.
   *
   * @dataProvider dataTestDereferenceSingle
   */
  public function testDereferenceSingle(string $property_id, string $uuid, array $nodes, int $dereferenceMethod, $expected) {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();
    $mockEntityTypeManager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->setMethods(
              [
                'getStorage',
              ]
          )
      ->getMockForAbstractClass();
    $this->writeProtectedProperty($mock, 'entityTypeManager', $mockEntityTypeManager);
    $mockNodeStorage = $this->getMockBuilder(EntityStorageInterface::class)
      ->setMethods(
              [
                'loadByProperties',
              ]
          )
      ->getMockForAbstractClass();

    // Expect.
    $mockEntityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($mockNodeStorage);
    $mockNodeStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(
              [
                'field_data_type' => $property_id,
                'uuid'           => $uuid,
              ]
          )
      ->willReturn($nodes);
    $this->writeProtectedProperty($mock, 'dereferenceMethod', $dereferenceMethod);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'dereferenceSingle', $property_id, $uuid);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests function dereference.
   */
  public function testDereference() {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->setMethods(['getPropertyList', 'dereferenceProperty'])
      ->disableOriginalConstructor()
      ->getMock();

    $properties_list = [
      'property to be dereferenced',
      'another to dereference',
    ];
    $uuid1 = uniqid('uuid1-');
    $uuid2 = uniqid('uuid2-');
    $data = (object) [
      'property to be dereferenced' => $uuid1,
      'another to dereference' => $uuid2,
      'other property' => 'Some other value',
    ];
    $expected = (object) [
      'property to be dereferenced' => 'Some value',
      'another to dereference' => 'Yet another value',
      'other property' => 'Some other value',
    ];

    // Expect.
    $mock->expects($this->once())
      ->method('getPropertyList')
      ->willReturn($properties_list);
    $mock->expects($this->exactly(2))
      ->method('dereferenceProperty')
      ->withConsecutive(
              [$properties_list[0], $uuid1],
              [$properties_list[1], $uuid2]
          )
      ->willReturnOnConsecutiveCalls(
              'Some value',
              'Yet another value'
          );

    // Assert.
    $this->assertEquals($expected, $mock->dereference($data));
  }

  /**
   * Tests skipping the dereferencing.
   */
  public function testDereferenceSkipping() {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $uuid1 = uniqid('uuid1-');
    $uuid2 = uniqid('uuid2-');
    $data = (object) [
      'property to be dereferenced' => $uuid1,
      'another to dereference' => $uuid2,
      'other property' => 'Some other value',
    ];
    // DEREFERENCE_OUTPUT_MINIMAL.
    $method = 1;

    // Assert the dereferencing left the data unchanged, with identifiers.
    $actual = $this->invokeProtectedMethod($mock, 'dereference', $data, $method);
    $this->assertEquals($data, $actual);
  }

  /**
   * Provides data to test emptyPropertyOfSameType.
   */
  public function dataEmptyPropertyOfSameType() {
    return [
      [
        "some string", "",
      ],
      [
      ['some', 'array'], [],
      ],
    ];
  }

  /**
   * Tests the emptyPropertyOfSameType function.
   *
   * @dataProvider dataEmptyPropertyOfSameType
   */
  public function testEmptyPropertyOfSameType($data, $expected) {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'emptyPropertyOfSameType', $data);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests function processReferencesInUpdatedProperty.
   */
  public function testProcessReferencesInUpdatedProperty() {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->setMethods(['queueReferenceForRemoval'])
      ->disableOriginalConstructor()
      ->getMock();

    $property_id = 'someProperty';
    $old = uniqid('some-uuid-old-');
    $new = uniqid('some-uuid-new-');

    $mock->expects($this->once())
      ->method('queueReferenceForRemoval')
      ->willReturn(NULL);

    // Assert.
    $this->invokeProtectedMethod($mock, 'processReferencesInUpdatedProperty', $property_id, $old, $new);
  }

  /**
   * Tests function processReferencesInDeletedProperty.
   */
  public function testProcessReferencesInDeletedProperty() {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->setMethods(['queueReferenceForRemoval'])
      ->disableOriginalConstructor()
      ->getMock();

    $property_id = 'someProperty';
    $old = uniqid('some-uuid-old-');

    $mock->expects($this->once())
      ->method('queueReferenceForRemoval')
      ->willReturn(NULL);

    // Assert.
    $this->invokeProtectedMethod($mock, 'processReferencesInDeletedProperty', $property_id, $old);
  }

  /**
   * Tests function processReferencesInDeletedDataset.
   */
  public function testProcessReferencesInDeletedDataset() {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->setMethods(['getPropertyList', 'processReferencesInDeletedProperty'])
      ->disableOriginalConstructor()
      ->getMock();

    $properties_list = [
      'property to be dereferenced',
      'another to dereference',
    ];
    $uuid1 = uniqid('uuid1-');
    $uuid2 = uniqid('uuid2-');
    $data = (object) [
      'property to be dereferenced' => $uuid1,
      'another to dereference' => $uuid2,
      'other property' => 'Some other value',
    ];

    // Expect.
    $mock->expects($this->once())
      ->method('getPropertyList')
      ->willReturn($properties_list);
    $mock->expects($this->exactly(2))
      ->method('processReferencesInDeletedProperty')
      ->willReturn(NULL);

    // Assert.
    $this->invokeProtectedMethod($mock, 'processReferencesInDeletedDataset', $data);
  }

  /**
   * Tests function processReferencesInUpdatedDataset.
   */
  public function testProcessReferencesInUpdatedDataset() {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->setMethods(
              [
                'getPropertyList',
                'emptyPropertyOfSameType',
                'processReferencesInUpdatedProperty',
              ]
          )
      ->disableOriginalConstructor()
      ->getMock();

    $properties_list = [
      'property only in old',
      'property only in new',
    ];
    $old_uuid = uniqid('uuid-old-');
    $new_uuid = uniqid('uuid-new-');
    $old = (object) [
      'property only in old' => $old_uuid,
    ];
    $new = (object) [
      'property only in new' => $new_uuid,
    ];

    // Expect.
    $mock->expects($this->once())
      ->method('getPropertyList')
      ->willReturn($properties_list);
    $mock->expects($this->once())
      ->method('processReferencesInUpdatedProperty')
      ->willReturn(NULL);
    $mock->expects($this->once())
      ->method('emptyPropertyOfSameType')
      ->with()
      ->willReturn(NULL);

    // Assert.
    $this->invokeProtectedMethod($mock, 'processReferencesInUpdatedDataset', $old, $new);
  }

  /**
   * Provides data to test testSetDereferenceMethod.
   */
  public function dataSetDereferenceMethod() {
    return [
      [
        0, 0,
      ],
      [
        1, 1,
      ],
      [
        2, 2,
      ],
    ];
  }

  /**
   * Tests the setDereferenceMethod function.
   *
   * @dataProvider dataSetDereferenceMethod
   */
  public function testSetDereferenceMethod(int $method, int $expected) {
    // Setup.
    $mock = $this->getMockBuilder(ValueReferencer::class)
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'setDereferenceMethod', $method);
    $this->assertEquals($method, $actual);
    $this->assertEquals($method, $this->accessProtectedProperty($mock, 'dereferenceMethod'));
  }

}
