<?php

namespace Drupal\Tests\dkan_api\Unit\Controller;

use Drupal\dkan_api\Controller\Docs;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_common\Service\Factory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class DocsTest extends DkanTestBase {

  public function testConstruct() {
    // Setup.
    $mock = $this->getMockBuilder(Docs::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'getJsonFromYmlFile',
      ])
      ->getMock();

    $mockContainer = $this->getMockContainer();

    $mockDkanFactory = $this->createMock(Factory::class);
    $mockModuleHandler = $this->createMock(ModuleHandlerInterface::class);
    $mockYmlSerializer = $this->createMock(SerializerInterface::class);

    // Expect.
    $mockContainer->expects($this->exactly(3))
      ->method('get')
      ->withConsecutive(
        ['module_handler'],
        ['dkan.factory'],
        ['serialization.yaml']
      )
      ->willReturnOnConsecutiveCalls(
        $mockModuleHandler,
        $mockDkanFactory,
        $mockYmlSerializer
      );
    $mock->expects($this->once())
      ->method('getJsonFromYmlFile')
      ->with()
      ->willReturn([]);

    // Assert.
    $mock->__construct($mockContainer);
    $this->assertSame(
      $mockContainer,
      $this->readAttribute($mock, 'container')
    );
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
  }

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

  public function testGetUnauthenticated() {
    // Setup.
    $mock = $this->getMockBuilder(Docs::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'filterSpecOperations',
        'sendResponse',
      ])
      ->getMock();

    $test = [];
    $filtered_array = [];
    $this->writeProtectedProperty($mock, 'spec', $test);

    $mockJsonResponse = $this->createMock(JsonResponse::class);

    // Expect.
    $mock->expects($this->once())
      ->method('filterSpecOperations')
      ->with($test, ['get'])
      ->willReturn($filtered_array);
    $mock->expects($this->once())
      ->method('sendResponse')
      ->with(json_encode($filtered_array))
      ->willReturn($mockJsonResponse);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getUnauthenticated');
    $this->assertEquals($mockJsonResponse, $actual);
  }

  /**
   * Provides data to test filterSpecOperations.
   */
  public function dataTestFilterSpecOperations() {
    return [
      'Filtering by bar removes foo and resulting empty path' => [
        [ 'paths' => [
            'pathWithOnlyFoo' => [
              'foo' => 'one',
            ],
            'pathWithBothFooAndBar' => [
              'bar' => 'two',
              'foo' => 'three',
            ],
          ]
        ],
        ['bar'],
        ['paths' => [
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
   * @param $operations_allowed
   * @param $expected
   *
   * @dataProvider dataTestFilterSpecOperations
   */
  public function testFilterSpecOperations($original, $operations_allowed, $expected) {
    // Setup.
    $mock = $this->getMockBuilder(Docs::class)
      ->disableOriginalConstructor()
      ->getMock();

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'filterSpecOperations', $original, $operations_allowed);
    $this->assertEquals($expected, $actual);
  }

}
