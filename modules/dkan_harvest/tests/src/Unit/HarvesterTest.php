<?php

namespace Drupal\Tests\Unit\harvest;

use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Tests\dkan_harvest\MemStore;
use Drupal\dkan_harvest\ETL\Factory;
use Drupal\dkan_harvest\Harvester;
use Drupal\dkan_harvest\ResultInterpreter;
use Drupal\dkan_metastore\Exception\MissingObjectException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 * @covers Harvester
 * @coversDefaultClass Harvester
 * @group dkan_harvest
 * @group unit
 */
class HarvesterTest extends TestCase {

  public function testPlanValidation(): void {
    // Opis v2 represents missing required fields as an array.
    $this->expectExceptionMessage("Invalid harvest plan. load {\"missing\":[\"type\"]}");
    $plan = $this->getPlan("badplan");
    $this->getHarvester($plan, new MemStore(), new MemStore());
  }

  public static function basicData(): array {
    return [
      ["file://" . __DIR__ . "/../../files/data4.json"],
      ["https://demo.getdkan.org/data.json"],
    ];
  }

  /**
   * @dataProvider basicData
   */
  public function testBasic(string $uri): void {
    $plan = $this->getPlan("plan");
    $plan->extract->uri = $uri;
    $item_store = new MemStore();
    $hash_store = new MemStore();

    $mock_client = $this->createMock(Client::class);
    $mock_client->method('request')->willReturn(
          new Response(
              200,
              [],
              file_get_contents(__DIR__ . "/../../files/data3.json")
          )
      );

    $harvester = $this->getHarvester($plan, $item_store, $hash_store, $mock_client);

    $result = $harvester->harvest();

    $interpreter = new ResultInterpreter($result);

    $this->assertEquals(10, $interpreter->countCreated());
    $this->assertEquals(0, $interpreter->countUpdated());
    $this->assertEquals(0, $interpreter->countFailed());
    $this->assertEquals(10, $interpreter->countProcessed());
    $this->assertEquals(10, count($item_store->retrieveAll()));

    $result = $harvester->harvest();

    $interpreter = new ResultInterpreter($result);

    $this->assertEquals(0, $interpreter->countCreated());
    $this->assertEquals(0, $interpreter->countUpdated());
    $this->assertEquals(0, $interpreter->countFailed());
    $this->assertEquals(10, $interpreter->countProcessed());
    $this->assertEquals(10, count($item_store->retrieveAll()));

    if (substr_count($uri, "file://") > 0) {
      $plan->extract->uri = str_replace("data4.json", "data5.json", $uri);
      $harvester = $this->getHarvester($plan, $item_store, $hash_store);

      $result = $harvester->harvest();
      $interpreter = new ResultInterpreter($result);

      $this->assertEquals(1, $interpreter->countCreated());
      $this->assertEquals(1, $interpreter->countUpdated());
      $this->assertEquals(2, $interpreter->countFailed());
      $this->assertEquals(10, $interpreter->countProcessed());
      $this->assertEquals(11, count($item_store->retrieveAll()));
    }

    $harvester->revert();

    if (substr_count($uri, "file://") > 0) {
      $expected = 1;
    }
    else {
      $expected = 0;
    }

    $this->assertEquals($expected, count($item_store->retrieveAll()));
  }

  public function testBadUri(): void {
    $uri = "httpp://asdfnde.exo/data.json";

    $plan = $this->getPlan("plan");
    $plan->extract->uri = $uri;

    $harvester = $this->getHarvester($plan, new MemStore());
    $result = $harvester->harvest();
    $this->assertEquals("FAILURE", $result['status']['extract']);
  }

  private function getPlan(string $name) {
    $path = __DIR__ . "/../../files/{$name}.json";
    $content = file_get_contents($path);
    return json_decode($content);
  }

  private function getHarvester($plan, $item_store = NULL, $hash_store = NULL, $client = NULL): Harvester {

    if (!isset($item_store)) {
      $item_store = new MemStore();
    }

    if (!isset($hash_store)) {
      $hash_store = new MemStore();
    }

    $factory = new Factory($plan, $item_store, $hash_store, $client);
    return new Harvester($factory);
  }

  public function testRevertExceptions() {
    $logger = $this->getMockBuilder(LoggerChannel::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['error'])
      ->getMock();
    $logger_factory = $this->getMockBuilder(LoggerChannelFactory::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();
    $logger_factory->expects($this->any())
      ->method('get')
      ->willReturn($logger);

    $container = new Container();
    $container->set('logger.factory', $logger_factory);
    \Drupal::setContainer($container);

    $factory = $this->getMockBuilder(Factory::class)
      ->onlyMethods(['get'])
      ->setConstructorArgs([
        $this->getPlan("plan"),
        new \stdClass(),
        new StubHashStorage(),
      ])
      ->getMock();
    $factory->expects($this->any())
      ->method('get')
      // All calls to $load->removeItem will produce an exception.
      // @see StubLoad::removeItem()
      ->willReturn(new StubLoad());

    $harvester = new Harvester($factory);

    // Even though all 5 calls to $load->removeItem() threw exceptions, we
    // should still see a count of 5 attempts.
    $this->assertEquals(5, $harvester->revert());
  }

}

/**
 * A load object.
 *
 * We can mock this because it's not typed and doesn't have an interface.
 */
class StubLoad {

  public function removeItem() {
    throw new MissingObjectException();
  }

}

/**
 * A hash storage object.
 *
 * We can mock this because it's not typed and doesn't have an interface.
 */
class StubHashStorage {

  public function retrieveAll(): array {
    return [1, 2, 3, 4, 5];
  }

  public function remove() {
    // No-op.
  }

}
