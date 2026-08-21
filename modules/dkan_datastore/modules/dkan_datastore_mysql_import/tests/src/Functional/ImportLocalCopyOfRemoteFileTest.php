<?php

namespace Drupal\Tests\dkan_datastore_mysql_import\Functional;

use Drupal\Tests\dkan_datastore\Functional\ImportLocalCopyOfRemoteFileTest as ParentTest;

/**
 * Test dataset import when using existing localized files.
 *
 * @group dkan_datastore_mysql_import
 * @group btb
 * @group functional
 * @group functional3
 */
class ImportLocalCopyOfRemoteFileTest extends ParentTest {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'dkan_common',
    'dkan_datastore',
    'dkan_datastore_mysql_import',
    'dkan_metastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

}
