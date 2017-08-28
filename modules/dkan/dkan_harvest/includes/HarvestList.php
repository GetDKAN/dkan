<?php

/**
 * @file
 * Base MigrateList class for Harvest Migrations.
 *
 * Should be a simpler files retriving impletation then MigrateListFiles.
 */

/**
 * HarvestList class to hold the list of items to migrate.
 */
class HarvestList extends MigrateList {

  protected $sourceCacheDir;
  protected $files;

  protected $offset;
  protected $length;  

  /**
   * Constructor.
   *
   * @param string $source_cache_dir
   *        This will use the file name as the item id.
   */
  public function __construct($source_cache_dir, $options = array()) {
    parent::__construct();

    $this->sourceCacheDir = $source_cache_dir;
    $file_scan_options = array(
      'recurse' => FALSE,
      'key' => 'name',
    );

    $this->files = file_scan_directory($this->sourceCacheDir, '/(.*)/', $file_scan_options);

    $this->offset = isset($options['offset']) ? $options['offset'] : 0;
    $this->length = isset($options['length']) ? $options['length'] : 0;   
  }

  /**
   * Implements MigrateList::getIdList().
   *
   * Return an array of file names (without extension).
   */
  public function getIdList() {
    $idList = $this->getAllCachedFiles();

    if ($this->length) {
      return array_slice($idList, $this->offset, $this->length);
    }  

    return $idList;
  }

  /**
   * Return an array with all cached files (without extension).
   */
  public function getAllCachedFiles() {
    return array_keys($this->files);
  }  

  /**
   * Implements MigrateList::__toString().
   */
  public function __toString() {
    // Remove any leading.
    return preg_replace('@[^a-z0-9-]+@', '_', strtolower($this->sourceCacheDir));
  }

  /**
   * Implements MigrateList::computeCount().
   */
  public function computeCount() {
    return count($this->getIdList());
  }

}
