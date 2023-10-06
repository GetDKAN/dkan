<?php

namespace Drupal\Tests\dastastore_mysql_import\Unit\Service;

use Drupal\common\DataResource;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore_mysql_import\Service\MysqlImport;

use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;

/**
 * @covers \Drupal\datastore_mysql_import\Service\MysqlImport
 *
 * @group datastore_mysql_import
 */
class MysqlImportTest extends TestCase {

  protected const HOST = 'http://example.org';
  protected const TABLE_NAME = 'example_table';

  /**
   * Test spec generation.
   */
  public function testGenerateTableSpec() {
    $mysqlImporter = $this->getMysqlImporter();
    $columns = [
      "two\nlines",
      "two\n lines \nwith spaces\n",
      "oneline",
    ];

    $expectedSpec = [
      'two_lines' => [
        'type' => 'text',
        'description' => 'two lines',
      ],
      'two__lines__with_spaces' => [
        'type' => 'text',
        'description' => 'two lines with spaces',
      ],
      'oneline' => [
        'type' => 'text',
        'description' => 'oneline',
      ],
    ];
    $spec = $mysqlImporter->generateTableSpec($columns);

    $this->assertEquals($expectedSpec, $spec);
  }

  protected function getMysqlImporter() {
    $resource = new DataResource(self::HOST . '/text.csv', 'text/csv');
    $delimiter = ',';
    $databaseTableFactory = $this->getDatabaseTableFactoryMock();

    return new class($resource, $databaseTableFactory->getInstance('test')) extends MysqlImport {

      public $sqlStatement = '';

      public function __construct($resource, $dataStorage) {
          $this->resource = $resource;
          $this->dataStorage = $dataStorage;
      }

      public function run(): Result {
        $this->runIt();
        return new Result();
      }

      protected function getDatabaseConnectionCapableOfDataLoad() {
        return new class {
          public function query() {
            return NULL;
          }
        };
      }

      protected function getSqlStatement(string $file_path, string $table_name, array $headers, string $eol, int $header_line_count, string $delimiter): string {
        $this->sqlStatement = parent::getSqlStatement($file_path, $table_name, $headers, $eol, $header_line_count, $delimiter);
        return $this->sqlStatement;
      }

    };

  }

  protected function getDatabaseTableFactoryMock() {
    return (new Chain($this))
      ->add(DatabaseTableFactory::class, 'getInstance', DatabaseTable::class)
      ->add(DatabaseTable::class, 'count', 4)
      ->add(DatabaseTable::class, 'getTableName', self::TABLE_NAME)
      ->getMock();
  }

  protected function getJobstoreFactoryMock() {
    $jobStore = (new Chain($this))
      ->add(JobStore::class, 'retrieve', '')
      ->add(ImportJob::class, 'run', Result::class)
      ->add(ImportJob::class, 'getResult', Result::class)
      ->add(JobStore::class, 'store', '')
      ->getMock();

    return (new Chain($this))
      ->add(JobStoreFactory::class, 'getInstance', $jobStore)
      ->getMock();
  }

}
