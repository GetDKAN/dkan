<?php

namespace Drupal\harvest;

/**
 * Localize files for a given harvest all at once.
 */
class HarvestLocalizer {

  protected HarvestService $harvestService;

  public function __construct(HarvestService $harvestService) {
    $this->harvestService = $harvestService;
  }

  public function localize($harvest_id) {

  }

}
