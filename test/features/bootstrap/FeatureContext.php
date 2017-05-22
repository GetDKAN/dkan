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
  /**
   * Assert selector count in given region.
   *
   * @Then I should see exactly :number :selector in region :region
   */
  public function iShouldSeeExactlyInSelectorInRegion($number, $selector, $region) {
    $session = $this->getSession();
    $region_obj = $session->getPage()->find('region', $region);
    if ($region_obj == null) {
      throw new \Exception(sprintf('The region "%s" was not found on the page %s', $region, $this->getSession()->getCurrentUrl()));
    }
    $elements = $region_obj->findAll('css', $selector);
    $count = count($elements);
    if ($count != $number) {
      throw new \Exception(sprintf('The selector "%s" was found %d times in the %s region on the page %s', $selector, $count, $region, $this->getSession()->getCurrentUrl()));
    }
  }

}
