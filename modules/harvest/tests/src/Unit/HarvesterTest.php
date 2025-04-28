<?php

namespace Drupal\Tests\Unit\harvest;

use Drupal\harvest\ETL\Factory;
use Drupal\harvest\Harvester;
use Drupal\harvest\ResultInterpreter;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Drupal\Tests\harvest\MemStore;

class HarvesterTest extends TestCase {

  public function testPlanValidation(): void {
    $this->expectExceptionMessage("Invalid harvest plan. load {\"missing\":\"type\"}");
    $plan = $this->getPlan("badplan");
    $this->getHarvester($plan, new MemStore(), new MemStore());
  }

  public function basicData(): array {
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

}
