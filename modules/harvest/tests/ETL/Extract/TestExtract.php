<?php

namespace modules\harvest\tests\ETL\Extract;

use Drupal\harvest\ETL\Extract\Extract;

class TestExtract extends Extract {

  protected function getItems(): array {
    return [];
  }

}
