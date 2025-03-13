<?php

namespace Drupal\Tests\harvest\ETL\Extract;

use PHPUnit\Framework\TestCase;

class ExtractTest extends TestCase {

  public function testNoItems(): void {
    $this->expectExceptionMessage("No Items were extracted.");
    (new TestExtract())->run();
  }

  public function testNoObjects(): void {
    $item = json_encode("Hello World!!");
    $this->expectExceptionMessage("The items extracted are not php objects: {$item}");
    (new TestExtractNoObjects())->run();
  }

}
