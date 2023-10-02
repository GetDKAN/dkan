<?php

namespace Drupal\Tests\datastore_mysql_import\Functional;

use Drupal\Tests\datastore\Functional\UseLocalWithPrepareLocalizeTest as ParentTest;

/**
 * Test dataset import when using existing localized files.
 *
 * @group datastore_mysql_import
 * @group datastore
 * @group btb
 * @group functional
 *
 * @see \Drupal\Tests\datastore\Kernel\UseLocalWithPrepareLocalizeTest
 */
class UseLocalWithPrepareLocalizeTest extends ParentTest {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'datastore',
    'datastore_mysql_import',
    'metastore',
    'node',
  ];

}
