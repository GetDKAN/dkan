<?php

namespace Drupal\Tests\harvest;

use Drupal\harvest\ETL\Extract\Extract;

/**
 * Stub ETL extract class for testing.
 */
class TestExtract extends Extract {

  /**
   * {@inheritdoc}
   */
  protected function getItems(): array {
    return [];
  }

}
