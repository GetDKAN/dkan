<?php

namespace Drupal\Tests\datastore\Unit\Plugin\QueueWorker;

use Contracts\ParserInterface;
use CsvParser\Parser\Csv;
use Contracts\Mock\Storage\Memory;
use Drupal\datastore\DatastoreResource;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\common\Storage\DatabaseTableInterface;
use Procrastinator\Result;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Importer class.
 *
 * @covers \Drupal\datastore\Plugin\QueueWorker\ImportJob
 * @coversDefaultClass \Drupal\datastore\Plugin\QueueWorker\ImportJob
 *
 * @group datastore
 * @group dkan-core
 */
class ImportJobTest extends TestCase {

  private $database;

  /**
   * This method is called before each test.
   */
  protected function setUp(): void {
    $this->database = new TestMemStorage();
    $this->assertTrue($this->database instanceof DatabaseTableInterface);
  }

  protected function tearDown(): void {
    parent::tearDown();
    $this->database = NULL;
  }

  /**
   *
   */
  private function getDatastore(DatastoreResource $resource): ImportJob {
    $storage = new Memory();
    $config = [
      'resource' => $resource,
      'storage' => $this->database,
      'parser' => Csv::getParser(),
    ];
    return ImportJob::get('1', $storage, $config);
  }

  /**
   *
   */
  public function testBasics() {
    $resource = new DatastoreResource(1, __DIR__ . '/../../../../data/countries.csv', 'text/csv');
    $this->assertEquals(1, $resource->getID());

    $datastore = $this->getDatastore($resource);

    $this->assertTrue($datastore->getParser() instanceof ParserInterface);
    $this->assertEquals(Result::STOPPED, $datastore->getResult()->getStatus());

    $datastore->run();
    $this->assertNotEquals(Result::ERROR, $datastore->getResult()->getStatus());

    $schema = $datastore->getStorage()->getSchema();
    $this->assertTrue(is_array($schema['fields'] ?? FALSE));

    $status = $datastore->getResult()->getStatus();
    $this->assertEquals(Result::DONE, $status);

    $this->assertEquals(4, $datastore->getStorage()->count());

    $datastore->run();
    $status = $datastore->getResult()->getStatus();
    $this->assertEquals(Result::DONE, $status);

    $datastore->drop();

    $status = $datastore->getResult()->getStatus();
    $this->assertEquals(Result::STOPPED, $status);
  }

  /**
   *
   */
  public function testFileNotFound() {
    $resource = new DatastoreResource(1, __DIR__ . '/../../../../data/non-existent.csv', 'text/csv');
    $datastore = $this->getDatastore($resource);
    $datastore->run();

    $this->assertEquals(Result::ERROR, $datastore->getResult()->getStatus());
  }

  /**
   *
   */
  public function testNonTextFile() {
    $resource = new DatastoreResource(1, __DIR__ . '/../../../../data/non-text.csv', 'text/csv');
    $datastore = $this->getDatastore($resource);
    $datastore->run();

    $this->assertEquals(Result::ERROR, $datastore->getResult()->getStatus());
  }

  /**
   *
   */
  public function testDuplicateHeaders() {
    $resource = new DatastoreResource(1, __DIR__ . '/../../../../data/duplicate-headers.csv', 'text/csv');
    $datastore = $this->getDatastore($resource);
    $datastore->run();

    $this->assertEquals(Result::ERROR, $datastore->getResult()->getStatus());
    $this->assertEquals('Duplicate headers error: bar, baz', $datastore->getResult()
      ->getError());
  }

  /**
   *
   */
  public function testLongColumnName() {
    $resource = new DatastoreResource(1, __DIR__ . '/../../../../data/longcolumn.csv', 'text/csv');
    $datastore = $this->getDatastore($resource);
    $truncatedLongFieldName = 'extra_long_column_name_with_tons_of_characters_that_will_ne_e872';

    $datastore->run();
    $schema = $datastore->getStorage()->getSchema();
    $fields = array_keys($schema['fields']);

    $this->assertEquals($truncatedLongFieldName, $fields[2]);
    $this->assertEquals(64, strlen($fields[2]));

    $this->assertNotEquals($fields[3], $truncatedLongFieldName);
    $this->assertEquals(64, strlen($fields[3]));
  }

  /**
   *
   */
  public function testColumnNameSpaces() {
    $resource = new DatastoreResource(1, __DIR__ . '/../../../../data/columnspaces.csv', 'text/csv');
    $datastore = $this->getDatastore($resource);
    $noMoreSpaces = 'column_name_with_spaces_in_it';

    $datastore->run();
    $schema = $datastore->getStorage()->getSchema();
    $fields = array_keys($schema['fields']);
    $this->assertEquals($noMoreSpaces, $fields[2]);
  }

  /**
   * Test JSON/hydrate round-trip.
   *
   * This pattern is deprecated.
   *
   * @group legacy
   */
  public function testSerialization() {
    $timeLimit = 40;
    $resource = new DatastoreResource(1, __DIR__ . '/../../../../data/countries.csv', 'text/csv');
    $this->assertEquals(1, $resource->getID());

    $datastore = $this->getDatastore($resource);
    $datastore->setTimeLimit($timeLimit);
    $datastore->run();
    $json = json_encode($datastore);

    $datastore2 = ImportJob::hydrate($json);

    $this->assertEquals(Result::DONE, $datastore2->getResult()->getStatus());
    $this->assertEquals($timeLimit, $datastore2->getTimeLimit());
  }

  /**
   * Test whether a potential multi-batch import works correctly.
   */
  public function testLargeImport() {
    $resource = new DatastoreResource(1, __DIR__ . '/../../../../data/Bike_Lane.csv', 'text/csv');

    $storage = new Memory();

    $config = [
      'resource' => $resource,
      'storage' => $this->database,
      'parser' => Csv::getParser(),
    ];

    $results = [];
    do {
      $import_job = ImportJob::get('1', $storage, $config);
      $import_job->setTimeLimit(1);
      $import_job->run();
      $this->assertNotEquals(
        Result::ERROR,
        $import_job->getResult()->getStatus()
      );
      $results += $import_job->getStorage()->retrieveAll();
    } while ($import_job->getResult()->getStatus() != Result::DONE);

    $a = '["1","11110000","L","1","DESIGNATED","16.814","16.846","51.484"]';
    $this->assertEquals($a, $results[0]);

    $b = '["5083","87080001","R","1","DESIGNATED","1.074","1.177","163.244"]';
    $this->assertEquals($b, $results[5001]);

    $c = '["11001","57060000","R","1","DESIGNATED","4.505","4.682","285.7762"]';
    $this->assertEquals($c, $results[10001]);
  }

  /**
   * This is the same as testLargeImport but expects more than one pass.
   */
  public function testMultiplePasses() {
    $this->markTestIncomplete('This does not always use more than one pass.');
    $resource = new DatastoreResource(1, __DIR__ . '/../../../../data/Bike_Lane.csv', 'text/csv');

    $storage = new Memory();

    $config = [
      'resource' => $resource,
      'storage' => $this->database,
      'parser' => Csv::getParser(),
    ];

    $results = [];
    $passes = 0;
    do {
      $import_job = ImportJob::get('1', $storage, $config);
      $import_job->setTimeLimit(1);
      $import_job->run();
      $this->assertNotEquals(
        Result::ERROR,
        $import_job->getResult()->getStatus()
      );
      $results += $import_job->getStorage()->retrieveAll();
      ++$passes;
    } while ($import_job->getResult()->getStatus() != Result::DONE);

    // How many passses did it take?
    $this->assertGreaterThan(1, $passes);

    $a = '["1","11110000","L","1","DESIGNATED","16.814","16.846","51.484"]';
    $this->assertEquals($a, $results[0]);

    $b = '["5083","87080001","R","1","DESIGNATED","1.074","1.177","163.244"]';
    $this->assertEquals($b, $results[5001]);

    $c = '["11001","57060000","R","1","DESIGNATED","4.505","4.682","285.7762"]';
    $this->assertEquals($c, $results[10001]);
  }

  /**
   *
   */
  public function testBadStorage() {
    $this->expectExceptionMessage('Storage must be an instance of ' . DatabaseTableInterface::class);
    $resource = new DatastoreResource(1, __DIR__ . '/../../../../data/countries.csZv', 'text/csv');

    ImportJob::get('1', new Memory(), [
      'resource' => $resource,
      'storage' => new TestMemStorageBad(),
      'parser' => Csv::getParser(),
    ]);
  }

  /**
   *
   */
  public function testNonStorage() {
    $this->expectExceptionMessage('Storage must be an instance of Drupal\common\Storage\DatabaseTableInterface');
    $resource = new DatastoreResource(1, __DIR__ . '/../../../../data/countries.csv', 'text/csv');
    ImportJob::get('1', new Memory(), [
      'resource' => $resource,
      'storage' => new class() {

      },
      'parser' => Csv::getParser(),
    ]);
  }

  public function sanitizeDescriptionProvider(): array {
    return [
      'multiline' => ["Multi\nLine", 'Multi Line'],
    ];
  }

  /**
   * @dataProvider sanitizeDescriptionProvider
   * @covers ::sanitizeDescription
   */
  public function testSanitizeDescription($column, $expected) {
    $this->assertEquals($expected, ImportJob::sanitizeDescription($column));
  }

  public function sanitizeHeaderProvider() {
    return [
      'reserved_word' => ['accessible', '_accessible'],
      'numeric' => [1, '_1'],
    ];
  }

  /**
   * @dataProvider sanitizeHeaderProvider
   * @covers ::sanitizeHeader
   */
  public function testSanitizeHeader($column, $expected) {
    $this->assertEquals($expected, ImportJob::sanitizeHeader($column));
  }

  public function truncateHeaderProvider(): array {
    $max_length = 64;
    return [
      'max_length' => [
        str_repeat('a', $max_length),
        $max_length,
      ],
      'longer_length' => [
        str_repeat('b', $max_length + 1),
        $max_length,
      ],
    ];
  }

  /**
   * @dataProvider truncateHeaderProvider
   * @covers ::truncateHeader
   */
  public function testTruncateHeader($column, $expected) {
    $this->assertEquals($expected, strlen(ImportJob::truncateHeader($column)));
  }

}
