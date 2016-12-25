<?php

/**
 * @file
 * Base phpunit tests for HarvestSourceType class.
 */

class HarvestSourceTypeTest extends \PHPUnit_Framework_TestCase {

  // dkan_harvest_test status.
  public static $dkanHarvestTestBeforClassStatus = TRUE;

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass() {
  }

  /**
   * {@inheritdoc}
   */
  public function setup() {
  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage HarvestSourceType machineName invalid!
   */
  public function testHarvestSourceTypeConstructMachineNameException() {
    $source_type = new HarvestSourceType(NULL, array(
      'label' => 'Dkan Harvest Another Test Type',
      'cache callback' => 'dkan_harvest_cache_default',
      'migration class' => 'HarvestMigration',
    ));
  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage HarvestSourceType cacheCallback invalid!
   */
  public function testHarvestSourceTypeConstructCacheCallbackMissingException() {
    $source_type = new HarvestSourceType('harvest_test_type', array(
      'label' => 'Dkan Harvest Another Test Type',
      'migration class' => 'HarvestMigration',
    ));
  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage HarvestSourceType cacheCallback invalid!
   */
  public function testHarvestSourceTypeConstructCacheCallbackNotFunctionException() {
    $source_type = new HarvestSourceType('harvest_test_type', array(
      'label' => 'Dkan Harvest Another Test Type',
      'cache callback' => 'harvest_callback_that_do_not_exists',
      'migration class' => 'HarvestMigration',
    ));
  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage HarvestSourceType migrate invalid!
   */
  public function testHarvestSourceTypeConstructMigrationClassMissingException() {
    $source_type = new HarvestSourceType('harvest_test_type', array(
      'label' => 'Dkan Harvest Another Test Type',
      'cache callback' => 'dkan_harvest_cache_default',
    ));
  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage HarvestSourceType migrate invalid!
   */
  public function testHarvestSourceTypeConstructMigrationClassNotExistsException() {
    $source_type = new HarvestSourceType('harvest_test_type', array(
      'label' => 'Dkan Harvest Another Test Type',
      'cache callback' => 'dkan_harvest_cache_default',
      'migration class' => 'NotReallyAHarvestClass',
    ));
  }

  /**
   *
   */
  public function testHarvestSourceTypeConstruct() {
    $source_type = new HarvestSourceType('harvest_test_type', array(
      'label' => 'Dkan Harvest Another Test Type',
      'cache callback' => 'dkan_harvest_cache_default',
      'migration class' => 'HarvestMigration',
    ));
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown() {
  }

  /**
   * {@inheritdoc}
   */
  public static function tearDownAfterClass() {
  }
}
