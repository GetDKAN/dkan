<?php

/**
 * @file
 * HarvestMigrateSourceList class.
 */

/**
 * Harvest implementation of the MigrateSourceList class.
 *
 * @class HarvestMigrateSourceList
 */
class HarvestMigrateSourceList extends MigrateSourceList {

  /**
   * Return List of IDs of the source items.
   */
  public function getIdList() {
    return $this->listClass->getIdList();
  }

}
