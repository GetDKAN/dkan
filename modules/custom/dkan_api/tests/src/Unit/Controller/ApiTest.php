<?php

namespace Drupal\Tests\dkan_api\Unit\Controller;

use Drupal\dkan_api\Controller\Api;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_common\Service\Factory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sae\Sae;

/**
 * Tests Drupal\dkan_api\Controller\Api.
 *
 * @coversDefaultClass \Drupal\dkan_api\Controller\Api
 * @group dkan_api
 * @author Yaasir Ketwaroo <yaasir.ketwaroo@semanticbits.com>
 */
class ApiTest extends DkanTestBase {

  /**
   * Tests __construct().
   */
  public function testConstruct() {

    $mock = $this->getMockBuilder(Api::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $mockContainer = $this->getMockContainer();

    $mockDkanFactory = $this->createMock(Factory::class);

    // Expect.
    $mockContainer->expects($this->once())
      ->method('get')
      ->with('dkan.factory')
      ->willReturn($mockDkanFactory);

    // Assert.
    $mock->__construct($mockContainer);

    $this->assertSame(
            $mockContainer,
            $this->readAttribute($mock, 'container')
    );

    $this->assertSame(
            $mockDkanFactory,
            $this->readAttribute($mock, 'dkanFactory')
    );

  }

  /**
   * Tests GetAll.
   */
  public function testGetAll() {

    $flattened = [
      json_encode('foo'),
      json_encode([]),
      json_encode(['foo' => 'bar']),
    ];

    // This is a bit iffy but should be close enough.
    $unflattened = [
      'foo',
        [],
        // Peculiarities of json encode.
      (object) ['foo' => 'bar'],
    ];

    $mock = $this->getMockBuilder(Api::class)
      ->setMethods([
        'getEngine',
      ])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $mockSae = $this->getMockBuilder(Sae::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockDkanFactory = $this->getMockBuilder(Factory::class)
      ->setMethods(['newJsonResponse'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'dkanFactory', $mockDkanFactory);

    $mockJsonResponse = $this->createMock(JsonResponse::class);

    // Expect.
    $mock->expects($this->once())
      ->method('getEngine')
      ->willReturn($mockSae);

    $mockSae->expects($this->once())
      ->method('get')
      ->willReturn($flattened);

    $mockDkanFactory->expects($this->once())
      ->method('newJsonResponse')
      ->with(
                    $unflattened,
          200,
          ["Access-Control-Allow-Origin" => "*"]
                    )
      ->willReturn($mockJsonResponse);

    // Assert.
    $actual = $mock->getAll();

    $this->assertSame($mockJsonResponse, $actual);

  }

  /**
   * Tests get().
   */
  public function testGet() {

    // Setup.
    $uuid = 'foobar123';

    $data = (object) ['foo' => 'bar'];
    $dataEncoded = json_encode($data);

    $mock = $this->getMockBuilder(Api::class)
      ->setMethods([
        'getEngine',
      ])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $mockSae = $this->getMockBuilder(Sae::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockDkanFactory = $this->getMockBuilder(Factory::class)
      ->setMethods(['newJsonResponse'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'dkanFactory', $mockDkanFactory);

    $mockJsonResponse = $this->createMock(JsonResponse::class);
    // Expect.
    $mock->expects($this->once())
      ->method('getEngine')
      ->willReturn($mockSae);

    $mockSae->expects($this->once())
      ->method('get')
      ->with($uuid)
      ->willReturn($dataEncoded);

    $mockDkanFactory->expects($this->once())
      ->method('newJsonResponse')
      ->with(
                    $data,
          200,
          ["Access-Control-Allow-Origin" => "*"]
                    )
      ->willReturn($mockJsonResponse);

    // Assert.
    $actual = $mock->get($uuid);

    $this->assertSame($mockJsonResponse, $actual);
  }

  /**
   * Tests get() on exception conditions.
   */
  public function testGetException() {

    // Setup.
    $uuid = 'foobar123';

    $exceptionMessage = __METHOD__ . " exception message";
    // Can't mock this. has to be an actual exception for the throw to work.
    $expectedException = new \Exception($exceptionMessage);

    $mock = $this->getMockBuilder(Api::class)
      ->setMethods([
        'getEngine',
      ])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $mockSae = $this->getMockBuilder(Sae::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockDkanFactory = $this->getMockBuilder(Factory::class)
      ->setMethods(['newJsonResponse'])
      ->disableOriginalConstructor()
      ->getMock();

    $this->writeProtectedProperty($mock, 'dkanFactory', $mockDkanFactory);

    $mockJsonResponse = $this->createMock(JsonResponse::class);

    // Expect.
    $mock->expects($this->once())
      ->method('getEngine')
      ->willReturn($mockSae);

    $mockSae->expects($this->once())
      ->method('get')
      ->with($uuid)
      ->willThrowException($expectedException);

    $mockDkanFactory->expects($this->once())
      ->method('newJsonResponse')
      ->with(
                    (object) ["message" => $exceptionMessage],
                    404
                    )
      ->willReturn($mockJsonResponse);

    // Assert.
    $actual = $mock->get($uuid);

    $this->assertSame($mockJsonResponse, $actual);
  }

}
