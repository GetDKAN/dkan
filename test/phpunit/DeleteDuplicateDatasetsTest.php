<?php
/**
 * @file
 * Unit tests for MigrateDataJsonDatasetBase::deleteDuplicateDatasets().
 */

/**
 * Test MigrateDataJsonDatasetBase::deleteDuplicateDatasets().
 */
class TestMigrateDataJsonDatabaseDeleteDuplicates extends \PHPUnit_Framework_TestCase {
  protected $migration;
  protected $row;
  protected $dataset1;
  protected $dataset2;
  protected $resource;

  /**
   * Setup.
   */
  public function setup() {
    include "dkan_migrate_base.module";
    $node = new stdClass();
    $node->title = 'Test:: MigrateDataJsonDatabase Class';
    $node->type = 'dataset';
    node_object_prepare($node);
    $node->language = LANGUAGE_NONE;
    $node->uid = 1;

    $node2 = clone $node;
    $node3 = clone $node;

    node_save($node);
    $this->dataset1 = $node;

    node_save($node2);
    $this->dataset2 = $node2;

    $node3->type = 'resource';
    node_save($node3);
    $this->resource = $node3;

    $this->migration = new MigrateDataJsonDatasetBase();

    $row = new stdClass();
    $row->title = $node->title;
    $this->row = $row;

  }

  /**
   * There are two datasets with the name "Test:: MigrateDataJsonDatabase".
   */
  public function testSetup() {
    $actual = db_select('node', 'n')
      ->condition('title', 'Test:: MigrateDataJsonDatabase Class')
      ->fields('n', array('nid'))
      ->countQuery()
      ->execute()
      ->fetchField();
    $expected = 3;
    $this->assertEquals($actual, $expected, 'There are three test nodes.');
  }

  /**
   * Delete datasets with the same title (duplicates) as the row.
   */
  public function testDeleteDuplicateDatasets() {
    $this->migration->deleteDuplicateDataSets($this->row);
    $actual = db_select('node', 'n')
      ->condition('title', 'Test:: MigrateDataJsonDatabase Class')
      ->condition('type', 'dataset')
      ->fields('n', array('nid'))
      ->countQuery()
      ->execute()
      ->fetchField();
    $expected = 0;
    $this->assertEquals($actual, $expected, 'If datasets exists with the same name as row then delete datasets.');
  }

  /**
   * Do not delete the existing dataset.
   */
  public function testDoNotDeleteExistingDataset($data) {
    $this->row->existing = $this->dataset1;
    $this->migration->deleteDuplicateDataSets($this->row);

    $actual = db_select('node', 'n')
      ->condition('title', 'Test:: MigrateDataJsonDatabase Class')
      ->fields('n', array('nid'))
      ->execute()
      ->fetchField();
    $expected = $this->dataset1->nid;
    $this->assertEquals($actual, $expected, "The existing dataset does not get deleted.");
  }

  /**
   * Do not delete resources with same name.
   */
  public function testDoNotDeleteDuplicateResources($data) {
    $this->migration->deleteDuplicateDataSets($this->row);
    $actual = db_select('node', 'n')
      ->condition('title', 'Test:: MigrateDataJsonDatabase Class')
      ->fields('n', array('nid'))
      ->execute()
      ->fetchField();
    $expected = $this->resource->nid;

    $this->assertEquals($actual, $expected, "The existing dataset does not get deleted.");
  }

  /**
   * Teardown.
   */
  public function tearDown() {
    $nids = db_select('node', 'n')
      ->condition('title', 'Test:: MigrateDataJsonDatabase Class')
      ->fields('n', array('nid'))
      ->execute()
      ->fetchAllKeyed();
    node_delete_multiple(array_keys($nids));
  }

}
