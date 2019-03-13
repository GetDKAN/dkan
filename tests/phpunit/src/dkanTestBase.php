<?php

use \PHPUnit\Framework\TestCase;

class dkanTestBase extends TestCase
{

	protected $dkanDirectory;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->dkanDirectory = realpath(dirname(__FILE__) . '/../../../');
  }


}
