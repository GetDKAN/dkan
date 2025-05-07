<?php

namespace Drupal\Tests\harvest;

use Drupal\harvest\ETL\Extract\Extract;

/**
 * Stub ETL extract class for testing.
 */
class TestExtractNoObjects extends Extract {

  /**
   * {@inheritdoc}
   */
  protected function getItems(): array {
    return ["Hello World!!"];
  }

}
