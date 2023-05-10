<?php

namespace Drupal\Tests\dastastore_mysql_import\Unit\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\File\FileSystem;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

use Drupal\common\DataResource;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\datastore\Service\ImportService;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore_mysql_import\Service\MysqlImport;

use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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

  /**
   * Test MysqlImport importer.
   */
  public function testMysqlImporter() {
    $file_path = 'file://' . __DIR__ . '/../../../../../../tests/data/countries.csv';
    $options = (new Options())
      ->add('database', Connection::class)
      ->add('event_dispatcher', ContainerAwareEventDispatcher::class)
      ->add('file_system', FileSystem::class)
      ->add('request_stack', RequestStack::class)
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->index(0);
    $container = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(FileSystem::class, 'realpath', $file_path)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getHost', self::HOST)
      ->getMock();
    \Drupal::setContainer($container);

    $resource = new DataResource(self::HOST . '/text.csv', 'text/csv');
    $databaseTableFactory = $this->getDatabaseTableFactoryMock();
    $jobStoreFactory = $this->getJobstoreFactoryMock();

    $service = new ImportService($resource, $jobStoreFactory, $databaseTableFactory);
    $service->setImporterClass(MysqlImport::class);
    $service->import();

    $result = $service->getResult();
    $this->assertTrue($result instanceof Result);
  }

  /**
   * Test MysqlImport importer with a CSV file with new lines in it's headers.
   */
  public function testMysqlImporterWithCSVFileWithNewLinesInHeaders() {
    $file_path = 'file://' . __DIR__ . '/../../../../../../tests/data/newlines_in_headers.csv';
    $options = (new Options())
      ->add('file_system', FileSystem::class)
      ->index(0);
    $container = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(FileSystem::class, 'realpath', $file_path)
      ->getMock();
    \Drupal::setContainer($container);

    $mysqlImporter = $this->getMysqlImporter();
    $mysqlImporter->run();

    $this->assertEquals($mysqlImporter->sqlStatement, implode(' ', [
      'LOAD DATA LOCAL INFILE \'' . $file_path . '\'',
      'INTO TABLE ' . self::TABLE_NAME,
      'FIELDS TERMINATED BY \',\'',
      'OPTIONALLY ENCLOSED BY \'"\'',
      'ESCAPED BY \'\'',
      'LINES TERMINATED BY \'\n\'',
      'IGNORE 2 LINES',
      '(a_b,c)',
      'SET record_number = NULL;',
    ]));
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

      protected function getSqlStatement(string $file_path, string $tablename, array $headers, string $eol, int $header_line_count, string $delimiter): string {
        $this->sqlStatement = parent::getSqlStatement($file_path, $tablename, $headers, $eol, $header_line_count, $delimiter);
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
