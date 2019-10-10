<?php

namespace Drupal\Tests\dkan_datastore\Unit\Storage;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Delete;
use Drupal\Core\Database\Query\Select;
use Drupal\Core\Database\Query\Update;
use Drupal\Core\Database\Schema;
use Drupal\Core\Database\Statement;
use Drupal\dkan_common\Tests\Mock\Chain;
use Drupal\dkan_datastore\Storage\JobStore;
use FileFetcher\FileFetcher;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class JobStoreTest extends TestCase {

  /**
   *
   */
  public function testConstruction() {
    $chain = (new Chain($this))
      ->add(Connection::class, "blah", "blah");

    $jobStore = new JobStore($chain->getMock());
    $this->assertTrue(is_object($jobStore));
  }

  /**
   *
   */
  public function testRetrieve() {
    $job_data = json_encode(new FileFetcher("file://" . __DIR__ . "/../../../data/countries.csv"));
    $job = (object) [];
    $job->ref_uuid = "1";
    $job->job_data = $job_data;

    $chain = (new Chain($this))
      ->add(Connection::class, "schema", Schema::class)
      ->add(Schema::class, "tableExists", TRUE)
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'condition', Select::class)
      ->add(Select::class, 'execute', Statement::class)
      ->add(Statement::class, 'fetch', $job);

    $jobStore = new JobStore($chain->getMock());
    $this->assertTrue(is_object($jobStore->retrieve("1", FileFetcher::class)));
  }

  /**
   *
   */
  public function testRetrieveAll() {
    $job_data = json_encode(new FileFetcher("file://" . __DIR__ . "/../../../data/countries.csv"));
    $job = (object) [];
    $job->ref_uuid = "1";
    $job->job_data = $job_data;

    $chain = (new Chain($this))
      ->add(Connection::class, "schema", Schema::class)
      ->add(Schema::class, "tableExists", TRUE)
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'execute', Statement::class)
      ->add(Statement::class, 'fetchAll', [$job]);

    $jobStore = new JobStore($chain->getMock());
    $this->assertTrue(is_array($jobStore->retrieveAll(FileFetcher::class)));
  }

  /**
   *
   */
  public function testStore() {
    $jobObject = new FileFetcher("file://" . __DIR__ . "/../../../data/countries.csv");

    $job_data = json_encode($jobObject);
    $job = (object) [];
    $job->jid = "1";
    $job->ref_uuid = "1";
    $job->job_data = $job_data;

    $chain = (new Chain($this))
      ->add(Connection::class, "schema", Schema::class)
      ->add(Schema::class, "tableExists", TRUE)
      ->add(Connection::class, 'select', Select::class, 'select_1')
      ->add(Select::class, 'fields', Select::class)
      ->add(Select::class, 'condition', Select::class)
      ->add(Select::class, 'execute', Statement::class)
      ->add(Statement::class, 'fetch', $job)
      ->add(Connection::class, 'update', Update::class)
      ->add(Update::class, "fields", Update::class)
      ->add(Update::class, "condition", Update::class)
      ->add(Update::class, "execute", NULL);

    $jobStore = new JobStore($chain->getMock());

    $this->assertEquals("", $jobStore->store("1", $jobObject));
  }

  /**
   *
   */
  public function testRemove() {
    $chain = (new Chain($this))
      ->add(Connection::class, "schema", Schema::class)
      ->add(Schema::class, "tableExists", TRUE)
      ->add(Connection::class, "delete", Delete::class)
      ->add(Delete::class, "condition", Delete::class)
      ->add(Delete::class, "execute", NULL);

    $jobStore = new JobStore($chain->getMock());

    $this->assertEquals("", $jobStore->remove("1", FileFetcher::class));
  }

}
