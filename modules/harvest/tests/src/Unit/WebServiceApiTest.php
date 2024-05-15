<?php

namespace Drupal\Tests\harvest\Unit;

use Contracts\Mock\Storage\MemoryFactory;
use Drupal\Component\DependencyInjection\Container;
use Drupal\Tests\common\Traits\ServiceCheckTrait;
use Drupal\harvest\Entity\HarvestPlanRepository;
use Drupal\harvest\Entity\HarvestRunRepository;
use Drupal\harvest\HarvestService;
use Drupal\harvest\Storage\HarvestHashesDatabaseTableFactory;
use Drupal\harvest\WebServiceApi;
use Drupal\metastore\MetastoreService;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @covers \Drupal\harvest\WebServiceApi
 * @coversDefaultClass \Drupal\harvest\WebServiceApi
 *
 * @group dkan
 * @group harvest
 * @group unit
 */
class WebServiceApiTest extends TestCase {
  use ServiceCheckTrait;

  private $request;

  /**
   * Getter.
   */
  public function getContainer() {
    // TODO: Change the autogenerated stub.
    parent::setUp();

    $container = $this->getMockBuilder(ContainerInterface::class)
      ->onlyMethods(['get'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $container->method('get')
      ->with(
        $this->logicalOr(
          $this->equalTo('dkan.harvest.service'),
          $this->equalTo('dkan.harvest.logger_channel'),
          $this->equalTo('request_stack')
        )
      )
      ->will($this->returnCallback([$this, 'containerGet']));

    return $container;
  }

  /**
   *
   */
  public function containerGet($input) {
    switch ($input) {
      case 'dkan.harvest.service':
        return new HarvestService(
          new MemoryFactory(),
          $this->createStub(HarvestHashesDatabaseTableFactory::class),
          $this->getMetastoreMockChain(),
          $this->getHarvestEntityRepositoryMock(),
          $this->createStub(HarvestRunRepository::class),
          $this->createStub(LoggerInterface::class)
        );

      break;
      case 'request_stack':
        $stack = $this->getMockBuilder(RequestStack::class)
          ->disableOriginalConstructor()
          ->onlyMethods(['getCurrentRequest'])
          ->getMock();

        $stack->method("getCurrentRequest")->willReturn($this->request);

        return $stack;

      break;
    }
  }

  /**
   *
   */
  public function testEmptyIndex() {
    $controller = WebServiceApi::create($this->getContainer());
    $response = $controller->index();
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals($response->getContent(), json_encode([]));
  }

  /**
   *
   */
  public function testBadPlan() {
    $this->request = new Request();
    $controller = WebServiceApi::create($this->getContainer());
    $response = $controller->register();
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals($response->getContent(), json_encode(["message" => "Harvest plan must be a php object."]));
  }

  /**
   *
   */
  public function testRegisterAndIndex() {
    $request = $this->getMockBuilder(Request::class)
      ->onlyMethods(['getContent'])
      ->disableOriginalConstructor()
      ->getMock();

    $plan = [
      'identifier' => 'test',
      'extract' => ['type' => "blah", "uri" => "http://blah"],
      'load' => ['type' => 'blah'],
    ];

    $request->method('getContent')->willReturn(json_encode($plan));

    $this->request = $request;

    $controller = WebServiceApi::create($this->getContainer());
    $response = $controller->register();
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals($response->getContent(), json_encode(["identifier" => "test"]));

    $response = $controller->index();
    $this->assertInstanceOf(JsonResponse::class, $response);
  }

  /**
   *
   */
  public function testRun() {
    $options = (new Options())
      ->add("request_stack", RequestStack::class)
      ->add("dkan.harvest.service", HarvestService::class)
      ->index(0);

    $this->checkService('dkan.harvest.service', 'harvest');

    // We are not using the logger but it is good to check.
    $this->checkService('dkan.harvest.logger_channel', 'harvest');

    $container = (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getContent', json_encode((object) ['plan_id' => 'test']))
      ->add(HarvestService::class, "runHarvest", Result::class)
      ->getMock();

    $controller = WebServiceApi::create($container);
    $response = $controller->run();
    $this->assertInstanceOf(JsonResponse::class, $response);
  }

  /**
   * Private.
   */
  private function getMetastoreMockChain() {
    return (new Chain($this))
      ->add(MetastoreService::class, 'publish', '1')
      ->getMock();
  }

  /**
   * Private.
   */
  private function getHarvestEntityRepositoryMock() {
    return (new Chain($this))
      ->add(HarvestPlanRepository::class, 'getAllHarvestPlanIds', [])
      ->add(HarvestPlanRepository::class, 'storePlanJson', 'test')
      ->getMock();
  }

}
