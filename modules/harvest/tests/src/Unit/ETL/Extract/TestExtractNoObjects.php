<?php

namespace Drupal\Tests\Unit\harvest\ETL\Extract;

use Drupal\harvest\ETL\Extract\Extract;

/**
 * @covers \Drupal\harvest\ETL\Extract\Extract
 * @coversDefaultClass \Drupal\harvest\ETL\Extract\Extract
 *
 * @group dkan
 * @group harvest
 * @group unit
 */
class TestExtractNoObjects extends Extract {

  protected function getItems(): array {
    return ["Hello World!!"];
  }

}
