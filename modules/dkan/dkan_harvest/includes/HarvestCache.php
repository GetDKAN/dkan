<?php

/**
 * @file
 * File for dkan_harvest HarvestSource class.
 *
 * This will serve as a in code documentation as well.
 * Please update the comments if you update the class!
 */

/**
 * Class to store caching informations about the harvest source.
 *
 * Each dataset id added can have multiple flags.
 */
class HarvestCache {
  const DKAN_HARVEST_CACHE_PROCESSED = 0;
  const DKAN_HARVEST_CACHE_FAILED = 1;
  const DKAN_HARVEST_CACHE_FILTERED = 2;
  const DKAN_HARVEST_CACHE_EXCLUDED = 4;
  const DKAN_HARVEST_CACHE_DEFAULTED = 8;
  const DKAN_HARVEST_CACHE_OVERRIDDEN = 16;

  public $harvestSource;
  public $harvestCacheTime;
  public $processed;

  /**
   * Constructor for HarvestCache class.
   */
  public function __construct(HarvestSource $harvest_source = NULL, $harvestcache_time = NULL, $processed = array()) {
    if (is_a($harvest_source, 'HarvestSource')) {
      $this->harvestSource = $harvest_source;
    }
    else {
      throw new Exception('HarvestSource not valid!');
    }

    if (!isset($harvestcache_time)) {
      $harvestcache_time = time();
    }

    $this->harvestCacheTime = $harvestcache_time;
    $this->processed = $processed;
  }

  /**
   * Get all the processed harvest source elements.
   */
  public function getProcessed() {
    return $this->processed;
  }

  /**
   * Get processed elements count.
   */
  public function getProcessedCount() {
    return count($this->getProcessed());
  }

  /**
   * Get all the failed processed harvest source elements.
   */
  public function getFailed() {
    return array_filter($this->processed,
      function ($processed_flag) {
        return ($processed_flag['flag'] & self::DKAN_HARVEST_CACHE_FAILED);
      });
  }

  /**
   * Get all the count of failed processed harvest source elements.
   */
  public function getFailedCount() {
    return count($this->getFailed());
  }

  /**
   * Get all the filtered processed harvest source elements.
   */
  public function getFiltered() {
    return array_filter($this->processed,
      function ($processed_flag) {
        return ($processed_flag['flag'] & self::DKAN_HARVEST_CACHE_FILTERED) == self::DKAN_HARVEST_CACHE_FILTERED;
      });
  }

  /**
   * Get the count of all filtered processed harvest source elements.
   */
  public function getFilteredCount() {
    return count($this->getFiltered());
  }

  /**
   * Get all the excluded processed harvest source elements.
   */
  public function getExcluded() {
    return array_filter($this->processed,
      function ($processed_flag) {
        return ($processed_flag['flag'] & self::DKAN_HARVEST_CACHE_EXCLUDED);
      });
  }

  /**
   * Get the count of excluded processed harvest source elements.
   */
  public function getExcludedCount() {
    return count($this->getExcluded());
  }

  /**
   * Get the defaulted saved harvest source elements.
   */
  public function getDefaulted() {
    return array_filter($this->getSaved(),
      function ($processed_flag) {
        return ($processed_flag['flag'] & self::DKAN_HARVEST_CACHE_DEFAULTED) == self::DKAN_HARVEST_CACHE_DEFAULTED;
      });
  }

  /**
   * Get the count of defaulted saved harvest source elements.
   */
  public function getDefaultedCount() {
    return count($this->getDefaulted());
  }

  /**
   * Get the overridden saved harvest source elements.
   */
  public function getOverridden() {
    return array_filter($this->getSaved(),
      function ($processed_flag) {
        return ($processed_flag['flag'] & self::DKAN_HARVEST_CACHE_OVERRIDDEN) == self::DKAN_HARVEST_CACHE_OVERRIDDEN;
      });
  }

  /**
   * Get the count of overridden saved harvest source elements.
   */
  public function getOverriddenCount() {
    return count($this->getOverridden());
  }

  /**
   * Get cached source elements that were saved to the cache directory.
   */
  public function getSaved() {
    // If this source was filtered. start with the filtered elements.
    // Else use all the proccess elements.
    $base = $this->getFiltered();

    if (empty($base)) {
      $base = $this->processed;
    }

    // From the base processed elements, drop all the elements that were
    // excluded or failed.
    return array_filter($base,
      function ($base_flag) {
        return !($base_flag['flag'] & (self::DKAN_HARVEST_CACHE_EXCLUDED | self::DKAN_HARVEST_CACHE_FAILED));
      });
  }

  /**
   * Get count of cached source elements saved in the cache directory.
   */
  public function getSavedCount() {
    return count($this->getSaved());
  }

  /**
   * Set cache entry with specific flag.
   *
   * @param string $cache_id
   *        An id to name a cache entry.
   * @param int $flag
   *        Status of a cache entry.
   */
  public function setCacheEntry($cache_id, $flag, $title) {
    if (!isset($this->processed[$cache_id])) {
      $this->processed[$cache_id] = array('flag' => $flag, 'title' => $title);
    }
    else {
      $this->processed[$cache_id]['title'] = $title;
      if ($flag == self::DKAN_HARVEST_CACHE_PROCESSED) {
        // Processed means no failure. So we make sure that the failed flag is
        // removed.
        $this->processed[$cache_id]['flag'] &= ~(self::DKAN_HARVEST_CACHE_FAILED);
      }
      else {
        $this->processed[$cache_id]['flag'] = $this->processed[$cache_id]['flag'] | $flag;
      }
    }
  }

  /**
   * Flag a element as processed.
   */
  public function setCacheEntryProcessed($cache_id, $title = NULL) {
    $this->setCacheEntry($cache_id, self::DKAN_HARVEST_CACHE_PROCESSED, $title);
  }

  /**
   * Flag a element as failed.
   */
  public function setCacheEntryFailed($cache_id, $title = NULL) {
    $this->setCacheEntry($cache_id, self::DKAN_HARVEST_CACHE_FAILED, $title);
  }

  /**
   * Flag a element as filtered.
   */
  public function setCacheEntryFiltered($cache_id, $title = NULL) {
    $this->setCacheEntry($cache_id, self::DKAN_HARVEST_CACHE_FILTERED, $title);
  }

  /**
   * Flag a element as excluded.
   */
  public function setCacheEntryExcluded($cache_id, $title = NULL) {
    $this->setCacheEntry($cache_id, self::DKAN_HARVEST_CACHE_EXCLUDED, $title);
  }

  /**
   * Flag a element as defaulted.
   */
  public function setCacheEntryDefaulted($cache_id, $title = NULL) {
    $this->setCacheEntry($cache_id, self::DKAN_HARVEST_CACHE_DEFAULTED, $title);
  }

  /**
   * Flag a element as overridden.
   */
  public function setCacheEntryOverridden($cache_id, $title = NULL) {
    $this->setCacheEntry($cache_id, self::DKAN_HARVEST_CACHE_OVERRIDDEN, $title);
  }

}
