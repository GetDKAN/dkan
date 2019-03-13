<?php

namespace Drupal\Tests\dkan_harvest\Unit;

use Drupal\dkan_harvest\Load\Dkan8;

class TestDkan8 extends Dkan8 {

  protected $fileHelper;

  public function __construct($log, $config, $sourceId, $runId) {
    parent::__construct($log, $config, $sourceId, $runId);
    $this->fileHelper = new TestFileHelper();
  }

}
