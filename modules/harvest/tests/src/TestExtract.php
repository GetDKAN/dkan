<?php

namespace Drupal\Tests\harvest;

use Drupal\harvest\ETL\Extract\Extract;

/**
 * @covers \Drupal\harvest\ETL\Extract\Extract
 * @coversDefaultClass \Drupal\harvest\ETL\Extract\Extract
 *
 * @group dkan
 * @group harvest
 * @group unit
 */
class TestExtract extends Extract {

  protected function getItems(): array {
    return [];
  }

}
