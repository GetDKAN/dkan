<?php

use Drupal\DrupalExtension\Context\DrupalContext;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Context\Step\Given;
use Behat\Behat\Context\Step\Then;
use Symfony\Component\Process\Process;
use Behat\Gherkin\Node\TableNode;

require 'vendor/autoload.php';

class FeatureContext extends DrupalContext
{
  // Keep track of created data dashboards so they can be cleaned up.
  protected $data_dashboards = array();

  /**
   * @Given /^I scroll to the top$/
   */
  public function iScrollToTheTop()
  {
    $driver = $this->getSession()->getDriver();
    // Wait two seconds for admin menu if using js.
    if ($driver instanceof Selenium2Driver) {
      $element = $driver . findElement(By . id("header"));
      $actions = new Actions($driver);
      $actions . moveToElement($element);
      // actions.click();
      $actions . perform();
    }
  }

  /**
   * @When /^I switch to the frame "([^"]*)"$/
   */
  public function iSwitchToTheFrame($frame)
  {
    $this->getSession()->switchToIFrame($frame);
  }

  /**
   * @Then /^I should see the "([^"]*)" element in the "([^"]*)" region$/
   */
  public function assertRegionElement($tag, $region)
  {
    $regionObj = $this->getMainContext()->getRegion($region);
    $elements = $regionObj->findAll('css', $tag);
    if (!empty($elements)) {
      return;
    }
    throw new \Exception(sprintf('The element "%s" was not found in the "%s" region on the page %s', $tag, $region, $this->getSession()->getCurrentUrl()));
  }

  /**
   * @Given /^I switch out of all frames$/
   */
  public function iSwitchOutOfAllFrames()
  {
    $this->getSession()->switchToIFrame();
  }

  /**
   * @Then /^I wait for the dialog box to appear$/
   */
  public function iWaitForTheDialogBoxToAppear()
  {
    $this->getSession()->wait(2000, "jQuery('#user-login-dialog').children().length > 0");
  }

  /**
   * Selects option in select field with specified by node title.
   *
   * @When /^(?:|I )select node named "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)"$/
   */
  public function selectNodeOption($select, $option)
  {
    $this->assertDrushCommandWithArgument('php-eval', "\"return db_query('SELECT nid FROM node WHERE title = \'$option\'')->fetchField();\"");
    $option = $this->readDrushOutput();
    $option = trim(str_replace(array("'"), "", $option));
    $select = $this->fixStepArgument($select);
    $option = $this->fixStepArgument($option);
    $this->getSession()->getPage()->selectFieldOption($select, $option);
  }

  /**
   * @Given /^I am a "([^"]*)" of the group "([^"]*)"$/
   */
  public function iAmAMemberOfTheGroup($role, $group_name)
  {
    $this->assertDrushCommandWithArgument('php-eval', "\"return db_query('SELECT nid FROM node WHERE title = \'$group_name\'')->fetchField();\"");
    $option = $this->readDrushOutput();
    $gid = trim(str_replace(array("'"), "", $option));
    $user = $this->user;
    $this->assertDrushCommandWithArgument('php-eval', "\"return db_query('SELECT uid FROM users WHERE name = \'$user->name\'')->fetchField();\"");
    $option = $this->readDrushOutput();
    $user_id = trim(str_replace(array("'"), "", $option));
    $this->assertDrushCommandWithArgument('php-eval', "\"return db_query('SELECT rid FROM og_role WHERE name = \'$role\'')->fetchField();\"");
    $option = $this->readDrushOutput();
    $rid = trim(str_replace(array("'"), "", $option));
    $this->assertDrushCommandWithArgument('og-add-user', "node $gid $rid $user_id");
  }

  /**
   * Properly inputs item in field rendered by Chosen.js.
   *
   *
   * @Given /^I fill in the chosen field "([^"]*)" with "([^"]*)"$/
   */
  public function iFillInTheChosenFieldWith($field, $value)
  {
    $session = $this->getSession();
    $page = $session->getPage();
    $xpath = $page->find('xpath', '//input[@value="' . $field . '"]');
    $field = $this->fixStepArgument($field);
    $value = $this->fixStepArgument($value);
    // Focus means autocoplete will actually show up.
    $this->getSession()->getDriver()->focus('//input[@value="' . $field . '"]');
    //$page->fillField($field, $value);
    $this->iWaitForSeconds(1);
    // Selects the first dropdown since there is no id or other way to
    // reference the desired entry.
    $title = $session->getPage()->find(
      'xpath',
      $session->getSelectorsHandler()->selectorToXpath('xpath', '//li[.="' . $value . '"]')

    );
    $title->click();
  }

  /**
   * @Given /^I click the chosen field "([^"]*)" and enter "([^"]*)"$/
   */
  public function iClickTheChosenFieldAndEnter($field, $value)
  {
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
    $this->iWaitForSeconds(1);
    // Click value that now appears.
    $title = $session->getPage()->find(
      'xpath',
      $session->getSelectorsHandler()->selectorToXpath('xpath', '//li[.="' . $value . '"]')

    );
    $title->click();
  }


  /**
   * Click some text.
   *
   * @When /^I click on the text "([^"]*)"$/
   */
  public function iClickOnTheText($text)
  {
    $session = $this->getSession();
    $element = $session->getPage()->find(
      'xpath',
      $session->getSelectorsHandler()->selectorToXpath('xpath',
        '//*[contains(text(), "' . $text . '")]')
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
  public function iClickMapIcon($num)
  {
    $session = $this->getSession();
    $element = $session->getPage()->find(
      'xpath',
      $session->getSelectorsHandler()->selectorToXpath(
        'xpath',
        '//div[contains(@class, "leaflet-marker-pane")]//img[' . $num . ']'
      )
    );
    if (null === $element) {
      throw new \InvalidArgumentException(sprintf('Cannot find map icon: "%s"', $num));
    }
    $element->click();
  }

  /**
   * Copy of "I fill in" but doesn't escape "(".
   *
   * When using "I fill in" it escaped autocomplete fields node id. Just using
   * the title wouldn't work. The following focuses on the field and selects
   * the first dropdown.
   *
   * @Given /^I fill in the autocomplete field "([^"]*)" with "([^"]*)"$/
   */
  public function iFillInTheAutoFieldWith($field, $value)
  {
    $session = $this->getSession();
    $field = $this->fixStepArgument($field);
    $value = $this->fixStepArgument($value);
    $input_title = $session->getPage()->find(
      'xpath',
      $session->getSelectorsHandler()->selectorToXpath('xpath', '//input[@value="' . $field . '"]')

    );
    $input_title->click();
    $this->iWaitForSeconds(2);
    // Selects the first dropdown since there is no id or other way to
    // reference the desired entry.
    $title = $session->getPage()->find(
      'xpath',
      $session->getSelectorsHandler()->selectorToXpath('xpath', '//li[.="' . $value . '"]')

    );
    $title->click();
  }

  /**
   * @Given /^I empty the field "([^"]*)"$/
   */
  public function iEmptyTheField($locator)
  {
    $session = $this->getSession();
    $page = $session->getPage();
    $field = $page->findField($locator);

    if (null === $field) {
      throw new ElementNotFoundException(
        $this->getSession(), 'form field', 'id|name|label|value', $locator
      );
    }

    $field->setValue("");
  }

  /**
   * Determine if the a user is already logged in.
   */
  public function loggedIn()
  {
    $session = $this->getSession();
    $session->visit($this->locatePath('/'));
    $driver = $this->getSession()->getDriver();
    // Wait two seconds for admin menu if using js.
    if ($driver instanceof Selenium2Driver) {
      $session->wait(2000);
    }
    // If a logout link is found, we are logged in. While not perfect, this is
    // how Drupal SimpleTests currently work as well.
    $element = $session->getPage();
    return $element->findLink($this->getDrupalText('log_out'));
  }

  /**
   * @Given /^groups memberships:$/
   */
  public function groupsMemberships(TableNode $table)
  {
    $memberships = $table->getHash();
    foreach ($memberships as $membership) {
      // Find group node.
      $group_node = $membership['group'];
      foreach ($this->nodes as $node) {
        if ($node->type == 'group' && $node->title == $group_node) {
          $group_node = $node;
        }
      }

      // Subscribe nodes and users to group.
      if (isset($membership['members'])) {
        $members = explode(",", $membership['members']);
        foreach ($this->users as $user) {
          if (in_array($user->name, $members)) {
            og_group(
              'node',
              $group_node->nid,
              array(
                'entity' => $user,
                'entity_type' => 'user',
                "membership type" => OG_MEMBERSHIP_TYPE_DEFAULT,
              )
            );
            // Patch till i figure out why rules are not firing.
            if ($user->name == 'editor') {
              og_role_grant('node', $group_node->nid, $user->uid, 4);
            }
          }
        }
      }

      if (isset($membership['nodes'])) {
        $content = explode(",", $membership['nodes']);
        foreach ($this->nodes as $node) {
          if ($node->type != 'group' && in_array($node->title, $content)) {
            og_group(
              'node',
              $group_node->nid,
              array(
                'entity' => $node,
                'entity_type' => 'node',
                'state' => OG_STATE_ACTIVE,
              )
            );
          }
        }
      }
    }
  }

  /**
   * @Given /^I wait for "([^"]*)" seconds$/
   */
  public function iWaitForSeconds($seconds)
  {
    $session = $this->getSession();
    $session->wait($seconds * 1000);
  }

  /**
   * @Then /^I should see the administration menu$/
   */
  public function iShouldSeeTheAdministrationMenu()
  {
    $xpath = "//div[@id='admin-menu']";
    // grab the element
    $element = $this->getXPathElement($xpath);
  }

  /**
   * @Then /^I should have an "([^"]*)" text format option$/
   */
  public function iShouldHaveAnTextFormatOption($option)
  {
    $xpath = "//select[@name='body[und][0][format]']//option[@value='" . $option . "']";
    // grab the element
    $element = $this->getXPathElement($xpath);
  }

  /**
   * Returns an element from an xpath string
   * @param  string $xpath
   *   String representing the xpath
   * @return object
   *   A mink html element
   */
  protected function getXPathElement($xpath)
  {
    // get the mink session
    $session = $this->getSession();
    // runs the actual query and returns the element
    $element = $session->getPage()->find(
      'xpath',
      $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
    );
    // errors must not pass silently
    if (null === $element) {
      throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));
    }
    return $element;
  }

  /**
   * Take screenshot when step fails.
   * Works only with Selenium2Driver.
   *
   * @AfterStep
   */
  public function takeScreenshotAfterStep($event)
  {
    if (4 === $event->getResult()) {
      $driver = $this->getSession()->getDriver();
      if (!($driver instanceof Selenium2Driver)) {
        // throw new UnsupportedDriverActionException('Taking screenshots is not supported by %s, use Selenium2Driver instead.', $driver);
        return;
      }
      $screenshot = $driver->getScreenshot();
      $file = 'screens/' . time() . ' ' . $event->getLogicalParent()->getTitle();
      $file = $file . '.png';
      file_put_contents($file, $screenshot);
    }
  }

  /************************************/
  /* DATA DASHBOARDS                  */
  /************************************/

  /**
   * @Given data_dashboards:
   */
  public function addDataDashboard(TableNode $data_dashboards_table)
  {

    // Map readable field names to drupal field names.
    $field_map = array(
      'title' => 'title',
    );

    foreach ($data_dashboards_table->getHash() as $data_dashboards_hash) {

      $node = new stdClass();
      $node->type = 'data_dashboard';

      foreach ($data_dashboards_hash as $field => $value) {

        if (isset($field_map[$field])) {
          $drupal_field = $field_map[$field];
          $node->$drupal_field = $value;
        } else {
          throw new Exception(sprintf("Data Dashboard field %s doesn't exist, or hasn't been mapped. See FeatureContext::addDataDashboard for mappings.", $field));
        }
      }
      $created_node = $this->getDriver()->createNode($node);
      // Add the created node to the data dashboards array.
      $this->data_dashboards[$created_node->nid] = $created_node;
    }
  }

   /**
   * @Given /^I add a Dataset Filtered List$/
   */
  public function iAddADatasetFilteredList() {
    $add_button = $this->getXPathElement("//fieldset[@class='widget-preview panel panel-default'][3]//a");
    $add_button->click();
  }

  /**
   * Clean up generated content.
   */
  public function afterScenario($event)
  {

    parent::afterScenario($event);

    if (!empty($this->data_dashboards)) {
      foreach ($this->data_dashboards as $data_dashboard_id => $data_dashboard) {
        $this->getDriver()->nodeDelete($data_dashboard);
      }
    }
  }

  /**
   * @Then /^the administrator role should have all permissions$/
   */
  public function theAdministratorRoleShouldHaveAllPermissions() {
    // Get list of all permissions
    $permissions = array();
    foreach (module_list(FALSE, FALSE, TRUE) as $module) {
      // Drupal 7
      if (module_invoke($module, 'permission')) {
        $permissions = array_merge($permissions, array_keys(module_invoke($module, 'permission')));
      }
    }
    $administrator_role = user_role_load_by_name('administrator');
    $administrator_perms = db_query("SELECT permission FROM {role_permission} WHERE rid = :admin_rid", array(':admin_rid' => $administrator_role->rid))
        ->fetchCol();
    foreach($permissions as $perm) {
      if (!in_array($perm, $administrator_perms)) {
        echo $perm;
        throw new Exception(sprintf("Administrator role missing permission %s", $perm));
      }
    }
  }
}