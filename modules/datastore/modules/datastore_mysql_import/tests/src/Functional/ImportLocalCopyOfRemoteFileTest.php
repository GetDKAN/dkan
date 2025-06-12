<?php

namespace Drupal\Tests\datastore_mysql_import\Functional;

use Drupal\Tests\datastore\Functional\ImportLocalCopyOfRemoteFileTest as ParentTest;

/**
 * Test dataset import when using existing localized files.
 *
 * @group datastore_mysql_import
 * @group btb
 * @group functional
 * @group functional3
 */
class ImportLocalCopyOfRemoteFileTest extends ParentTest {

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

  protected $defaultTheme = 'stark';

}
