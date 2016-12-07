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
   * @When I hover over the admin menu item :item
   */
  public function iHoverOverTheAdminMenuItem($item) {
    $session = $this->getSession();
    $page = $session->getPage();

    $menu = $page->findById('admin-menu-wrapper');
    if (null === $menu) {
      throw new \InvalidArgumentException(sprintf('The admin-menu could not be found'));
    }

    $element = $menu->findLink($item);
    if (null === $element) {
        throw new \InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $item));
    }

    $element->mouseOver();
  }

  /**
   * @When I should see the admin menu item :item
   */
  public function iShouldSeeTheAdminMenuItem($item) {
    $session = $this->getSession();
    $page = $session->getPage();
    $menu = $page->findById('admin-menu-wrapper');
    $element = $menu->findLink($item);
    if (null === $element) {
        throw new \InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $item));
    }
  }

  /**
   * @When I switch to window
   */
  public function iSwitchToPopup() {
    $windowNames = $this->getSession()->getWindowNames();
    if (count($windowNames) > 1) {
      $this->getSession()->switchToWindow($windowNames[1]);
    }
  }
}
