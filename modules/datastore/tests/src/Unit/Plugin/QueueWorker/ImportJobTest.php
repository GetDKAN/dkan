<?php

namespace Drupal\Tests\datastore\Unit\Plugin\QueueWorker;

use Contracts\Mock\Storage\Memory;
use CsvParser\Parser\Csv;
use CsvParser\Parser\ParserInterface;
use Drupal\common\DataResource;
use Drupal\common\Storage\DatabaseTableInterface;
use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Unit tests for Importer class.
 *
 * @covers \Drupal\datastore\Plugin\QueueWorker\ImportJob
 * @coversDefaultClass \Drupal\datastore\Plugin\QueueWorker\ImportJob
 *
 * @group dkan
 * @group dkan-core
 * @group datastore
 * @group unit
 */
class ImportJobTest extends TestCase {

  /**
   * Database.
   *
   * @var \Drupal\common\Storage\DatabaseTableInterface
   */
  private $database;

  /**
   * This method is called before each test.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->database = new TestMemStorage();
    $this->assertTrue($this->database instanceof DatabaseTableInterface);

    $options = (new Options())
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->add('request_stack', RequestStack::class)
      ->index(0);
    $container = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(StreamWrapperManager::class, 'getViaUri', StreamWrapperInterface::class)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getHost', 'web');
    \Drupal::setContainer($container->getMock());
  }

  protected function tearDown(): void {
    parent::tearDown();
    $this->database = NULL;
  }

  /**
   * Get an ImportJob object.
   *
   * @param \Drupal\common\DataResource $resource
   *   DataResource object.
   *
   * @return \Drupal\datastore\Plugin\QueueWorker\ImportJob
   *   ImportJob object.
   */
  private function getImportJob(DataResource $resource): ImportJob {
    $storage = new Memory();
    $config = [
      'resource' => $resource,
      'storage' => $this->database,
      'parser' => Csv::getParser(),
    ];
    return ImportJob::get($resource->getUniqueIdentifier(), $storage, $config);
  }

  /**
   *
   */
  public function testBasics() {
    $resource = new DataResource(__DIR__ . '/../../../../data/countries.csv', 'text/csv');

    $import_job = $this->getImportJob($resource);

    $this->assertTrue($import_job->getParser() instanceof ParserInterface);
    $this->assertEquals(Result::WAITING, $import_job->getResult()->getStatus());

    $import_job->run();
    $this->assertNotEquals(Result::ERROR, $import_job->getResult()->getStatus());

    $schema = $import_job->getStorage()->getSchema();
    $this->assertTrue(is_array($schema['fields'] ?? FALSE));

    $status = $import_job->getResult()->getStatus();
    $this->assertEquals(Result::DONE, $status);

    $this->assertEquals(4, $import_job->getStorage()->count());

    $import_job->run();
    $status = $import_job->getResult()->getStatus();
    $this->assertEquals(Result::DONE, $status);

    $import_job->drop();

    $status = $import_job->getResult()->getStatus();
    $this->assertEquals(Result::STOPPED, $status);
  }

  /**
   *
   */
  public function testFileNotFound() {
    $resource = new DataResource(__DIR__ . '/../../../../data/non-existent.csv', 'text/csv');
    $datastore = $this->getImportJob($resource);
    $datastore->run();

    $this->assertEquals(Result::ERROR, $datastore->getResult()->getStatus());
  }

  /**
   *
   */
  public function testNonTextFile() {
    $resource = new DataResource(__DIR__ . '/../../../../data/non-text.csv', 'text/csv');
    $datastore = $this->getImportJob($resource);
    $datastore->run();

    $this->assertEquals(Result::ERROR, $datastore->getResult()->getStatus());
  }

  /**
   *
   */
  public function testDuplicateHeaders() {
    $resource = new DataResource(__DIR__ . '/../../../../data/duplicate-headers.csv', 'text/csv');
    $datastore = $this->getImportJob($resource);
    $datastore->run();

    $this->assertEquals(Result::ERROR, $datastore->getResult()->getStatus());
    $this->assertEquals('Duplicate headers error: bar, baz', $datastore->getResult()
      ->getError());
  }

  /**
   *
   */
  public function testLongColumnName() {
    $resource = new DataResource(__DIR__ . '/../../../../data/longcolumn.csv', 'text/csv');
    $datastore = $this->getImportJob($resource);
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
    $resource = new DataResource(__DIR__ . '/../../../../data/columnspaces.csv', 'text/csv');
    $datastore = $this->getImportJob($resource);
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
    $resource = new DataResource(__DIR__ . '/../../../../data/countries.csv', 'text/csv');

    $datastore = $this->getImportJob($resource);
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
    $resource = new DataResource(__DIR__ . '/../../../../data/Bike_Lane.csv', 'text/csv');

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
    $resource = new DataResource(__DIR__ . '/../../../../data/Bike_Lane.csv', 'text/csv');

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
    $resource = new DataResource(__DIR__ . '/../../../../data/countries.csZv', 'text/csv');

    ImportJob::get($resource->getUniqueIdentifier(), new Memory(), [
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
    $resource = new DataResource(__DIR__ . '/../../../../data/countries.csv', 'text/csv');
    ImportJob::get('1', new Memory(), [
      'resource' => $resource,
      'storage' => new class() {

      },
      'parser' => Csv::getParser(),
    ]);
  }

  public static function sanitizeDescriptionProvider(): array {
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

  public static function sanitizeHeaderProvider() {
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

  public static function truncateHeaderProvider(): array {
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
