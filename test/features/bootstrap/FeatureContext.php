<?php

use Drupal\DKANExtension\Context\RawDKANContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDKANContext
{
  protected $old_global_user;

  // This file is only meant for temporary custom step functions or overrides to the dkanextension.
  // Changes should be implemented in dkanextension so that it works across all projects.

}
