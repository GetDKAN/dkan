<?php

namespace Drupal\Tests\dkan_api\Unit\Controller;

use Drupal\dkan_api\Controller\Docs;
use Drupal\dkan_api\Storage\DrupalNodeDataset;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_common\Service\Factory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class.
 */
class DocsTest extends DkanTestBase {

  /**
   * Public.
   */
  public function testConstruct() {
    // Setup.
    $mock = $this->getMockBuilder(Docs::class)
      ->disableOriginalConstructor()
      ->setMethods(
              [
                'getJsonFromYmlFile',
              ]
          )
      ->getMock();

    $mockContainer = $this->getMockContainer();

    $mockDkanFactory = $this->createMock(Factory::class);
    $mockModuleHandler = $this->createMock(ModuleHandlerInterface::class);
    $mockYmlSerializer = $this->createMock(SerializerInterface::class);
    $mockStorage = $this->getMockBuilder(DrupalNodeDataset::class)
      ->disableOriginalConstructor()
      ->setMethods(
              [
                'setSchema',
              ]
          )
      ->getMock();

    // Expect.
    $mockContainer->expects($this->exactly(4))
      ->method('get')
      ->withConsecutive(
              ['module_handler'],
              ['dkan.factory'],
              ['serialization.yaml'],
              ['dkan_api.storage.drupal_node_dataset']
          )
      ->willReturnOnConsecutiveCalls(
              $mockModuleHandler,
              $mockDkanFactory,
              $mockYmlSerializer,
              $mockStorage
          );
    $mock->expects($this->once())
      ->method('getJsonFromYmlFile')
      ->with()
      ->willReturn([]);

    // Assert.
    $mock->__construct($mockContainer);
    $this->assertSame(
          $mockModuleHandler,
          $this->readAttribute($mock, 'moduleHandler')
      );
    $this->assertSame(
          $mockDkanFactory,
          $this->readAttribute($mock, 'dkanFactory')
      );
    $this->assertSame(
          $mockYmlSerializer,
          $this->readAttribute($mock, 'ymlSerializer')
      );
    $this->assertSame(
          $mockStorage,
          $this->readAttribute($mock, 'storage')
      );

  }

  /**
   * Public.
   */
  public function testGetComplete() {
    // Setup.
    $mock = $this->getMockBuilder(Docs::class)
      ->disableOriginalConstructor()
      ->setMethods(['sendResponse'])
      ->getMock();

    $test = (object) [
      "openapi" => "3.0.1",
      "info" => [
        "title" => "Test API",
      ]
      ];
    $this->writeProtectedProperty($mock, 'spec', $test);

    $mockJsonResponse = $this->createMock(JsonResponse::class);

    // Expect.
    $mock->expects($this->once())
      ->method('sendResponse')
      ->with(json_encode($test))
      ->willReturn($mockJsonResponse);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getComplete');
    $this->assertEquals($mockJsonResponse, $actual);
  }

  /**
   * Public.
   */
  public function testGetDatasetSpecific() {
    // Setup.
    $mock = $this->getMockBuilder(Docs::class)
      ->disableOriginalConstructor()
      ->setMethods(
              [
                'removeSpecOperations',
                'removeSpecPaths',
                'replaceDistributions',
                'sendResponse',
              ]
          )
      ->getMock();

    $someUuid = uniqid('uuid_');

    $this->writeProtectedProperty($mock, 'spec', []);

    $mockJsonResponse = $this->createMock(JsonResponse::class);

    // Expect.
    $mock->expects($this->once())
      ->method('removeSpecOperations')
      ->withAnyParameters()
      ->willReturn([]);
    $mock->expects($this->once())
      ->method('removeSpecPaths')
      ->withAnyParameters()
      ->willReturn(['paths' => ['/api/v1/dataset/{uuid}' => []]]);
    $mock->expects($this->once())
      ->method('replaceDistributions')
      ->withAnyParameters()
      ->willReturn([]);
    $mock->expects($this->once())
      ->method('sendResponse')
      ->with(json_encode([]))
      ->willReturn($mockJsonResponse);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getDatasetSpecific', $someUuid);
    $this->assertEquals($mockJsonResponse, $actual);
  }

  /**
   * Public.
   */
  public function testSendResponse() {
    // Setup.
    $mock = $this->getMockBuilder(Docs::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $mockDkanFactory = $this->getMockBuilder(Factory::class)
      ->setMethods(['newJsonResponse'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'dkanFactory', $mockDkanFactory);

    $mockJsonResponse = $this->createMock(JsonResponse::class);

    $data = '{}';

    // Expect.
    $mockDkanFactory->expects($this->once())
      ->method('newJsonResponse')
      ->with(
              $data,
              200,
              [
                'Content-type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
              ]
          )
      ->willReturn($mockJsonResponse);

    // Asset.
    $actual = $mock->sendResponse($data);
    $this->assertSame($mockJsonResponse, $actual);
  }

  /**
   * Provides data to test removeSpecOperations.
   */
  public function dataTestRemoveSpecOperations() {
    return [
      'Removing `foo` resulting empty path' => [
      [
    'paths' => [
      'pathWithOnlyFoo' => [
        'foo' => 'one',
      ],
      'pathWithBothFooAndBar' => [
        'bar' => 'two',
        'foo' => 'three',
      ],
    ]
      ],
      ['foo'],
      [
      'paths' => [
        'pathWithBothFooAndBar' => [
          'bar' => 'two',
        ],
      ]
      ]
      ],
    ];
  }

  /**
   * @param $original
   * @param $ops_to_remove
   * @param $expected
   *
   * @dataProvider dataTestRemoveSpecOperations
   */
  public function testRemoveSpecOperations($original, $ops_to_remove, $expected) {
    // Setup.
    $mock = $this->getMockBuilder(Docs::class)
      ->disableOriginalConstructor()
      ->getMock();

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'removeSpecOperations', $original, $ops_to_remove);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Public.
   */
  public function testRemoveSpecPaths() {
    // Setup.
    $mock = $this->getMockBuilder(Docs::class)
      ->disableOriginalConstructor()
      ->getMock();
    $test = [
    'paths' => [
      'keep' => [],
      'remove' => [],
    ]
  ];
    $expected = [
    'paths' => [
      'keep' => [],
    ]
  ];

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'removeSpecPaths', $test, ['remove']);
    $this->assertEquals($expected, $actual);
  }

}
