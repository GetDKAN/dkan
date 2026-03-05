<?php

namespace Drupal\Tests\datastore_data_preview\Unit\ExtraField;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 4) . '/datastore_data_preview.module';

/**
 * Tests datastore_data_preview_entity_extra_field_info().
 *
 * @group datastore_data_preview
 */
class ExtraFieldInfoTest extends TestCase {

  /**
   * Verifies the extra field info hook returns correct structure.
   */
  public function testExtraFieldInfoStructure() {
    $result = datastore_data_preview_entity_extra_field_info();

    $this->assertArrayHasKey('node', $result);
    $this->assertArrayHasKey('data', $result['node']);
    $this->assertArrayHasKey('display', $result['node']['data']);
    $this->assertArrayHasKey('datastore_data_preview', $result['node']['data']['display']);

    $field = $result['node']['data']['display']['datastore_data_preview'];
    $this->assertArrayHasKey('label', $field);
    $this->assertArrayHasKey('description', $field);
    $this->assertArrayHasKey('weight', $field);
    $this->assertFalse($field['visible']);
  }

}
