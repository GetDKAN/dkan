<?php

namespace Drupal\dkan_common\Tests;

use Drupal\Tests\UnitTestCase;

/**
 * Base class for phpunit for dkan modules.
 */
class DkanTestBase extends UnitTestCase {

  use DkanUnitTestTrait;

  protected $dkanDirectory;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->dkanDirectory = realpath(dirname(__FILE__) . '/../../../');
  }

}
