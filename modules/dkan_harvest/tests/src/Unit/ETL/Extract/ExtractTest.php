<?php

namespace Drupal\Tests\Unit\harvest\ETL\Extract;

use Drupal\Tests\harvest\TestExtract;
use Drupal\Tests\harvest\TestExtractNoObjects;
use PHPUnit\Framework\TestCase;

/**
 * @group dkan
 * @group harvest
 * @group unit
 *
 * @covers \Drupal\harvest\ETL\Extract\Extract
 * @coversDefaultClass \Drupal\harvest\ETL\Extract\Extract
 */
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
