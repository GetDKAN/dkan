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
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\datastore\Service\Import as Service;
use Drupal\datastore_mysql_import\Service\MySqlImportJob;
use Drupal\datastore_mysql_import\Storage\MySqlDatabaseTable;
use Drupal\datastore_mysql_import\Storage\MySqlDatabaseTableFactory;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @covers \Drupal\datastore_mysql_import\Service\MySqlImportJob
 * @coversDefaultClass  \Drupal\datastore_mysql_import\Service\MySqlImportJob
 *
 * @group dkan
 * @group datastore
 * @group datastore_mysql_import
 */
class MySqlImportJobTest extends TestCase {

  protected const HOST = 'http://example.org';

  protected const TABLE_NAME = 'example_table';

  public function providerGenerateTableSpec() {
    return [
      [
        [
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
        ],
        [
          "two\nlines",
          "two\n lines \nwith spaces\n",
          'oneline',
        ],
      ],
      'duplicate_columns' => [
        [
          'duplicate' => [
            'type' => 'text',
            'description' => 'duplicate',
          ],
          'duplicate_2' => [
            'type' => 'text',
            'description' => 'duplicate',
          ],
        ],
        [
          'duplicate',
          'duplicate',
        ],
      ],
    ];
  }

  /**
   * @covers ::generateTableSpec
   * @dataProvider providerGenerateTableSpec
   */
  public function testGenerateTableSpec($expectedSpec, $columns) {
    $this->assertEquals(
      $expectedSpec,
      $this->getMysqlImporter()->generateTableSpec($columns)
    );
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

    $service = new Service($resource, $jobStoreFactory, $databaseTableFactory);
    $service->setImporterClass(MySqlImportJob::class);
    $service->import();

    $result = $service->getResult();
    $this->assertTrue($result instanceof Result);
  }

  /**
   * Test MysqlImport importer with a CSV file with new lines in it's headers.
   */
  public function testMysqlImporterWithCSVFileWithNewLinesInHeaders() {
    $this->markTestIncomplete('datastore resource issue.');
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

  public function provideGetEol() {
    return [
      [NULL, 'no_line_ending'],
      ['\r\n', "ending\r\n"],
      ['\r', "ending\r"],
      ['\n', "ending\n"],
    ];
  }

  /**
   * @covers ::getEol
   * @dataProvider provideGetEol
   */
  public function testGetEol($expected, $string) {
    $this->markTestIncomplete('move this to DatastoreResourceTest');
    $job = $this->getMockBuilder(MySqlImportJob::class)
      ->disableOriginalConstructor()
      ->getMock();

    $ref_get_eol = new \ReflectionMethod($job, 'getEol');
    $ref_get_eol->setAccessible(TRUE);

    $this->assertSame($expected, $ref_get_eol->invokeArgs($job, [$string]));
  }

  protected function getMysqlImporter() {
    $resource = new DataResource(self::HOST . '/text.csv', 'text/csv');
    $delimiter = ',';
    $databaseTableFactory = $this->getDatabaseTableFactoryMock();

    return new class($resource, $databaseTableFactory->getInstance('test')) extends MySqlImportJob {

      public $sqlStatement = '';

      public function __construct($resource, $dataStorage) {
        $this->resource = $resource;
        $this->dataStorage = $dataStorage;
      }

      public function run(): Result {
        $this->runIt();
        return new Result();
      }

      protected function getDatabaseConnectionCapableOfDataLoad($key = 'extra') {
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
      ->add(MySqlDatabaseTableFactory::class, 'getInstance', MySqlDatabaseTable::class)
      ->add(MySqlDatabaseTable::class, 'count', 4)
      ->add(MySqlDatabaseTable::class, 'getTableName', self::TABLE_NAME)
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
