<?php

namespace Drupal\DKANExtension\Context;

use Behat\Behat\Hook\Scope\AfterScenarioScope;

/**
 * Defines application features from the specific context.
 */
class TimezoneContext extends RawDKANContext {
  public $currentTimezone = '';

  /**
   * Allow tests to change the timezone setting.
   *
   * @Given I set the default timezone to :option
   */
  public function iSetTheDefaultTimeszoneTo($option) {
    // Save the original timezone to restore after testing.
    $this->currentTimezone = variable_get('date_default_timezone', $this->currentTimezone);
    variable_set('date_default_timezone', $option);
    drupal_flush_all_caches();
  }

  /**
   * Restore the timezone to the pre-testing configuration.
   *
   * @AfterScenario @resetTimezone
   */
  public function afterScenarioTimezone(AfterScenarioScope $scope) {
    variable_set('date_default_timezone', $this->currentTimezone);
    drupal_flush_all_caches();
  }

}
