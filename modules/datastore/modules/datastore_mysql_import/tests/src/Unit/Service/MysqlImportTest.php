<?php

namespace Drupal\Tests\datastore_mysql_import\Unit\Service;

use Drupal\common\DataResource;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore_mysql_import\Service\MysqlImport;

use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use MockChain\Chain;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 *
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
      'oneline',
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

  public function provideGetEol() {
    return [
      [NULL, "\n", 'no_line_ending'],
      ['\r\n', "\r\n", "ending\r\n"],
      ['\r', "\r", "ending\r"],
      ['\n', "\n", "ending\n"],
    ];
  }

  /**
   * @covers ::getEol
   * @dataProvider provideGetEol
   */
  public function testGetEol($expected_token, $expected_eol, $line) {
    $importer = $this->getMockBuilder(MysqlImport::class)
      ->disableOriginalConstructor()
      ->getMock();

    $ref_get_eol = new \ReflectionMethod(MysqlImport::class, 'getEol');
    $ref_eol_table = new \ReflectionClassConstant(MysqlImport::class, 'EOL_TABLE');

    $this->assertSame($expected_token, $token = $ref_get_eol->invokeArgs($importer, [$line]));
    if ($line === 'no_line_ending') {
      $this->assertNull($token);
    }
    else {
      $this->assertSame($expected_eol, $ref_eol_table->getValue()[$token]);
    }
  }

  public function testGetColsFromFileBadFile() {
    // Create an unreadable file in memory.
    $root = vfsStream::setup('root');
    vfsStream::newFile('file.csv', 0000)
      ->at($root)
      ->setContent('yes,no,maybe');

    $importer = $this->getMockBuilder(MysqlImport::class)
      ->disableOriginalConstructor()
      ->getMock();
    $ref_get_cols_from_file = new \ReflectionMethod(MysqlImport::class, 'getColsFromFile');

    $this->expectException(FileException::class);
    $this->expectExceptionMessage('Failed to open resource file "vfs://root/file.csv"');
    $ref_get_cols_from_file->invokeArgs($importer, [
      vfsStream::url('root/file.csv'),
      ',',
    ]);
  }

  public function provideGetColsFromFile() {
    return [
      [['foo', 'bar'], 'foo,bar', 'foo,bar'],
      [['foo', 'bar'], "foo,bar\n", "foo,bar\n"],
    ];
  }

  /**
   * @covers ::getColsFromFile
   * @dataProvider provideGetColsFromFile
   */
  public function testGetColsFromFile($expected_columns, $expected_column_lines, $file_contents) {
    // Create a file in memory.
    $root = vfsStream::setup('root');
    vfsStream::newFile('file.csv')
      ->at($root)
      ->setContent($file_contents);

    $importer = $this->getMockBuilder(MysqlImport::class)
      ->disableOriginalConstructor()
      ->getMock();
    $ref_get_cols_from_file = new \ReflectionMethod(MysqlImport::class, 'getColsFromFile');

    [$columns, $column_lines] = $ref_get_cols_from_file->invokeArgs($importer, [
      vfsStream::url('root/file.csv'),
      ',',
    ]);
    $this->assertEquals($expected_columns, $columns);
    $this->assertEquals($expected_column_lines, $column_lines);
  }

}
