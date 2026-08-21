<?php

namespace Drupal\Tests\dkan_harvest;

use Drupal\dkan_harvest\ETL\Extract\Extract;

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
