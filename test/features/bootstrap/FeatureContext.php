<?php

use Behat\Behat\Context\Context;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Devinci\DevinciExtension\Context\JavascriptContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use WebDriver\Key;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext
{

  protected $jsContext;

  /**
   * @Given /^I scroll to the top$/
   */
  public function iScrollToTheTop() {
    $driver = $this->getSession()->getDriver();
    // Wait two seconds for admin menu if using js.
    if ($driver instanceof Selenium2Driver) {
      $element = $driver.findElement(By.id("header"));
      $actions = new Actions($driver);
      $actions.moveToElement($element);
      // actions.click();
      $actions.perform();
    }
  }

  /**
   * @When /^I switch to the frame "([^"]*)"$/
   */
  public function iSwitchToTheFrame($frame) {
    $this->getSession()->switchToIFrame($frame);
  }

  /**
   * @Then /^I should see the "([^"]*)" element in the "([^"]*)" region$/
   */
  public function assertRegionElement($tag, $region) {
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
  public function iSwitchOutOfAllFrames() {
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
   * @Then the Dataset search updates behind the scenes
   */
  public function theDatasetSearchUpdatesBehindTheScenes()
  {
    $index = search_api_index_load('datasets');
    $items =  search_api_get_items_to_index($index);
    search_api_index_specific_items($index, $items);
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
   * Properly inputs item in field rendered by Chosen.js.
   *
   *
   * @Given /^I fill in the chosen field "([^"]*)" with "([^"]*)"$/
   */
  public function iFillInTheChosenFieldWith($field, $value) {
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
    if(!isset($title)){
      throw new Exception(sprintf('"' . $value . '" option was not found in the chosen field.'));
    }
    $title->click();
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
    $this->iWaitForSeconds(1);
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
  public function iClickMapIcon($num) {
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
  public function iFillInTheAutoFieldWith($field, $value) {
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
  public function iEmptyTheField($locator) {
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
   * Wait for the given number of seconds. ONLY USE FOR DEBUGGING!
   *
   * @Given I wait for :time second(s)
   */
  public function iWaitForSeconds($time) {
    sleep($time);
  }

  /**
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $environment = $scope->getEnvironment();

    $this->jsContext = $environment->getContext('Devinci\DevinciExtension\Context\JavascriptContext');
  }


  /**
   * Wait to click on something in case it does not appear immediately (javascript)
   *
   * @Given I wait and press :text
   */
  public function iWaitAndPress($text) {
    $wait = $this->jsContext->maximum_wait;
    try {
      $found = $this->jsContext->spin(function ($context) use ($text) {
        $context->getSession()->getPage()->pressButton($text);
        return (TRUE);
      }, $wait);
      return $found;
    }
    catch(\Exception $e) {
      throw new \Exception( "Couldn't find button $text within $wait seconds");
    }
  }


  /**
   * @When I attach the drupal file :arg1 to :arg2
   *
   * Overrides attachFileToField() in Mink context to fix but with relative
   * path.
   */
  public function iAttachTheDrupalFileTo($path, $field)
  {
    $field = $this->fixStepArgument($field);

    // Relative paths stopped working after selenium 2.44.
    $offset = 'features/bootstrap/FeatureContext.php';
    $dir =  __file__;
    $test_dir = str_replace($offset, "", $dir);
    $path = $this->getMinkParameter('files_path') . '/' . $path;
    $this->getSession()->getPage()->attachFileToField($field, $path);
  }

  /**
   * Check toolbar if this->user isn't working.
   */
  public function getCurrentUser() {
    if ($this->user) {
      return $this->user;
    }
    $session = $this->getSession();
    $page = $session->getPage();
    $xpath = $page->find('xpath', "//div[@class='content']/span[@class='links']/a[1]");
    $userName = $xpath->getText();
    $uid = db_query('SELECT uid FROM users WHERE name = :user_name', array(':user_name' =>  $userName))->fetchField();
    if ($uid && $user = user_load($uid)) {
      return $user;
    }
    return FALSE;
  }

  /************************************/
  /* DATA DASHBOARDS                  */
  /************************************/

  /**
   * @Given Data Dashboard:
   */
  public function addDataDashboard(TableNode $data_dashboards_table) {

    // Map readable field names to drupal field names.
    $field_map = array(
      'title' => 'title',
    );

    foreach ($data_dashboards_table->getHash() as $data_dashboards_hash) {

      $node = new stdClass();
      $node->type = 'data_dashboard';

      foreach($data_dashboards_hash as $field => $value) {

        if(isset($field_map[$field])) {
          $drupal_field = $field_map[$field];
          $node->$drupal_field = $value;
        }
        else {
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
   *
   * @AfterScenario
   */
  public function afterScenario() {
    if (!empty($this->data_dashboards)) {
      foreach ($this->data_dashboards as $data_dashboard_id => $data_dashboard) {
        $this->getDriver()->nodeDelete($data_dashboard);
      }
    }
  }

  /**
   * Returns fixed step argument (with \\" replaced back to ").
   *
   * @param string $argument
   *
   * @return string
   */
  public function fixStepArgument($argument)
  {
    return str_replace('\\"', '"', $argument);
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

  /************************************/
  /* Gravatar                         */
  /************************************/

//  /**
//   * @Then /^I should see a gravatar link in the "([^"]*)" region$/
//   */
//  public function iShouldSeeAGravatarLinkInTheRegion($region)
//  {
////   $regionObj = $this->getMainContext()->getRegion($region);
////    $elements = $regionObj->findAll('css', 'img');
////    if (!empty($elements)) {
////      foreach ($elements as $element) {
////        if ($element->hasAttribute('src')) {
////          $value = $element->getAttribute('src');
////          //if (preg_match('/\/\/www\.gravatar\.com\/avatar\/.*/', $value)) {
////            return;
////          }
////        }
////      }
////    }
////    throw new \Exception(sprintf('The element gravatar link was not found in the "%s" region on the page %s', $region, $this->getSession()->getCurrentUrl()));
//
//  }
//
//  /**
//   * @Then /^I should not see a gravatar link in the "([^"]*)" region$/
//   */
//  public function iShouldNotSeeAGravatarLinkInTheRegion($region)
//  {
////    $regionObj = $this->getMainContext()->getRegion($region);
////    $elements = $regionObj->findAll('css', 'img');
////    $match = FALSE;
////    if (!empty($elements)) {
////      foreach ($elements as $element) {
////        if ($element->hasAttribute('src')) {
////          $value = $element->getAttribute('src');
////          if (preg_match('/\/\/www\.gravatar\.com\/avatar\/.*/', $value)) {
////            $match = TRUE;
////          }
////        }
////      }
////    }
////    if ($match) {
////      throw new \Exception(sprintf('The element gravatar link was found in the "%s" region on the page %s', $region, $this->getSession()->getCurrentUrl()));
////    }
////    else {
////      return;
////    }
//  }

  /**
   * @Given :provider previews are :setting for :format_name resources
   *
   * Changes variables in the database to enable or disable external previews
   */
  public function externalPreviewsAreEnabledForFormat($provider, $setting, $format_name)
  {
    $format = current(taxonomy_get_term_by_name($format_name, 'format'));
    $preview_settings = variable_get("dkan_dataset_format_previews_tid{$format->tid}", array());
    // If $setting was "enabled," the preview is turned on. Otherwise, it's
    // turned off.
    $preview_settings[$provider] = ($setting == 'enabled') ? $provider : 0;
    variable_set("dkan_dataset_format_previews_tid{$format->tid}", $preview_settings);
  }

  /**
   * @Then I should see the local preview link
   */
  public function iShouldSeeTheLocalPreviewLink()
  {
      $this->assertSession()->pageTextContains(variable_get('dkan_dataset_teaser_preview_label', '') . ' ' . t('Preview'));
  }

  /**
   * @Given I should see the first :number dataset items in :orderby :sortdirection order.
   */
  public function iShouldSeeTheFirstDatasetListInOrder($number, $orderby, $sortdirection){
    $number = (int) $number;
    // Search the list of datasets actually on the page (up to $number items)
    $dataset_list = array();
    $count = 0;
    while(($count < $number ) && ($row = $this->getSession()->getPage()->find('css', '.views-row-'.($count+1))) !== null ){
      $row = $row->find('css', 'h2');
      $dataset_list[] = $row->getText();
      $count++;
    }

    if ($count !== $number) {
      throw new Exception("Couldn't find $number datasets on the page. Found $count.");
    }

    switch($orderby){
      case 'Date changed':
        $orderby = 'changed';
        break;
      case 'Title':
        $orderby = 'title';
        break;
      default:
        throw new Exception("Ordering by '$orderby' is not supported by this step.");
    }

    $index = search_api_index_load('datasets');
    $query = new SearchApiQuery($index);

    $results = $query->condition('type', 'dataset')
      ->condition('status', '1')
      ->sort($orderby, strtoupper($sortdirection))
      ->range(0, $number)
      ->execute();
    $count = count($results['results']);
    if (count($results['results']) !== $number) {
      throw new Exception("Couldn't find $number datasets in the database. Found $count.");
    }

    foreach($results['results'] as $nid => $result) {
      $dataset = node_load($nid);
      $found_title = array_shift($dataset_list);
      if ($found_title !== $dataset->title) {
        throw new Exception("Does not match order of list, $found_title was next on page but expected $dataset->title");
      }
    }
  }

  /**
   * @Then I should see the list of permissions for :role role
   */
  public function iShouldSeePermissionsForRole($role)
  {

    $role_names = og_get_user_roles_name();
    if ($rid = array_search($role, $role_names)) {
      $permissions = og_role_permissions(array($rid => ''));
      foreach(reset($permissions) as $machine_name => $perm) {
        // Currently the permissions returned by og for a role are only the machine name and its true value,
        // need to find a way to find the checkbox of a permission and see if it is checked
        $search = "edit-".$rid."-".strtr($machine_name, " ", "-");
        if(!$this->getSession()->getPage()->hasCheckedField($search)){
          throw new \Exception("Permission $machine_name is not set for $role.");
        }
      }
    }
  }

  /**
   * @Then I should get :format content from the :button button
   */
  public function assertButtonReturnsFormat($format, $button){

    if($button === "JSON"){
      $button = "json view of content";
    }

    $content = $this->getSession()->getPage()->findLink($button);
    try {
      $file = file_get_contents($content->getAttribute("href"));
    }catch(Exception $e){
      throw $e;
    }
    if($format === "JSON") {
      json_decode($file);
      if (!json_last_error() == JSON_ERROR_NONE) {
        throw new Exception("Not JSON format.");
      }
    }
  }

  /**
   * @Then I should see the redirect button for :site
   */
  public function assertRedirectButton($site){
    $page = $this->getSession()->getPage();

    switch($site){
      case 'Google+':
        $element = $page->find('css', '.fa-google-plus-square');
        $link = $element->getParent()->getAttribute("href");
        $return = preg_match("#https:\/\/plus\.google\.com\/share\?url=.*dataset\/.*#", $link);
        break;
      case 'Twitter':
        $element = $page->find('css', '.fa-twitter-square');
        $link = $element->getParent()->getAttribute("href");
        $return = preg_match("#https:\/\/twitter\.com\/share\?url=.*dataset\/.*#", $link);
        break;
      case 'Facebook':
        $element = $page->find('css', '.fa-facebook-square');
        $link = $element->getParent()->getAttribute("href");
        $return = preg_match("#https:\/\/www\.facebook\.com\/sharer\.php.*dataset\/.*#", $link);
        break;
      default:
        throw new Exception("Not a valid site for DKAN sharing.");
    }

    if(!$return){
      throw new Exception("The $site redirect button is not properly configured.");
    }
  }
}
