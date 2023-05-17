<?php

namespace Drupal\Tests\datastore_mysql_import\Kernel\Service;

use Drupal\common\DataResource;
use Drupal\datastore_mysql_import\Factory\MysqlImportFactory;
use Drupal\datastore_mysql_import\Service\MysqlImport;
use Drupal\datastore_mysql_import\Storage\MySqlDatabaseTable;
use Drupal\KernelTests\KernelTestBase;
use Procrastinator\Result;

/**
 * @covers \Drupal\datastore_mysql_import\Service\MysqlImport
 * @coversDefaultClass \Drupal\datastore_mysql_import\Service\MysqlImport
 *
 * @group datastore_mysql_import
 * @group kernel
 */
class MysqlImportTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'datastore',
    'datastore_mysql_import',
    'metastore',
  ];

  public function testTableDuplicateException() {
    $identifier = 'my_id';
    $file_path = dirname(__FILE__, 4) . '/data/columnspaces.csv';
    $data_resource = new DataResource($file_path, 'text/csv');

    $import_factory = $this->container->get('dkan.datastore.service.factory.import');
    $this->assertInstanceOf(MysqlImportFactory::class, $import_factory);

    /** @var \Drupal\datastore\Plugin\QueueWorker\ImportJob $import_job */
    $import_job = $import_factory->getInstance(
      $identifier,
      ['resource' => $data_resource]
    )->getImporter();
    $this->assertInstanceOf(MySqlDatabaseTable::class, $import_job->getStorage());

    // Store the table.
    $result = $import_job->run();
    $this->assertEquals(Result::DONE, $result->getStatus(), $result->getError());

    // Do it again...
    $import_job = $import_factory->getInstance(
      $identifier,
      ['resource' => $data_resource]
    )->getImporter();
    // The import job aggressively keeps track of what's already done, so we
    // have to reset that.
    $import_job->getResult()->setStatus(Result::IN_PROGRESS);
    // Try to import again.
    $result = $import_job->run();
    $this->assertEquals(Result::ERROR, $result->getStatus(), $result->getError());
    $this->assertStringContainsString('already exists', $result->getError());
  }

  /**
   * Test MysqlImport importer.
   */
  public function testMysqlImporter() {
    $file_path = dirname(__FILE__, 7) . '/tests/data/countries.csv';

    $import_factory = $this->container->get('dkan.datastore.service.factory.import');
    $this->assertInstanceOf(MysqlImportFactory::class, $import_factory);

    $service = $import_factory->getInstance('my_identifier', [
      'resource' => new DataResource($file_path, 'text/csv'),
    ]);

    $service->import();
    $result = $service->getResult();
    $this->assertTrue($result instanceof Result);
    $this->assertEquals(Result::DONE, $result->getStatus(), $result->getError());
  }

  /**
   * Test MysqlImport importer with a CSV file with new lines in its headers.
   */
  public function testMysqlImporterWithCSVFileWithNewLinesInHeaders() {
    $file_path = dirname(__FILE__, 7) . '/tests/data/newlines_in_headers.csv';

    $import_factory = $this->container->get('dkan.datastore.service.factory.import');
    $this->assertInstanceOf(MysqlImportFactory::class, $import_factory);

    $service = $import_factory->getInstance('my_identifier', [
      'resource' => new DataResource($file_path, 'text/csv'),
    ]);
    // Add our stubbed MysqlImport class.
    $service->setImporterClass(MockGetSqlStatementMysqlImport::class);
    $this->assertInstanceOf(
      MockGetSqlStatementMysqlImport::class,
      $importer = $service->getImporter()
    );

    $importer->run();
    // Get the SQL statement from the stubbed import class.
    $this->assertEquals(implode(' ', [
      'LOAD DATA LOCAL INFILE \'' . $file_path . '\'',
      'INTO TABLE {' . $service->getStorage()->getTableName() . '}',
      'FIELDS TERMINATED BY \',\'',
      'OPTIONALLY ENCLOSED BY \'"\'',
      'ESCAPED BY \'\'',
      'LINES TERMINATED BY \'\n\'',
      'IGNORE 2 LINES',
      '(a_b,c)',
      'SET record_number = NULL;',
    ]), $importer->sqlStatement);
  }

}

class MockGetSqlStatementMysqlImport extends MysqlImport {

  public string $sqlStatement = '';

  protected function getSqlStatement(string $file_path, string $table_name, array $headers, string $eol, int $header_line_count, string $delimiter): string {
    $this->sqlStatement = parent::getSqlStatement($file_path, $table_name, $headers, $eol, $header_line_count, $delimiter);
    return $this->sqlStatement;
  }

}
