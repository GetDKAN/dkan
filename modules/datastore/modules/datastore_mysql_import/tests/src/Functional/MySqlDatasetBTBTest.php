<?php

namespace Drupal\Tests\datastore_mysql_import\Functional;

use Drupal\Tests\dkan\Functional\DatasetBTBTest;

/**
 * Class DatasetTest
 *
 * This test is identical to Drupal\Tests\dkan\Functional\DatasetTest, except it
 * enables datastore_mysql_import.
 *
 * @group dkan
 * @group datastore_mysql_import
 * @group functional
 */
class MySqlDatasetBTBTest extends DatasetBTBTest {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'datastore',
    'datastore_mysql_import',
    'field',
    'harvest',
    'metastore',
    'menu_ui',
    'node',
  ];

}
