<?php

/**
 * @file
 */

include_once __DIR__ . '/includes/HarvestSourceDataJsonStub.php';

/**
 *
 */
class HarvestCacheTest extends PHPUnit_Framework_TestCase {

  public $processed = array(
    array(
      'title' => 'Test entrie',
      'flag' => HarvestCache::DKAN_HARVEST_CACHE_PROCESSED,
    ),
    array(
      'title' => 'Test entrie',
      'flag' => HarvestCache::DKAN_HARVEST_CACHE_PROCESSED,
    ),
    array(
      'title' => 'Test entrie',
      'flag' => HarvestCache::DKAN_HARVEST_CACHE_FAILED,
    ),
    array(
      'title' => 'Test entrie filtered 1',
      'flag' => HarvestCache::DKAN_HARVEST_CACHE_FILTERED,
    ),
    array(
      'title' => 'Test entrie filtered 1',
      'flag' => HarvestCache::DKAN_HARVEST_CACHE_FILTERED,
    ),
    array(
      'title' => 'Test entrie',
      'flag' => HarvestCache::DKAN_HARVEST_CACHE_EXCLUDED,
    ),
    array(
      'title' => 'Test entrie',
      'flag' => HarvestCache::DKAN_HARVEST_CACHE_EXCLUDED,
    ),
    array(
      'title' => 'Test entrie',
      'flag' => HarvestCache::DKAN_HARVEST_CACHE_EXCLUDED,
    ),
    array(
      'title' => 'Test entrie',
      'flag' => HarvestCache::DKAN_HARVEST_CACHE_PROCESSED,
    ),
    array(
      'title' => 'Test entrie',
      'flag' => HarvestCache::DKAN_HARVEST_CACHE_PROCESSED,
    ),
    array(
      'title' => 'Test entrie',
      'flag' => HarvestCache::DKAN_HARVEST_CACHE_PROCESSED,
    ),
    array(
      'title' => 'Test entrie defaulted 1',
      'flag' => HarvestCache::DKAN_HARVEST_CACHE_FILTERED | HarvestCache::DKAN_HARVEST_CACHE_DEFAULTED,
    ),
    array(
      'title' => 'Test entrie overridden 1',
      'flag' => HarvestCache::DKAN_HARVEST_CACHE_FILTERED | HarvestCache::DKAN_HARVEST_CACHE_OVERRIDDEN,
    ),
    array(
      'title' => 'Test entrie overridden 2',
      'flag' => HarvestCache::DKAN_HARVEST_CACHE_OVERRIDDEN | HarvestCache::DKAN_HARVEST_CACHE_FILTERED,
    ),
    array(
      'title' => 'Test entrie overridden 3',
      'flag' => HarvestCache::DKAN_HARVEST_CACHE_PROCESSED | HarvestCache::DKAN_HARVEST_CACHE_OVERRIDDEN,
    ),
  );

  /**
   *
   */
  public function testProccessed() {
    $harvestSource = new HarvestSourceDataJsonStub(__DIR__ . '/data/dkan_harvest_datajson_test_original.json');

    $harvestCache = new HarvestCache($harvestSource, time(), $this->processed);

    $this->assertEquals($harvestCache->getProcessed(), $this->processed);

    $harvestCache->setCacheEntryProcessed('newprocessed', 'New Processed entry');

    $this->assertEquals($harvestCache->getProcessedCount(), count($this->processed) + 1);

    return $harvestCache;
  }

  /**
   * @depends testProccessed
   */
  public function testFailed($harvestCache) {
    $failed = array_filter($this->processed, function ($entry) {
      return ($entry['flag'] == HarvestCache::DKAN_HARVEST_CACHE_FAILED);
    });

    $this->assertEquals($harvestCache->getFailed(), $failed);

    $harvestCache->setCacheEntryFailed('newfailed', 'New Processed entry');

    $this->assertEquals($harvestCache->getFailedCount(), count($failed) + 1);
  }

  /**
   * @depends testProccessed
   */
  public function testFiltered($harvestCache) {
    $filtered = array_filter($this->processed, function ($entry) {
      return ($entry['flag'] & HarvestCache::DKAN_HARVEST_CACHE_FILTERED);
    });

    $this->assertEquals($harvestCache->getFiltered(), $filtered);

    $harvestCache->setCacheEntryFiltered('newfiltered', 'New Processed entry');

    $this->assertEquals($harvestCache->getFilteredCount(), count($filtered) + 1);
  }

  /**
   * @depends testProccessed
   */
  public function testExcluded($harvestCache) {
    $excluded = array_filter($this->processed, function ($entry) {
      return ($entry['flag'] == HarvestCache::DKAN_HARVEST_CACHE_EXCLUDED);
    });

    $this->assertEquals($harvestCache->getExcluded(), $excluded);

    $harvestCache->setCacheEntryExcluded('newexcluded', 'New Processed entry');

    $this->assertEquals($harvestCache->getExcludedCount(), count($excluded) + 1);
  }

  /**
   * @depends testProccessed
   */
  public function testDefaulted($harvestCache) {
    $defaulted = array_filter($this->processed, function ($entry) {
      return ($entry['flag'] == (HarvestCache::DKAN_HARVEST_CACHE_FILTERED | HarvestCache::DKAN_HARVEST_CACHE_DEFAULTED));
    });

    $this->assertEquals($harvestCache->getDefaulted(), $defaulted);

    $harvestCache->setCacheEntryFiltered('newdefaulted', 'New Processed entry');
    $harvestCache->setCacheEntryDefaulted('newdefaulted');

    $this->assertEquals($harvestCache->getDefaultedCount(), count($defaulted) + 1);
  }

  /**
   * @depends testProccessed
   */
  public function testOverridden($harvestCache) {
    $overridden = array_filter($this->processed, function ($entry) {
      return ($entry['flag'] == (HarvestCache::DKAN_HARVEST_CACHE_OVERRIDDEN | HarvestCache::DKAN_HARVEST_CACHE_FILTERED));
    });

    $this->assertEquals($harvestCache->getOverridden(), $overridden);

    $harvestCache->setCacheEntryFiltered('newoverridden', 'New Processed entry');
    $harvestCache->setCacheEntryOverridden('newoverridden');

    $this->assertEquals($harvestCache->getOverriddenCount(), count($overridden) + 1);
  }

}
