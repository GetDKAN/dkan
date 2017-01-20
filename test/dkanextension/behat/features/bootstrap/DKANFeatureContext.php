<?php

use Drupal\DrupalExtension\Context\DKAN\DKANContext;

/**
 * Defines application features from the specific context.
 */
class DKANFeatureContext extends DKANContext {

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
  }

}
