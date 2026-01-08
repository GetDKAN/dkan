<?php

namespace Drupal\Tests\dkan_datastore_mysql_import\Functional\Controller;

use Drupal\Tests\dkan_datastore\Functional\Controller\QueryDownloadControllerTest;

/**
 * Test streaming CSV downloads with data dictionaries.
 *
 * This is the same test as
 * \Drupal\Tests\dkan_datastore\Functional\Controller\QueryDownloadControllerTest,
 * but using the mysql importer.
 *
 * @group dkan
 * @group datastore_mysql_import
 * @group functional
 * @group btb
 * @group functional1
 *
 * @see \Drupal\Tests\dkan_datastore\Functional\Controller\QueryDownloadControllerTest
 */
class MySqlQueryDownloadControllerTest extends QueryDownloadControllerTest {

  protected static $modules = [
    'dkan_datastore_mysql_import',
  ];

}
