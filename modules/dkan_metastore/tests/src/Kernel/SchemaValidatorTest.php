<?php

namespace Drupal\Tests\dkan_metastore\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Covers _dkan_metastore_check_schemas() against the running site's schemas.
 *
 * @group dkan
 * @group dkan_metastore
 * @group kernel
 */
class SchemaValidatorTest extends KernelTestBase {

  protected static $modules = [
    'dkan',
    'dkan_common',
    'dkan_metastore',
    'system',
  ];

  /**
   * Bundled DKAN schemas must all parse cleanly under opis v2.
   *
   * Regression guard so we don't ship a draft-04 (or otherwise broken)
   * schema with future PRs.
   */
  public function testBundledSchemasParseCleanly(): void {
    $module_path = \Drupal::service('extension.list.module')->getPath('dkan_metastore');
    require_once DRUPAL_ROOT . '/' . $module_path . '/dkan_metastore.install';
    $problems = \Drupal::service('dkan.metastore.schema_validator')->checkAllSchemas();
    $this->assertSame([], $problems);
  }

}
