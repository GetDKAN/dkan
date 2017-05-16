<?php

namespace Drupal\DKANExtension\Context;

use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\AfterFeatureScope;

/**
 * Defines application features from the specific context.
 */
class TimezoneContext extends RawDKANContext {
  public static $originalTimezone = '';

  /**
   * Set the timezone to UTC for tests.
   *
   * @BeforeFeature @timezone
   */
  public static function setupFeatureTimezone(BeforeFeatureScope $scope) {
    self::$originalTimezone = variable_get('date_default_timezone');
    variable_set('date_default_timezone', 'UTC');
    drupal_flush_all_caches();
  }

  /**
   * Restore the timezone to the pre-testing configuration.
   *
   * @AfterFeature @timezone
   */
  public static function teardownFeatureTimezone(AfterFeatureScope $scope) {
    variable_set('date_default_timezone', self::$originalTimezone);
  }

}
