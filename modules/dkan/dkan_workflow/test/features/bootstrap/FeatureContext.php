<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\DrupalExtension\Context\DrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Mink\Selector\Xpath\Escaper;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  // Store pages to be referenced in an array.
  protected $pages = array();
  protected $groups = array();

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
    // Set the default timezone to NY
    date_default_timezone_set('America/New_York');
  }

  /******************************
   * HOOKS
   ******************************/

  /**
   * @AfterStep
   */
  public function debugStepsAfter(AfterStepScope $scope)
  {
    // Tests tagged with @debugEach will perform each step and wait for [ENTER] to proceed.
    if ($this->scenario->hasTag('debugEach')) {
      $env = $scope->getEnvironment();
      $drupalContext = $env->getContext('Drupal\DrupalExtension\Context\DrupalContext');
      $minkContext = $env->getContext('Drupal\DrupalExtension\Context\MinkContext');
      // Print the current URL.
      try {
        $minkContext->printCurrentUrl();
      }
      catch(Behat\Mink\Exception\DriverException $e) {
        print "No Url";
      }
      $drupalContext->iPutABreakpoint();
    }
  }

  /**
   * @BeforeStep
   */
  public function debugStepsBefore(BeforeStepScope $scope)
  {
    // Tests tagged with @debugBeforeEach will wait for [ENTER] before running each step.
    if ($this->scenario->hasTag('debugBeforeEach')) {
      $env = $scope->getEnvironment();
      $drupalContext = $env->getContext('Drupal\DrupalExtension\Context\DrupalContext');
      $drupalContext->iPutABreakpoint();
    }
  }

  /**
   * @BeforeScenario
   */
  public function registerScenario(BeforeScenarioScope $scope) {
    // Scenario not usually available to steps, so we do ourselves.
    // See issue
    $this->scenario = $scope->getScenario();
    //print  $this->scenario->getTitle();
  }

  /**
   * @BeforeScenario @mail
   */
  public function beforeMail()
  {
    // Store the original system to restore after the scenario.
    echo("Setting Testing Mail System\n");
    $this->originalMailSystem = variable_get('mail_system', array('default-system' => 'DefaultMailSystem'));
    // Set the test system.
    variable_set('mail_system', array('default-system' => 'TestingMailSystem'));
    // Flush the email buffer.
    variable_set('drupal_test_email_collector', array());
  }

  /**
   * @AfterScenario @mail
   */
  public function afterMail()
  {
    echo("Restoring Mail System\n");
    // Restore the default system.
    variable_set('mail_system', $this->originalMailSystem);
    // Flush the email buffer.
    variable_set('drupal_test_email_collector', array());
  }

  /****************************
   * HELPER FUNCTIONS
   ****************************/

  /**
   * Add page to context.
   *
   * @param $page
   */
  public function addPage($page) {
    $this->pages[$page['title']] = $page;
  }

  /**
   * Get Group by name
   *
   * @param $name
   * @return Group or FALSE
   */
  private function getGroupByName($name) {
    foreach($this->groups as $group) {
      if ($group->title == $name) {
        return $group;
      }
    }
    return FALSE;
  }

  /**
   * Get Group Role ID by name
   *
   * @param $name
   * @return Group Role ID or FALSE
   */
  private function getGroupRoleByName($name) {

    $group_roles = og_get_user_roles_name();

    return array_search($name, $group_roles);
  }

  /**
   * Get Membership Status Code by name
   *
   * @param $name
   * @return Membership status code or FALSE
   */
  private function getMembershipStatusByName($name) {
    switch($name) {
    case 'Active':
      return OG_STATE_ACTIVE;
      break;
    case 'Pending':
      return OG_STATE_PENDING;
      break;
    case 'Blocked':
      return OG_STATE_BLOCKED;
      break;
    default:
      break;
    }

    return FALSE;
  }

  /**
   * Explode a comma separated string in a standard way.
   *
   */
  function explode_list($string) {
    $array = explode(',', $string);
    $array = array_map('trim', $array);
    return is_array($array) ? $array : array();
  }

  /**
   * Get dataset nid by title from context.
   *
   * @param $nodeTitle title of the node.
   * @param $type type of nodo look for.
   *
   * @return Node ID or FALSE
   */
  private function getNidByTitle($nodeTitle, $type)
  {
    $context = array();
    switch($type) {
    case 'dataset':
      $context = $this->datasets;
      break;
    case 'resource':
      $context = $this->resources;
    }

    foreach($context as $key => $node) {
      if($node->title == $nodeTitle) {
        return $key;
      }
    }
    return FALSE;
  }

  /**
   * Update the node revision author with a db query.
   *
   * @param $node node id to be updated.
   * @param $vid revision id to be updated. Default to NULL to update the
   * latest revision.
   * @param $uid user id to use a revision author.
   */
  public function setNodeRevUid($nid, $uid, $vid = NULL) {
    if($vid == NULL){
      // Get the latest node vid
      $query = db_select('node_revision', 'nr')
        ->condition('nid', $nid, '=');

      $query = $query->extend('PagerDefault')->extend('TableSort');

      $result = $query->fields('nr', array('vid'))
        ->orderBy('vid', 'DESC')
        ->limit(1)
        ->execute();

      $resultAssoc = $result->fetchAssoc();
      if(!isset($resultAssoc['vid'])) {
        throw new Exception(sprintf("Failde to find latest node revision."));
      }

      db_update('node_revision')
        ->fields(array(
          'uid' => $uid,
          'log' => 'Updated from Behat'
        ))
        ->condition('nid', $nid, '=')
        ->condition('vid', $resultAssoc['vid'], '=')
        ->execute();

    } else {
      db_update('node_revision')
        ->fields(array(
          'uid' => $uid,
          'log' => 'Updated from Behat'
        ))
        ->condition('nid', $nid, '=')
        ->condition('vid', $vid, '=')
        ->execute();
    }
  }

  /**
   * Update the node revision author with a db query.
   *
   * @param $node node id to be updated.
   * @param $vid revision id to be updated. Default to NULL to update the
   * latest revision.
   * @param $uid user id to use a revision author.
   */
  public function setNodeRevUidAll($nid, $uid) {
    db_update('node_revision')
      ->fields(array(
        'uid' => $uid,
        'log' => 'Updated from Behat'
      ))
      ->condition('nid', $nid, '=')
      ->execute();
  }

  /*****************************
   * CUSTOM STEPS
   *****************************/

  /**
   * @Given pages:
   */
  public function addPages(TableNode $pagesTable) {
    foreach ($pagesTable as $pageHash) {
      // @todo Add some validation.
      $this->addPage($pageHash);
    }
  }

  /**
   * @Given I am on (the) :page page
   */
  public function iAmOnPage($page_title)
  {
    if (isset($this->pages[$page_title])) {
      $session = $this->getSession();
      $url = $this->pages[$page_title]['url'];
      $session->visit($this->locatePath($url));
      $code = $session->getStatusCode();

      if ($code < 200 || $code >= 300) {
        throw new Exception("Page $page_title ($url) visited, but it returned a non-2XX response code of $code.");
      }
    }
    else {
      throw new Exception("Page $page_title not found in the pages array, was it added?");
    }

  }

  /**
   * @When I search for :term
   */
  public function iSearchFor($term) {
    $session = $this->getSession();
    $search_form_id = '#dkan-sitewide-dataset-search-form--2';
    $search_form = $session->getPage()->findAll('css', $search_form_id);
    if (count($search_form) == 1) {
      $search_form = array_pop($search_form);
      $search_form->fillField("search", $term);
      $search_form->pressButton("edit-submit--2");
      $results = $session->getPage()->find("css", ".view-dkan-datasets");
      if (!isset($results)) {
        throw new Exception("Search results region not found on the page.");
      }
    }
    else if(count($search_form) > 1) {
      throw new Exception("More than one search form found on the page.");
    }
    else if(count($search_form) < 1) {
      throw new Exception("No search form with the id of found on the page.");
    }
  }

  /**
   * @Then I should see a dataset called :text
   *
   * @throws \Exception
   *   If region or text within it cannot be found.
   */
  public function iShouldSeeADatasetCalled($text)
  {
    $session = $this->getSession();
    $page = $session->getPage();
    $search_region = $page->find('css', '.view-dkan-datasets');
    $search_results = $search_region->findAll('css', '.views-row');

    $found = false;
    foreach( $search_results as $search_result ) {

      $title = $search_result->find('css', 'h2');

      if ($title->getText() === $text) {
        $found = true;
      }
    }

    if (!$found) {
      throw new \Exception(sprintf("The text '%s' was not found", $text));
    }
  }

  /**
   * @Given groups:
   */
  public function addGroups(TableNode $groupsTable)
  {
    // Map readable field names to drupal field names.
    $field_map = array(
      'author' => 'author',
      'title' => 'title',
      'published' => 'published'
    );

    foreach ($groupsTable as $groupHash) {
      $node = new stdClass();
      $node->type = 'group';
      foreach($groupHash as $field => $value) {
        if(isset($field_map[$field])) {
          $drupal_field = $field_map[$field];
          $node->$drupal_field = $value;
        }
        else {
          throw new Exception(sprintf("Group field %s doesn't exist, or hasn't been mapped. See FeatureContext::addGroups for mappings.", $field));
        }
      }
      $created_node = $this->getDriver()->createNode($node);

      // Add the created node to the groups array.
      $this->groups[$created_node->nid] = $created_node;

      // Add the url to the page array for easy navigation.
      $this->addPage(array(
        'title' => $created_node->title,
        'url' => '/node/' . $created_node->nid
      ));
    }
  }

  /**
   * Creates multiple group memberships.
   *
   * Provide group membership data in the following format:
   *
   * | user  | group     | role on group        | membership status |
   * | Foo   | The Group | administrator member | Active            |
   *
   * @Given group memberships:
   */
  public function addGroupMemberships(TableNode $groupMembershipsTable)
  {
    foreach ($groupMembershipsTable->getHash() as $groupMembershipHash) {

      if (isset($groupMembershipHash['group']) && isset($groupMembershipHash['user'])) {

        $group = $this->getGroupByName($groupMembershipHash['group']);
        $user = user_load_by_name($groupMembershipHash['user']);

        // Add user to group with the proper group permissions and status
        if ($group && $user) {

          // Add the user to the group
          og_group("node", $group->nid, array(
            "entity type" => "user",
            "entity" => $user,
            "membership type" => OG_MEMBERSHIP_TYPE_DEFAULT,
            "state" => $this->getMembershipStatusByName($groupMembershipHash['membership status'])
          ));

          // Grant user roles
          $group_role = $this->getGroupRoleByName($groupMembershipHash['role on group']);
          og_role_grant("node", $group->nid, $user->uid, $group_role);

        } else {
          if (!$group) {
            throw new Exception(sprintf("No group was found with name %s.", $groupMembershipHash['group']));
          }
          if (!$user) {
            throw new Exception(sprintf("No user was found with name %s.", $groupMembershipHash['user']));
          }
        }
      } else {
        throw new Exception(sprintf("The group and user information is required."));
      }
    }
  }

  /**
   * @Given datasets:
   */
  public function addDatasets(TableNode $datasetsTable)
  {
    global $user;

    // Map readable field names to drupal field names.
    $field_map = array(
      'author' => 'author',
      'title' => 'title',
      'author' => 'uid',
      'description' => 'body',
      'language' => 'language',
      'tags' => 'field_tags',
      'publisher' => 'og_group_ref',
      'moderation' => 'workbench_moderation',
      'date' => 'created',
    );

    // Default to draft moderation state.
    $workbench_moderation_state = 'draft';

    foreach ($datasetsTable as $datasetHash) {
      $node = new stdClass();

      // Defaults
      $node->type = 'dataset';
      $node->language = LANGUAGE_NONE;
      $node->is_new = TRUE;

      foreach($datasetHash as $key => $value) {
        if(!isset($field_map[$key])) {
          throw new Exception(sprintf("Dataset's field %s doesn't exist, or hasn't been mapped. See FeatureContext::addDatasets for mappings.", $key));
        } else if($key == 'author') {
          $author = user_load_by_name($value);
          if($author) {
            $user = $author;
            $drupal_field = $field_map[$key];
            $node->$drupal_field = $user->uid;
            $node->revision_uid = $user->uid;
            $node->log = "Updated from Behat addResources";
          }

        } else if($key == 'tags' || $key == 'publisher') {
          $value = $this->explode_list($value);
          $drupal_field = $field_map[$key];
          $node->$drupal_field = $value;

        } else if($key == 'moderation') {
          $workbench_moderation_state = $value;

        } else {
          // Defalut behavior, map stait to field map.
          $drupal_field = $field_map[$key];
          $node->$drupal_field = $value;
        }
      }

      $created_node = $this->getDriver()->createNode($node);

      // Make the node author as the revision author.
      // This is needed for workbench views filtering.
      $created_node->log = $created_node->uid;
      $created_node->revision_uid = $created_node->uid;
      db_update('node_revision')
        ->fields(array(
          'uid' => $created_node->uid,
        ))
        ->condition('nid', $created_node->nid, '=')
        ->execute();

      // Manage moderation state.
      // Requires this patch https://www.drupal.org/node/2393771
      workbench_moderation_moderate($created_node, $workbench_moderation_state, $created_node->uid);

      $user = user_load(0);
      // Add the created node to the datasets array.
      $this->datasets[$created_node->nid] = $created_node;

      // Add the url to the page array for easy navigation.
      $this->addPage(array(
        'title' => $created_node->title,
        'url' => '/node/' . $created_node->nid
      ));
    }
  }

  /**
   * @Given resources:
   */
  public function addResources(TableNode $resourcesTable)
  {
    global $user;
    // Map readable field names to drupal field names.
    $field_map = array(
      'title' => 'title',
      'description' => 'body',
      'author' => 'uid',
      'language' => 'language',
      'format' => 'field_format',
      'dataset' => 'field_dataset_ref',
      'date' => 'created',
      'moderation' => 'workbench_moderation',
    );

    // Default to draft moderation state.
    $workbench_moderation_state = 'draft';
    $dataset_ref = FALSE;

    foreach ($resourcesTable as $resourceHash) {
      $node_presave = new stdClass();

      // Defaults
      $node_presave->type = 'resource';
      $node_presave->language = LANGUAGE_NONE;
      $node_presave->is_new = TRUE;

      foreach($resourceHash as $key => $value) {
        $drupal_field = $field_map[$key];

        if(!isset($field_map[$key])) {
          throw new Exception(sprintf("Resource's field %s doesn't exist, or hasn't been mapped. See FeatureContext::addDatasets for mappings.", $key));

        } else if($key == 'author') {
          $author = user_load_by_name($value);
          if($author) {
            $user = $author;
            $drupal_field = $field_map[$key];
            $node_presave->$drupal_field = $user->uid;
            $node_presave->revision_uid = $user->uid;
            $node_presave->log = "Updated from Behat addResources";

          } else {
            throw new Exception(sprintf("Username" . $value . " not found."));
          }

        } elseif ($key == 'format') {
          $value = $this->explode_list($value);
          $node_presave->{$drupal_field} = $value;

        } elseif ($key == 'dataset') {
          if($nid = $this->getNidByTitle($value, 'dataset')) {
            $dataset_ref = $nid;
          } else {
            throw new Exception(sprintf("Dataset node not found."));
          }
        } else if($key == 'moderation') {
          // No need to define 'Draft' state as it is used as default.
          $workbench_moderation_state = $value;

        } else {
          // Default behavior.
          // PHP 5.4 supported notation.
          $node_presave->{$drupal_field} = $value;
        }
      }

      $node_created = $this->getDriver()->createNode($node_presave);

      if($dataset_ref) {
        $node_created->field_dataset_ref[$node_created->language][]['target_id'] = $dataset_ref;
        node_save($node_created);
        $this->setNodeRevUidAll($node_created->nid, $node_created->uid);
        $this->setNodeRevUid($dataset_ref, $node_created->uid);
      }

      // Manage moderation state.
      // Make the node author as the revision author.
      // This is needed for workbench views filtering.
      workbench_moderation_moderate($node_created, $workbench_moderation_state, $node_created->uid);

      $user = user_load(0);
      // Add the created node to the datasets array.
      $this->resources[$node_created->nid] = $node_created;

      // Add the url to the page array for easy navigation.
      $this->addPage(array(
        'title' => $node_created->title,
        'url' => '/node/' . $node_created->nid
      ));
    }
  }

  /**
   * @Then user :username should receive an email
   */
  public function userShouldReceiveAnEmail($username)
  {
    if($user = user_load_by_name($username)) {
      // We can't use variable_get() because $conf is only fetched once per
      // scenario.
      $variables = array_map('unserialize', db_query("SELECT name, value FROM {variable} WHERE name = 'drupal_test_email_collector'")->fetchAllKeyed());
      $this->activeEmail = FALSE;
      foreach ($variables['drupal_test_email_collector'] as $message) {
        if ($message['to'] == $user->mail) {
          $this->activeEmail = $message;
          return TRUE;
        }
      }
      throw new Exception(sprintf("No Email for " . $username . "found."));
    } else {
      throw new Exception(sprintf("User %s not found.", $username));
    }
  }

  /**
   * @Then user :username should receive an email containing :content
   */
  public function userShouldReceiveAnEmailContaining($username, $content)
  {
    if($user = user_load_by_name($username)) {
      // We can't use variable_get() because $conf is only fetched once per
      // scenario.
      $variables = array_map('unserialize', db_query("SELECT name, value FROM {variable} WHERE name = 'drupal_test_email_collector'")->fetchAllKeyed());
      $this->activeEmail = FALSE;
      foreach ($variables['drupal_test_email_collector'] as $message) {
        if ($message['to'] == $user->mail) {
          $this->activeEmail = $message;
          if (strpos($message['body'], $content) !== FALSE ||
            strpos($message['subject'], $content) !== FALSE) {
              return TRUE;
            }
          throw new \Exception('Did not find expected content in message body or subject.');
        }
      }
      throw new Exception(sprintf("No Email for " . $username . " found."));
    } else {
      throw new Exception(sprintf("User %s not found.", $username));
    }
  }

  /**
   * @Then the :emailAddress should recieve an email containing :content
   */
  public function theEmailAddressShouldRecieveAnEmailContaining($emailAddress, $content)
  {
    // We can't use variable_get() because $conf is only fetched once per
    // scenario.
    $variables = array_map('unserialize', db_query("SELECT name, value FROM {variable} WHERE name = 'drupal_test_email_collector'")->fetchAllKeyed());
    $this->activeEmail = FALSE;
    foreach ($variables['drupal_test_email_collector'] as $message) {
      if ($message['to'] == $emailAddress) {
        $this->activeEmail = $message;
        if (strpos($message['body'], $content) !== FALSE ||
          strpos($message['subject'], $content) !== FALSE) {
            return TRUE;
          }
        throw new \Exception('Did not find expected content in message body or subject.');
      }
    }
    throw new \Exception(sprintf('Did not find expected message to %s', $emailAddress));
  }

  /**
   * @Given I wait for :seconds seconds
   */
  public function iWaitForSeconds($seconds)
  {
    $session = $this->getSession();
    $session->wait($seconds * 1000);
  }

  /**
   * Properly check vbo checkboxes in workbench tree.
   *
   * @Given I check :title in the workbench tree
   */
  public function iCheckCheckboxInWorkbenchTree($title)
  {
    $session = $this->getSession();
    $page = $session->getPage();
    $workflow_list = $page->find('css', 'ul.views-workflow-list');
    $workflow_list_items = array();
    // Check if any workflow list is found.
    if(isset($workflow_list)) {
      $workflow_list_items = $workflow_list->findAll('css', '.item-content');
    }
    foreach($workflow_list_items as $workflow_list_item) {
      if($workflow_list_item->findLink($title) && $checkbox = $workflow_list_item->find('css' , '.vbo-select')) {
        $checkbox->click();
        $session->wait(2000);
        return;
      }
    }
    throw new Exception(sprintf($title . " not found in the current workbench tree."));
  }

  /**
   * Use individual moderation links in workbench tree.
   *
   * @Given I click :action for :title in the workbench tree
   */
  public function iClickActionForTitleWorkbenchTree($action, $title)
  {
    $session = $this->getSession();
    $page = $session->getPage();
    $workflow_list = $page->find('css', 'ul.views-workflow-list');
    $workflow_list_items = array();
    // Check if any workflow list is found.
    if(isset($workflow_list)) {
      $workflow_list_items = $workflow_list->findAll('css', '.item-content');
    }
    foreach($workflow_list_items as $workflow_list_item) {
      if($workflow_list_item->findLink($title) && $action = $workflow_list_item->findLink($action)) {
        $action->click();
        $session->wait(2000);
        return;
      }
    }
    throw new Exception(sprintf($title . " or ". $action . "not found in the current workbench tree."));
  }

  /**
   * Check items count in workbench tree.
   *
   * @Given the workbench tree should contain :count elements
   */
  public function WorkbenchTreeShouldContainCountElement($count)
  {
    $session = $this->getSession();
    $page = $session->getPage();
    $workflow_list = $page->find('css', 'ul.views-workflow-list');
    $workflow_list_items = array();
    // Check if any workflow list is found.
    if(isset($workflow_list)) {
      $workflow_list_items = $workflow_list->findAll('css', '.item-content');
    }
    if(count($workflow_list_items) != $count) {
      throw new Exception(sprintf("Workbench tree element count is different then count provided!"));
    }
  }

  /**
   * Use VBO Bulk action to change the moderation state on the current workbench
   * tree page.
   *
   * @Given I set all the elements in the workbench tree to :state
   */
  public function WorkbenchTreeSetAll($state)
  {
    $session = $this->getSession();
    $page = $session->getPage();
    $vbo_select = $page->find('css', 'input.vbo-select-this-page');
    $escaper = new Escaper();
    $state_escaped = $escaper->escapeLiteral($state);
    $vbo_action = $page->find('named', array('link_or_button', $state_escaped));
    if($vbo_select && $vbo_action) {
      $vbo_select->click();
      $vbo_action->click();
      return;
    }
    throw new Exception(sprintf("Workbench tree bulk update button(s) not found!"));
  }

  /**
   * Check the moderation state for a node of a certain type.
   *
   * @Then the moderation state of node :title of type :type should be :state
   */
  public function moderationStateShouldBe($title, $type, $state)
  {
    $states = array(
      'Draft' => 'draft',
      'Needs review' => 'needs_review',
      'Published' => 'published',
    );

    $types = array(
      'Dataset' => 'dataset',
      'Resource' => 'resource',
    );

    if (!$nid = $this->getNidByTitle($title, $types[$type])){
      throw new Exception(sprintf("Node with title " . $title . " and of type "
        . $type . " not found."));
    } else {
      $node = node_load($nid);
      $node_current = workbench_moderation_node_current_load($node);
      $state_current = $node_current->workbench_moderation['current']->state;
      if ($state_current != $states[$state]){
        throw new Exception(sprintf("Node with title " . $title . " and of type
          " . $type . " does not have " . $state . " moderation state."));
      }
    }
  }
}
