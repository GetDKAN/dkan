<?php

namespace Drupal\Tests\datastore_mysql_import\Functional\Controller;

use Drupal\Tests\datastore\Functional\Controller\QueryDownloadControllerTest;

/**
 * Test streaming CSV downloads with data dictionaries.
 *
 * This is the same test as
 * \Drupal\Tests\datastore\Functional\Controller\QueryDownloadControllerTest,
 * but using the mysql importer.
 *
 * @group dkan
 * @group datastore_mysql_import
 * @group functional
 * @group btb
 * @group functional1
 *
 * @see \Drupal\Tests\datastore\Functional\Controller\QueryDownloadControllerTest
 */
class MySqlQueryDownloadControllerTest extends QueryDownloadControllerTest {

  protected static $modules = [
    'datastore_mysql_import',
  ];

}
