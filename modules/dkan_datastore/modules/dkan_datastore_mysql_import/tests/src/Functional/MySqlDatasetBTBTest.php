<?php

namespace Drupal\Tests\dkan_datastore_mysql_import\Functional;

use Drupal\Tests\dkan\Functional\DatasetBTBTest;

/**
 * Dataset tests with the datastore_mysql_import module enabled.
 *
 * This test is a subclass of \Drupal\Tests\dkan\Functional\DatasetBTBTest, so
 * we get the same tests, except with datastore_mysql_import enabled.
 *
 * @group dkan
 * @group datastore_mysql_import
 * @group functional
 * @group functional4
 */
class MySqlDatasetBTBTest extends DatasetBTBTest {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'dkan_datastore',
    'dkan_datastore_mysql_import',
    'dblog',
    'field',
    'dkan_harvest',
    'dkan_metastore',
    'node',
  ];

}
