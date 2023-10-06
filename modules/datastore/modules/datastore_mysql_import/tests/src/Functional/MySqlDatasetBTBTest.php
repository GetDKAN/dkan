<?php

namespace Drupal\Tests\datastore_mysql_import\Functional;

use Drupal\Tests\dkan\Functional\DatasetBTBTest;

/**
 * Class DatasetTest
 *
 * This test is a subclass of \Drupal\Tests\dkan\Functional\DatasetBTBTest, so
 * we get the same tests, except with datastore_mysql_import enabled.
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
    'node',
  ];

}
