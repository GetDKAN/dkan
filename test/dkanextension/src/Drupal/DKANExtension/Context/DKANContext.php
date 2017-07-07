<?php

namespace Drupal\DKANExtension\Context;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Mink\Driver\Selenium2Driver;
use EntityFieldQuery;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Defines application features from the specific context.
 */
class DKANContext extends RawDKANContext {

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
    // Set the default timezone to NY.
    date_default_timezone_set('America/New_York');
  }

  /****************************
   * HELPER FUNCTIONS
   ****************************/

  /**
   * Explode a comma separated string in a standard way.
   */
  public function explodeList($string) {
    $array = explode(',', $string);
    $array = array_map('trim', $array);
    return is_array($array) ? $array : array();
  }

  /**
   * Get Mink context.
   */
  public function getMink() {
    return $this->minkContext;
  }

  /*****************************
   * CUSTOM STEPS
   *****************************/

  /**
   * Confirm that an element is not visible.
   *
   * @Then the :tag element with id set to :value in the :region( region) should not be visible
   */
  public function assertRegionElementIdNotVisible($tag, $value, $region) {
    $element = $this->assertRegionElementId($tag, $value, $region);
    if ($element->isVisible()) {
      throw new \Exception(sprintf('The "%s" attribute is visible on the element "%s" in the "%s" region on the page %s', 'id', $value, $tag, $region, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * Confirm that an element is visible.
   *
   * @Then the :tag element with id set to :value in the :region( region) should be visible
   */
  public function assertRegionElementIdVisible($tag, $value, $region) {
    $element = $this->assertRegionElementId($tag, $value, $region);
    if (!$element->isVisible()) {
      throw new \Exception(sprintf('The "%s" attribute is not visible on the element "%s" in the "%s" region on the page %s', "id", $value, $tag, $region, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * Confirm that an element exists on a region.
   *
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
   * Custom step to switch to a different window/popup.
   *
   * @When I switch to window
   */
  public function iSwitchToPopup() {
    $windowNames = $this->getSession()->getWindowNames();
    if (count($windowNames) > 1) {
      $this->getSession()->switchToWindow($windowNames[1]);
    }
  }

  /**
   * Confirm if an admin menu item is visible.
   *
   * @When I should see the admin menu item :item
   */
  public function iShouldSeeTheAdminMenuItem($item) {
    $session = $this->getSession();
    $page = $session->getPage();
    $menu = $page->findById('admin-menu-wrapper');
    $element = $menu->findLink($item);
    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $item));
    }
  }

  /**
   * Custom step to hover over an admin menu item.
   *
   * @When I hover over the admin menu item :item
   */
  public function iHoverOverTheAdminMenuItem($item) {
    $session = $this->getSession();
    $page = $session->getPage();

    $menu = $page->findById('admin-menu-wrapper');
    if (NULL === $menu) {
      throw new \InvalidArgumentException(sprintf('The admin-menu could not be found'));
    }

    $element = $menu->findLink($item);
    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $item));
    }

    $element->mouseOver();
  }

  /**
   * Confirm that a cached page is present.
   *
   * @Then I :outcome see a cached page
   */
  public function iShouldSeeCachedPage($outcome) {
    $session = $this->getSession();
    $headers = $session->getResponseHeaders();
    $cacheControl = $headers['Cache-Control'][0];
    if (strpos($cacheControl, 'public') === FALSE && $outcome === 'should') {
      throw new \Exception(sprintf("Page should be cached"));
    }
    if (strpos($cacheControl, 'no-cache') === FALSE && $outcome === 'should not') {
      throw new \Exception(sprintf("Page should not be cached"));
    }
  }

  /**
   * Confirm that the administration menu is visible.
   *
   * @Then /^I should see the administration menu$/
   */
  public function iShouldSeeTheAdministrationMenu() {
    $xpath = "//div[@id='admin-menu']";
    // Grab the element.
    $element = $this->getXPathElement($xpath);
    if (!isset($element)) {
      throw new \Exception('The admin menu could not be found.');
    }
  }

  /**
   * Confirm that a text format option is available.
   *
   * @Then /^I should have an "([^"]*)" text format option$/
   */
  public function iShouldHaveAnTextFormatOption($option) {
    $xpath = "//select[@name='body[und][0][format]']//option[@value='" . $option . "']";
    // Grab the element.
    $element = $this->getXPathElement($xpath);
    if (!isset($element)) {
      throw new \Exception("The $option format option could not be found.");
    }
  }

  /**
   * Returns an element from an xpath string.
   *
   * @param string $xpath
   *   String representing the xpath.
   *
   * @return object
   *   A mink html element
   */
  protected function getXPathElement($xpath) {
    // Get the mink session.
    $session = $this->getSession();
    // Runs the actual query and returns the element.
    $element = $session->getPage()->find(
        'xpath',
        $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
    );
    // Errors must not pass silently.
    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));
    }
    return $element;
  }

  /**
   * Confirm that a number of items are visible on the region.
   *
   * @Then I should see :arg1 items in the :arg2 region
   */
  public function iShouldSeeItemsInTheRegion($arg1, $arg2) {
    $context = $this->minkContext;
    $region = $context->getRegion($arg2);
    $items = $region->findAll('css', '.views-row');
    $num = sizeof($items);
    if ($num === 0) {
      $items = $region->find('css', '.views-row-last');
      if (!empty($items)) {
        $num = 2;
      }
      else {
        $items = $region->find('css', '.views-row-first');
        if (!empty($items)) {
          $num = 1;
        }
      }
    }
    if ($num !== intval($arg1)) {
      throw new \Exception(sprintf("Did not find %d %s items, found %d instead.", $arg1, $arg2, sizeof($num)));
    }
  }

  /**
   * Confirm that a Gravatar image is visible on the region.
   *
   * @Then /^I should see a gravatar image in the "([^"]*)" region$/
   */
  public function iShouldSeeAGravatarImageInTheRegion($region) {
    $regionObj = $this->minkContext->getRegion($region);
    $elements = $regionObj->findAll('css', 'img');
    if (!empty($elements)) {
      foreach ($elements as $element) {
        if ($element->hasAttribute('src')) {
          $value = $element->getAttribute('src');
          if (preg_match('/\/\/www\.gravatar\.com\/avatar\/.*/', $value)) {
            return;
          }
        }
      }
    }
    throw new \Exception(sprintf('The element gravatar link was not found in the "%s" region on the page %s', $region, $this->getSession()->getCurrentUrl()));

  }

  /**
   * Confirm that a Gravatar image is not visible in the region.
   *
   * @Then /^I should not see a gravatar image in the "([^"]*)" region$/
   */
  public function iShouldNotSeeAGravatarImageInTheRegion($region) {
    $regionObj = $this->minkContext->getRegion($region);
    $elements = $regionObj->findAll('css', 'img');
    $match = FALSE;
    if (!empty($elements)) {
      foreach ($elements as $element) {
        if ($element->hasAttribute('src')) {
          $value = $element->getAttribute('src');
          if (preg_match('/\/\/www\.gravatar\.com\/avatar\/.*/', $value)) {
            $match = TRUE;
          }
        }
      }
    }
    if ($match) {
      throw new \Exception(sprintf('The element gravatar link was found in the "%s" region on the page %s', $region, $this->getSession()->getCurrentUrl()));
    }
    else {
      return;
    }
  }

  /**
   * Confirm that a user page is visible.
   *
   * @Then I should see (the|a) user page
   * @Then I should see the :user user page
   */
  public function assertSeeTheUserPage($user = FALSE) {

    // TODO: This relies on the breadcrumb, can it be made better?
    $regionObj = $this->minkContext->getRegion('breadcrumb');
    $val = $regionObj->find('css', '.active-trail');
    $html = $val->getHtml();
    if ($html !== $user) {
      throw new \Exception('Could not find user name in breadcrumb. Text found:' . $val);
    }

    $regionObj = $this->minkContext->getRegion('user page');
    $val = $regionObj->getText();
    if ($user !== FALSE && strpos($val, $user) === FALSE) {
      throw new \Exception('Could not find username in the user page region. Text found:' . $val);
    }
  }

  /**
   * @Then I should see (the|a) user command center
   * @Then I should see the :user user command center
   */
  public function assertSeeUserCommandCenter($user = FALSE) {
    $regionObj = $this->minkContext->getRegion('user command center');
    $val = $regionObj->getText();
    if ($user !== FALSE && strpos($val, $user) === FALSE) {
      throw new \Exception('Could not find username in the user command center region. Text found:' . $val);
    }
    // TODO: Consider checking for the elements that should be in the command center.
  }

  /**
   * @AfterScenario
   *
   * Delete any tempusers that were created outside of 'Given users'.
   */
  public function deleteTempUsers(AfterScenarioScope $scope) {
    if ($scope->getScenario()->hasTag('deleteTempUsers')) {
      // Get all users that start with tempUser*.
      $results = db_query("SELECT uid from users where name like 'tempuser%%'");
      foreach ($results as $user) {
        user_delete($user->uid);
      }
    }
  }

  // ------------- Junk from previous FeatureContext ------------------- //.
  /**
   * @Then /^the administrator role should have all permissions$/
   */
  public function theAdministratorRoleShouldHaveAllPermissions() {
    // Get list of all permissions.
    $permissions = array();
    foreach (module_list(FALSE, FALSE, TRUE) as $module) {
      // Drupal 7.
      if (module_invoke($module, 'permission')) {
        $permissions = array_merge($permissions, array_keys(module_invoke($module, 'permission')));
      }
    }
    $administrator_role = user_role_load_by_name('administrator');
    $administrator_perms = db_query("SELECT permission FROM {role_permission} WHERE rid = :admin_rid", array(':admin_rid' => $administrator_role->rid))
      ->fetchCol();
    foreach ($permissions as $perm) {
      if (!in_array($perm, $administrator_perms)) {
        echo $perm;
        throw new Exception(sprintf("Administrator role missing permission %s", $perm));
      }
    }
  }

  /**
   * @Given /^I scroll to the top$/
   */
  public function iScrollToTheTop() {
    $driver = $this->getSession()->getDriver();
    // Wait two seconds for admin menu if using js.
    if ($driver instanceof Selenium2Driver) {
      $this->getSession()->executeScript('window.scrollTo(0,0);');
    }
  }

  /**
   * @When /^I switch to the frame "([^"]*)"$/
   */
  public function iSwitchToTheFrame($frame) {
    $this->getSession()->switchToIFrame($frame);
  }

  /**
   * @Given /^I switch out of all frames$/
   */
  public function iSwitchOutOfAllFrames() {
    $this->getSession()->switchToIFrame();
  }

  /**
   * @Then /^I wait for the dialog box to appear$/
   */
  public function iWaitForTheDialogBoxToAppear() {
    $this->getSession()->wait(2000, "jQuery('#user-login-dialog').children().length > 0");
  }

  /**
   * @Given /^I wait for "([^"]*)" seconds$/
   */
  public function iWaitForSeconds($milliseconds) {
    $session = $this->getSession();
    $session->wait($milliseconds * 1000);
  }

  /**
   * Properly inputs item in field rendered by Chosen.js.
   *
   * @Given /^I fill in the chosen field "([^"]*)" with "([^"]*)"$/
   */
  public function iFillInTheChosenFieldWith($field, $value) {
    $session = $this->getSession();
    $field = $this->fixStepArgument($field);
    $value = $this->fixStepArgument($value);
    // Focus means autocoplete will actually show up.
    $xpath = '//div[@id="' . $field . '"]//input';
    $this->getSession()->getDriver()->focus($xpath);
    // $page->fillField($field, $value);.
    $this->iWaitForSeconds(1);
    // Selects the first dropdown since there is no id or other way to
    // reference the desired entry.
    $title = $session->getPage()->find(
        'xpath',
        $session->getSelectorsHandler()->selectorToXpath('xpath', '//li[.="' . $value . '"]')
    );
    if (!isset($title)) {
      throw new \Exception(sprintf('"' . $value . '" option was not found in the chosen field.'));
    }
    $title->click();
  }

  /**
   * @Given /^I select "([^"]*)" from "([^"]*)" chosen\.js select box$/
   **/
  public function iSelectFromChosenJsSelectBox($option, $select) {
    $select = $this->fixStepArgument($select);
    $option = $this->fixStepArgument($option);

    $page = $this->getSession()->getPage();
    $field = $page->findField($select, TRUE);

    if (NULL === $field) {
      throw new \Exception(sprintf('"' . $select . '" field was not found in the form.'));
    }

    $id = $field->getAttribute('id');
    $opt = $field->find('named', array('option', $option));

    if ($opt === NULL) {
      throw new \Exception(sprintf('"' . $option . '" option was not found in the chosen field.'));
    }

    $val = $opt->getValue();

    $javascript = "jQuery('#$id').val('$val');
                   jQuery('#$id').trigger('chosen:updated');
                   jQuery('#$id').trigger('change');";

    $this->getSession()->executeScript($javascript);
  }

  /**
   * @Then the :selector elements should be sorted in this order :order
   */
  public function theElementsShouldBeSortedInThisOrder($selector, $order) {
    $region = $this->getRegion("content");
    $items = $region->findAll('css', $selector);
    $actual_order = array();
    foreach ($items as $item) {
      if ($item->getText() !== "") {
        $actual_order[] = $item->getText();
      }
    }
    $order = explode(" > ", $order);
    if ($order !== $actual_order) {
      throw new Exception(sprintf("The elements were not sorted in the order provided."));
    }
  }

  /**
   * @Given /^I click the chosen field "([^"]*)" and enter "([^"]*)"$/
   *
   * DEPRECATED: DONT USE. The clicking of the chosen fields to select some values
   * didn't work well (selenium errors about the value not being visible). Commenting
   * this out for now in case someone wants to replace it later with something that works.
   */
  /*public function iClickTheChosenFieldAndEnter($field, $value) {
  $session = $this->getSession();
  $page = $session->getPage();
  $field = $this->fixStepArgument($field);
  $value = $this->fixStepArgument($value);
  // Click chosen field.
  $field_click = $session->getPage()->find(
  'xpath',
  $session->getSelectorsHandler()->selectorToXpath('xpath', '//span[.="' . $field . '"]')

  );
  $field_click->click();
  $this->iDebugWaitForSeconds(1);
  // Click value that now appears.
  $title = $session->getPage()->find(
  'xpath',
  $session->getSelectorsHandler()->selectorToXpath('xpath', '//li[.="' . $value . '"]')
  );
  if(!isset($title)){
  throw new Exception(sprintf('"' . $value . '" option was not found in the chosen field.'));
  }
  $title->click();
  }*/

  /**
   * Click some text.
   *
   * @When /^I click on the text "([^"]*)"$/
   */
  public function iClickOnTheText($text) {
    $session = $this->getSession();
    $element = $session->getPage()->find(
        'xpath',
        $session->getSelectorsHandler()->selectorToXpath(
            'xpath',
            '//*[contains(text(), "' . $text . '")]'
        )
    );
    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('Cannot find text: "%s"', $text));
    }
    $element->click();
  }

  /**
   * Click some exact text.
   *
   * @When I click on the exact text :text
   */
  public function iClickOnTheExactText($text) {
    $session = $this->getSession();
    $element = $session->getPage()->find(
        'xpath',
        $session->getSelectorsHandler()->selectorToXpath(
            'xpath',
            '//*[text() = "' . $text . '"]'
        )
    );
    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('Cannot find text: "%s"', $text));
    }
    $element->click();
  }

  /**
   * Click on map icon as identified by its z-index.
   *
   * @Given /^I click map icon number "([^"]*)"$/
   */
  public function iClickMapIcon($num) {
    $session = $this->getSession();
    $element = $session->getPage()->find(
        'xpath',
        $session->getSelectorsHandler()->selectorToXpath(
            'xpath',
            '//div[contains(@class, "leaflet-marker-pane")]//img[' . $num . ']'
        )
    );
    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('Cannot find map icon: "%s"', $num));
    }
    $element->click();
  }

  /**
   * @Given /^I empty the field "([^"]*)"$/
   */
  public function iEmptyTheField($locator) {
    $session = $this->getSession();
    $page = $session->getPage();
    $field = $page->findField($locator);

    if (NULL === $field) {
      throw new ElementNotFoundException(
        $this->getSession(), 'form field', 'id|name|label|value', $locator
      );
    }

    $field->setValue("");
  }

  /**
   * Wait for the given number of seconds. ONLY USE FOR DEBUGGING!
   *
   * @Given I debug wait for :time second(s)
   */
  public function iDebugWaitForSeconds($time) {
    sleep($time);
  }

  /**
   * Wait to click on something in case it does not appear immediately (javascript)
   *
   * @Given I wait and press :text
   */
  public function iWaitAndPress($text) {
    $wait = $this->jsContext->maximum_wait;
    try {
      $found = $this->jsContext->spin(
        function ($context) use ($text) {
            $context->getSession()->getPage()->pressButton($text);
            return (TRUE);
        }, $wait
      );
      return $found;
    }
    catch (\Exception $e) {
      throw new \Exception("Couldn't find button $text within $wait seconds");
    }
  }

  /**
   * @When I wait for :text to disappear
   * @param $text
   * @throws \Exception
   */
  public function iWaitForTextToDisappear($text) {
    $this->jsContext->spin(
        function ($context) use ($text) {
            return !$context->getSession()->getPage()->hasContent($text);
        }, 10
    );
  }

  /**
   * @When I attach the drupal file :arg1 to :arg2
   *
   * Overrides attachFileToField() in Mink context to fix but with relative
   * path.
   */
  public function iAttachTheDrupalFileTo($path, $field) {
    $field = $this->fixStepArgument($field);
    $path = $this->getMinkParameter('files_path') . '/' . $path;
    $this->getSession()->getPage()->attachFileToField($field, $path);
  }

  /**
   * @When I attach the file :path to :field using file resup
   */
  public function iAttachTheDrupalFileUsingFileResup($path, $field) {
    $path = $this->getMinkParameter('files_path') . '/' . $path;
    $field = $this->fixStepArgument($field);
    $session = $this->getSession();
    $page = $session->getPage();
    $session->executeScript('jQuery(".file-resup-wrapper input").show()');
    $session->executeScript('jQuery(".file-resup-wrapper input[name=\'' . $field . '\']").parent().find("input[type=\'file\']").attr("id", "' . $field . '")');
    $session->getPage()->attachFileToField($field, $path);
  }

  /**
   * Wait for upload file to finish.
   *
   * Wait until the class="progress-bar" element is gone,
   * or timeout after 30 seconds (30,000 ms).
   *
   * @Given /^I wait for the file upload to finish$/
   */
  public function iWaitForUploadFileToFinish() {
    $this->getSession()->wait(30000, 'jQuery(".progress-bar").length === 0');
  }

  /**
   * @Then I should see the list of permissions for :role role
   */
  public function iShouldSeePermissionsForRole($role) {

    $role_names = og_get_user_roles_name();
    if ($rid = array_search($role, $role_names)) {
      $permissions = og_role_permissions(array($rid => ''));
      foreach (reset($permissions) as $machine_name => $perm) {
        // Currently the permissions returned by og for a role are only the machine name and its true value,
        // need to find a way to find the checkbox of a permission and see if it is checked.
        $search = "edit-" . $rid . "-" . strtr($machine_name, " ", "-");
        if (!$this->getSession()->getPage()->hasCheckedField($search)) {
          throw new \Exception("Permission $machine_name is not set for $role.");
        }
      }
    }
  }

  /**
   * @Then I should get :format content from the :button button
   */
  public function assertButtonReturnsFormat($format, $button) {

    if ($button === "JSON") {
      $button = "json view of content";
    }

    $content = $this->getSession()->getPage()->findLink($button);
    try {
      $file = file_get_contents($content->getAttribute("href"));
    }
    catch (\Exception $e) {
      throw $e;
    }
    if ($format === "JSON") {
      json_decode($file);
      if (!json_last_error() == JSON_ERROR_NONE) {
        throw new \Exception("Not JSON format.");
      }
    }
  }

  /**
   * @Then I should see the redirect button for :site
   */
  public function assertRedirectButton($site) {
    $page = $this->getSession()->getPage();

    switch ($site) {
      case 'Google+':
        $element = $page->find('css', '.fa-google-plus-square');
        $link = $element->getParent()->getAttribute("href");
        $return = preg_match('#https:\/\/plus\.google\.com\/share\?url=.*dataset\/.*#', $link);
        break;

      case 'Twitter':
        $element = $page->find('css', '.fa-twitter-square');
        $link = $element->getParent()->getAttribute("href");
        $return = preg_match('#https:\/\/twitter\.com\/share\?url=.*dataset\/.*#', $link);
        break;

      case 'Facebook':
        $element = $page->find('css', '.fa-facebook-square');
        $link = $element->getParent()->getAttribute("href");
        $return = preg_match('#https:\/\/www\.facebook\.com\/sharer\.php.*dataset\/.*#', $link);
        break;

      default:
        throw new \Exception("Not a valid site for DKAN sharing.");
    }

    if (!$return) {
      throw new \Exception("The $site redirect button is not properly configured.");
    }
  }

  /**
   * @When I disable the module :module
   */
  public function iDisableTheModule($module) {
    module_disable(array($module));
  }

  /**
   * @When I enable the module :module
   */
  public function iEnableTheModule($module) {
    module_enable(array($module));
  }

  /**
   * Returns fixed step argument (with \\" replaced back to ").
   *
   * @param string $argument
   *
   * @return string
   */
  public function fixStepArgument($argument) {
    return str_replace('\\"', '"', $argument);
  }

  /**
   * Checks if a button with id|name|title|alt|value exists in a region.
   *
   * @Then I should not see the button :button in the :region( region)
   * @Then I should not see the :button button in the :region( region)
   *
   * @param $button
   *   string The id|name|title|alt|value of the button
   * @param $region
   *   string The region in which the button should not be found
   *
   * @throws \Exception
   *   If region cannot be found or the button is present on region.
   */
  public function iShouldNotSeeTheButtonInThe($button, $region) {
    $regionObj = $this->getMink()->getRegion($region);
    $buttonObj = $regionObj->findButton($button);
    if ($buttonObj) {
      throw new \Exception(sprintf("The button '%s' is present in the region '%s' on the page %s", $button, $region, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * @Then all default content with type :type and bundle :bundle listed in :fixture fixture should :status
   */
  public function allDefaultContentWithTypeAndBundleListedInFixtureShould($type, $bundle, $fixture, $status) {
    // Prepare data.
    $default_content_mod_path = drupal_get_path('module', 'dkan_default_content');

    // Build path for 'list' fixture file.
    $list_fixture = $default_content_mod_path . '/data/' . $fixture . '_list.json';

    // Load the list of content.
    $content_list = file_get_contents($list_fixture);
    $content_list = json_decode($content_list, TRUE);

    foreach ($content_list['result'] as $content_id) {

      // Build path for 'show' fixture file.
      $show_fixture = $default_content_mod_path . '/data/' . $fixture . '_show%3Fid=' . $content_id . '.json';

      // Load content data.
      $content_data = file_get_contents($show_fixture);
      $content_data = json_decode($content_data, TRUE);

      // Get content UUID. Some content like datasets export the UUID in the ID field.
      $content_uuid = (isset($content_data['result']['uuid'])) ? $content_data['result']['uuid'] : $content_data['result']['id'];

      // Try to load the content based on the UUID.
      $query = new EntityFieldQuery();
      $query->entityCondition('entity_type', $type)
        ->entityCondition('bundle', $bundle)
        ->propertyCondition('uuid', $content_uuid);
      $result = $query->execute();

      // Show error if needed.
      if (($status === 'be loaded') && empty($result)) {
        throw new \Exception(sprintf("The content with type '%s' and id '%s' could not be found", $type, $content));
      }

      if (($status === 'not be loaded') && !empty($result)) {
        throw new \Exception(sprintf("The content with type '%s' and id '%s' could be found", $type, $content));
      }
    }
  }

  /**
   * Check a table with the given class name exists in the page.
   *
   * @Given I should see a table with a class name :class_name
   *
   * @return \Behat\Mink\Element\NodeElement
   *
   * @throws \Exception
   */
  public function assertTableByClassName($class_name) {
    $page = $this->getSession()->getPage();
    $table = $page->findAll('css', 'table.' . $class_name);
    if (empty($table)) {
      throw new \Exception(sprintf('No table found on the page %s', $this->getSession()->getCurrentUrl()));
    }
    return array_pop($table);
  }

  /**
   * Check on the number of rows a table with the class name :class_name.
   *
   * @Then the table with the class name :class_name should have :number row(s)
   *
   * @throws \Exception
   */
  public function assertTableRowNumber($class_name, $number) {
    if (!is_numeric($number)) {
      throw new \Exception(sprintf('Expected "number" to be numeric'));
    }

    $table = $this->assertTableByClassName($class_name);
    $rows = $table->findAll('css', 'tr');

    // The first row is for the header. bump.
    array_pop($rows);

    if (count($rows) != $number) {
      throw new \Exception(sprintf('Found %s rows in the table with the class name %s of the expected %s.', count($rows), $class_name, $number));
    }
  }

  /**
   * Helper function to get current context.
   */
  public function getRegion($region) {
    $session = $this->getSession();
    $regionObj = $session->getPage()->find('region', $region);
    if (!$regionObj) {
      throw new \Exception(sprintf('No region "%s" found on the page %s.', $region, $session->getCurrentUrl()));
    }
    return $regionObj;
  }

  /**
   * @Given I should see :number items of :item in the :region region
   */
  public function iShouldSeeItemsOfInTheRegion($number, $item, $region) {
    $regionObj = $this->getRegion($region);
    // Count the number of items in the region.
    $count = count($regionObj->findAll('css', $item));
    if (!$count) {
      throw new \Exception(sprintf("No items found in the '%s' region.", $region));
    }
    else {
      if ($count != $number) {
        if ($count > $number) {
          throw new \Exception(sprintf("More than %s items were found in the '%s' region (%s).", $number, $region, $count));
        }
        else {
          throw new \Exception(sprintf("Less than %s items were found in the '%s' region (%s).", $number, $region, $count));
        }
      }
    }
  }

  /**
   * @Given I should see :number items of :item or more in the :region region
   */
  public function iShouldSeeItemsOfOrMoreInTheRegion($number, $item, $region) {
    $regionObj = $this->getRegion($region);
    // Count the number of items in the region.
    $count = count($regionObj->findAll('css', $item));
    if (!$count) {
      throw new \Exception(sprintf("No items found in the '%s' region.", $region));
    }
    else {
      if ($count < $number) {
        throw new \Exception(sprintf("Less than %s items were found in the '%s' region (%s).", $number, $region, $count));
      }
    }
  }

  /**
   * @Then I should see :arg1 field
   */
  public function iShouldSeeField($arg1) {
    $session = $this->getSession();
    $page = $session->getPage();
    $field = $page->findField($arg1);
    if (!isset($field)) {
      throw new \Exception(sprintf("Field with the text '%s' not found", $arg1));
    }
  }

  /**
   * @Then I should not see :arg1 field
   */
  public function iShouldNotSeeField($arg1) {
    $session = $this->getSession();
    $page = $session->getPage();
    $field = $page->findField($arg1);
    if ($field) {
      throw new \Exception(sprintf("Field with the text '%s' is found", $arg1));
    }
  }

  /**
   * @Then the text :text should be visible in the element :element
   */
  public function theTextShouldBeVisible($text, $selector) {
    $element = $this->getSession()->getPage();
    $nodes = $element->findAll('css', $selector . ":contains('" . $text . "')");
    foreach ($nodes as $node) {
      if ($node->isVisible() === TRUE) {
        return;
      }
      else {
        throw new \Exception("Form item \"$selector\" with label \"$text\" is not visible.");
      }
    }
    throw new \Exception("Form item \"$selector\" with label \"$text\" not found.");
  }

  /**
   * @Then the text :text should not be visible in the element :element
   */
  public function theTextShouldNotBeVisible($text, $selector) {
    $element = $this->getSession()->getPage();
    $nodes = $element->findAll('css', $selector . ":contains('" . $text . "')");
    foreach ($nodes as $node) {
      if ($node->isVisible() === FALSE) {
        return;
      }
      else {
        throw new \Exception("Form item \"$selector\" with label \"$text\" is visible.");
      }
    }
    throw new \Exception("Form item \"$selector\" with label \"$text\" not found.");
  }

  /**
   * @Then I visit the link :selector
   */
  public function iVisitTheLink($selector) {
    $region = $this->getRegion("content");
    $items = $region->findAll('css', $selector);
    if (empty($items)) {
      throw new \Exception("Link '$selector' not found on the page.");
    }
    $url = reset($items)->getAttribute('href');
    $session = $this->getSession();
    $session->visit($this->locatePath($url));
  }

  /**
   * @Given /^I fill in the autocomplete field "([^"]*)" with "([^"]*)"$/
   *
   * Fill in the 'Autocomplete' field on a form.
   */
  public function iFillInTheAutocompleteFieldWith($field, $value) {
    $session = $this->getSession();
    $page = $session->getPage();

    $element = $page->findField($field);
    if (!$element) {
      throw new ElementNotFoundException($session, NULL, 'named', $field);
    }
    $page->fillField($field, $value);

    // Trigger all needed key events in order for the autocomplete to be triggered.
    // Just filling the field with a value is not enough.
    // TODO: Is there a better way to do this?
    $chars = str_split($value);
    $last_char = array_pop($chars);
    // Delete last char.
    $session->getDriver()->keyDown($element->getXpath(), 8);
    $session->getDriver()->keyUp($element->getXpath(), 8);
    // Re-add last char.
    $session->getDriver()->keyDown($element->getXpath(), $last_char);
    $session->getDriver()->keyUp($element->getXpath(), $last_char);
    $this->iWaitForSeconds(5);

    $title = $page->find(
        'xpath',
        $session->getSelectorsHandler()->selectorToXpath('xpath', '//li[.="' . $value . '"]')
    );
    $title->click();
  }

}
