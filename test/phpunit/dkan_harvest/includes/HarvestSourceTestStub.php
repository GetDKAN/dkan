<?php

/**
 *
 */
class HarvestSourceTestStub extends HarvestSource {

  /**
   *
   */
  public function __construct($machine_name, $uri) {
    $this->uri = $uri;
    $this->type = HarvestSourceType::getSourceType('harvest_test_type');
    $this->label = 'Dkan Harvest Test Source';
  }
}
