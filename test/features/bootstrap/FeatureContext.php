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

  /**
   * @Then the :tag element with id set to :value in the :region( region) exists
   *
   * This is a reword of the MarkupContext::assertRegionElementAttribute()
   * method which only checks for the first matched tag not the matched
   * attribute. Also added tests for element visibility.
   */
  public function assertRegionElementId($tag, $value, $region) {
    $attribute = 'id';

    $session = $this->getSession();
    $regionObj = $session->getPage()->find('region', $region);
    if (!$regionObj) {
      throw new \Exception(sprintf('No region "%s" found on the page %s.', $region, $session->getCurrentUrl()));
    }

    $elements = $regionObj->findAll('css', $tag);
    if (empty($elements)) {
      throw new \Exception(sprintf('The element "%s" was not found in the "%s" region on the page %s', $tag, $region, $this->getSession()->getCurrentUrl()));
    }

    $found_attr = FALSE;
    // Loop threw all the matching elements.
    foreach ($elements as $element) {
      $attr = $element->getAttribute($attribute);
      if (!empty($attr)) {
        $found_attr = TRUE;
        if (strpos($attr, "$value") !== FALSE) {
          // Found match.
          return $element;
        }
      }
    }

    if (!$found_attr) {
      throw new \Exception(sprintf('The "%s" attribute is not present on the element "%s" in the "%s" region on the page %s', $attribute, $tag, $region, $this->getSession()->getCurrentUrl()));
    }
    else {
      throw new \Exception(sprintf('The "%s" attribute does not equal "%s" on the element "%s" in the "%s" region on the page %s', $attribute, $value, $tag, $region, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * @Then the :tag element with id set to :value in the :region( region) should be visible
   */
  public function assertRegionElementIdVisible($tag, $value, $region) {
    $element = $this->assertRegionElementId($tag, $value, $region);
    if (!$element->isVisible()) {
        throw new \Exception(sprintf('The "%s" attribute is not visible on the element "%s" in the "%s" region on the page %s', "id", $value, $tag, $region, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * @Then the :tag element with id set to :value in the :region( region) should not be visible
   */
  public function assertRegionElementIdNotVisible($tag, $value, $region) {
    $element = $this->assertRegionElementId($tag, $value, $region);
    if ($element->isVisible()) {
        throw new \Exception(sprintf('The "%s" attribute is visible on the element "%s" in the "%s" region on the page %s', 'id', $value, $tag, $region, $this->getSession()->getCurrentUrl()));
    }
  }

}
