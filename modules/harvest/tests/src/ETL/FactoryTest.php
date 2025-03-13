<?php

namespace Drupal\Tests\harvest\ETL;

use Drupal\harvest\ETL\Factory;
use Drupal\Tests\harvest\MemStore;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase {

  public function testExtract(): void {
    $this->expectExceptionMessage("Class NoClass does not exist");
    $this->getFactory()->get('extract');
  }

  public function testTransform(): void {
    $this->expectExceptionMessage("Class NoClass does not exist");
    $this->getFactory()->get('transforms');
  }

  public function testLoad(): void {
    $this->expectExceptionMessage("Class NoClass does not exist");
    $this->getFactory()->get('load');
  }

  public function testPlanCheck(): void {
    $this->expectExceptionMessage("Harvest plan must be a php object.");
    new Factory("hello", new MemStore(), new MemStore());
  }

  private function getFactory(): Factory {
    return new Factory($this->getPlan("badplan2"), new MemStore(), new MemStore());
  }

  private function getPlan(string $name) {
    $path = __DIR__ . "/../json/{$name}.json";
    $content = file_get_contents($path);
    return json_decode($content);
  }

}
