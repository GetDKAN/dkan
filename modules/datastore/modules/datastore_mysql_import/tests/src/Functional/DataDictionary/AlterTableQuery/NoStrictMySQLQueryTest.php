<?php

namespace Drupal\Tests\datastore_mysql_import\Functional\DataDictionary\AlterTableQuery;

use Drupal\Tests\BrowserTestBase;

class NoStrictMySQLQueryTest extends BrowserTestBase {

  protected static $modules = [
    'datastore_mysql_import',
    'node',
  ];

  protected $defaultTheme = 'stark';

  public function testPostImport() {
    // Create a data dictionary for wide_table.csv. (Columns are numeric.)
    // Import wide_table.csv
    // Run post-import process on dataset.
    // Verify that the process was successful.
    // Verify that our DD schema was applied.
  }

}
