<?php

namespace Drupal\Tests\common\Unit\Storage;

use Contracts\Mock\Storage\Memory;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Delete;
use Drupal\Core\Database\Query\Select;
use Drupal\Core\Database\Query\Update;
use Drupal\Core\Database\Schema;
use Drupal\Core\Database\StatementWrapper;
use Drupal\Core\Database\StatementWrapperIterator;
use Drupal\common\Storage\JobStore;
use FileFetcher\FileFetcher;
use MockChain\Chain;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @covers \Drupal\common\Storage\JobStore
 * @coversDefaultClass \Drupal\common\Storage\JobStore
 *
 * @group dkan
 * @group common
 * @group unit
 */
class JobStoreTest extends TestCase {

  /**
   *
   */
  public function testConstruction() {
    $chain = (new Chain($this))
      ->add(Connection::class, "schema", Schema::class)
      ->add(Schema::class, "tableExists", FALSE);

    $jobStore = new JobStore(
      FileFetcher::class,
      $chain->getMock(),
      $this->createStub(EventDispatcherInterface::class)
    );
    $this->assertTrue(is_object($jobStore));
  }

  /**
   *
   */
  public function testRetrieve() {
    $job_data = json_encode($this->getFileFetcher());
    $job = (object) [];
    $job->ref_uuid = "1";
    $job->job_data = $job_data;

    $fieldInfo = [
      (object) ['Field' => "ref_uuid"],
      (object) ['Field' => "job_data"],
    ];

    // Substitute StatementWrapper as necessary for Drupal 10.0.x BC.
    $statement_wrapper_class = class_exists(StatementWrapperIterator::class)
      ? StatementWrapperIterator::class : StatementWrapper::class;

    $chain = (new Chain($this))
      ->add(Connection::class, "schema", Schema::class)
      ->add(Schema::class, "tableExists", TRUE)
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'condition', Select::class)
      ->add(Select::class, 'execute', $statement_wrapper_class)
      ->add($statement_wrapper_class, 'fetch', $job)
      ->add(Connection::class, 'query', $statement_wrapper_class)
      ->add($statement_wrapper_class, 'fetchAll', $fieldInfo);

    $jobStore = new JobStore(
      FileFetcher::class,
      $chain->getMock(),
      $this->createStub(EventDispatcherInterface::class)
    );
    $this->assertEquals($job_data, $jobStore->retrieve("1", FileFetcher::class));
  }

  /**
   *
   */
  public function testRetrieveAll() {
    $job_data = json_encode($this->getFileFetcher());
    $job = (object) [];
    $job->ref_uuid = "1";
    $job->job_data = $job_data;

    $fieldInfo = [
      (object) ['Field' => "ref_uuid"],
      (object) ['Field' => "job_data"],
    ];

    $sequence = (new Sequence())
      ->add($fieldInfo)
      ->add([$job]);

    // Substitute StatementWrapper as necessary for Drupal 10.0.x BC.
    $statement_wrapper_class = class_exists(StatementWrapperIterator::class)
      ? StatementWrapperIterator::class : StatementWrapper::class;

    $chain = (new Chain($this))
      ->add(Connection::class, "schema", Schema::class)
      ->add(Schema::class, "tableExists", TRUE)
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'execute', $statement_wrapper_class)
      ->add(Connection::class, 'query', $statement_wrapper_class)
      ->add($statement_wrapper_class, 'fetchAll', $sequence);

    $jobStore = new JobStore(
      FileFetcher::class,
      $chain->getMock(),
      $this->createStub(EventDispatcherInterface::class)
    );
    $this->assertTrue(is_array($jobStore->retrieveAll()));
  }

  /**
   *
   */
  public function testStore() {
    $jobObject = $this->getFileFetcher();

    $job_data = json_encode($jobObject);
    $job = (object) [];
    $job->jid = "1";
    $job->ref_uuid = "1";
    $job->job_data = $job_data;

    $fieldInfo = [
      (object) ['Field' => "ref_uuid"],
      (object) ['Field' => "job_data"],
    ];

    // Substitute StatementWrapper as necessary for Drupal 10.0.x BC.
    $statement_wrapper_class = class_exists(StatementWrapperIterator::class)
      ? StatementWrapperIterator::class : StatementWrapper::class;

    $connection = (new Chain($this))
      ->add(Connection::class, "schema", Schema::class)
      ->add(Schema::class, "tableExists", TRUE)
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'condition', Select::class)
      ->add(Select::class, 'execute', $statement_wrapper_class)
      ->add($statement_wrapper_class, 'fetch', $job)
      ->add(Connection::class, 'update', Update::class)
      ->add(Update::class, "fields", Update::class)
      ->add(Update::class, "condition", Update::class)
      ->add(Update::class, "execute", NULL)
      ->add(Connection::class, 'query', $statement_wrapper_class)
      ->add($statement_wrapper_class, 'fetchAll', $fieldInfo)
      ->getMock();

    $jobStore = new JobStore(
      FileFetcher::class,
      $connection,
      $this->createStub(EventDispatcherInterface::class)
    );

    $this->assertEquals("1", $jobStore->store(json_encode($jobObject), "1"));
  }

  /**
   *
   */
  public function testRemove() {
    $fieldInfo = [
      (object) ['Field' => "ref_uuid"],
      (object) ['Field' => "job_data"],
    ];

    // Substitute StatementWrapper as necessary for Drupal 10.0.x BC.
    $statement_wrapper_class = class_exists(StatementWrapperIterator::class)
      ? StatementWrapperIterator::class : StatementWrapper::class;

    $connection = (new Chain($this))
      ->add(Connection::class, "schema", Schema::class)
      ->add(Schema::class, "tableExists", TRUE)
      ->add(Connection::class, "delete", Delete::class)
      ->add(Delete::class, "condition", Delete::class)
      ->add(Delete::class, "execute", NULL)
      ->add(Connection::class, 'query', $statement_wrapper_class)
      ->add($statement_wrapper_class, 'fetchAll', $fieldInfo)
      ->getMock();

    $jobStore = new JobStore(
      FileFetcher::class,
      $connection,
      $this->createStub(EventDispatcherInterface::class)
    );

    $this->assertEquals("", $jobStore->remove("1", FileFetcher::class));
  }

  /**
   * Private.
   */
  private function getFileFetcher() {
    return FileFetcher::get("1", new Memory(), ["filePath" => "file://" . __DIR__ . "/../../data/countries.csv"]);
  }

}
