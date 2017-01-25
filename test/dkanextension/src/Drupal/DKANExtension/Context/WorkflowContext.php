<?php
namespace Drupal\DKANExtension\Context;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Drupal\DKANExtension\Hook\Scope\BeforeDKANEntityCreateScope;

use \stdClass;

/**
 * Defines application features from the specific context.
 */
class WorkflowContext extends RawDKANContext {

  protected $old_global_user;
  public static $modules_before_feature = array();
  public static $users_before_feature = array();

  /**
   * @BeforeFeature @enableDKAN_Workflow
   */
  public static function enableDKAN_Workflow(BeforeFeatureScope $scope)
  {
    self::$modules_before_feature = module_list(TRUE);
    self::$users_before_feature = array_keys(entity_load('user'));

    @module_enable(array(
      'dkan_workflow'
    ));
  }

  /**
   * @AfterFeature @enableDKAN_Workflow
   */
  public static function disableDKAN_Workflow(AfterFeatureScope $event)
  {
    $modules_after_feature = module_list(TRUE);
    $users_after_feature = array_keys(entity_load('user'));

    $modules_to_disable = array_diff_assoc(
      $modules_after_feature,
      self::$modules_before_feature
    );

    $users_to_delete = array_diff_assoc(
      $users_after_feature,
      self::$users_before_feature
    );

    // Clean users and disable modules.
    entity_delete_multiple('user', $users_to_delete);
    module_disable(array_values($modules_to_disable));
  }

  /**
   * @Given I update the moderation state of :named_entity to :state
   * @Given I update the moderation state of :named_entity to :state on date :date
   * @Given :workflow_user updates the moderation state of :named_entity to :state
   * @Given :workflow_user updates the moderation state of :named_entity to :state on date :date
   *
   * Transition a Moderated Node from one state to another.
   *
   * @param String|null $user The string of the username.
   * @param String $named_entity A named entity stored in the entity store.
   * @param String $state The state that you want to transition to.
   * @param String|null $date A valid php datetime string. Supports relative dates.
   * @throws \Exception
   */
  public function transitionModerationState($workflow_user=null, $named_entity, $state, $date = null) {
    global $user;

    // Save the original user to set it back later
    $global_user = $user;

    $node = $this->getModerationNode($named_entity);

    $possible_states = workbench_moderation_state_labels();
    $state_key = array_search($state, $possible_states);
    if (!$state_key) {
      $possible_states = implode(", ", $possible_states);
      throw new \Exception("State '$state' is not available. All possible states are [$possible_states].");
    }

    $current_user = ($workflow_user) ? user_load_by_name($workflow_user) : $this->getCurrentUser();
    if (!$current_user) {
      throw new \Exception("No user is logged in.");
    }

    $my_revision = $node->workbench_moderation['my_revision'];;
    $state_machine_name = array_search($state, $possible_states);

    // If node is moderated to the same state but with different time, then the moderation isn't performed but the time is updated.
    if($my_revision->state != $state_machine_name) {
      $next_states = workbench_moderation_states_next($my_revision->state, $current_user, $node);
      if (empty($next_states)) {
        $next_states = array();
      }
      if (!isset($next_states[$state_key])) {
        $next_states = implode(", ", $next_states);
        throw new \Exception("State '$possible_states[$state_key]' is not available to transition to. Transitions available to user '$current_user->name' are [$next_states]");
      }

      // Change global user to the current user in order to allow
      // workflow moderation to get the right user.
      $user = $current_user;

      // This function actually updates the transition.
      workbench_moderation_moderate($node, $state_key);

      // the workbench_moderation_moderate defer some status updates on the
      // node (currently the "Publish" status) to the process shutdown. Which
      // does not work well on Behat since scenarios are run on a single drupal
      // bootstrap.
      // To work around this setup. After calling the
      // `workbench_moderation_moderate` callback we check if a call to the
      // `workbench_moderation_store` function is part of the shutdown
      // execution and run it.
      $callbacks = &drupal_register_shutdown_function();
      while (list($key, $callback) = each($callbacks)) {
        if ($callback['callback'] == 'workbench_moderation_store') {
          call_user_func_array($callback['callback'], $callback['arguments']);
          unset($callbacks[$key]);
        }
      }

      // Back global user to the original user. Probably an anonymous.
      $user = $global_user;
    }

    // If a specific date is requested, then updated it after the fact.
    if (isset($date)) {
      $timestamp = strtotime($date, REQUEST_TIME);
      if (!$timestamp) {
        throw new \Exception("Error creating datetime from string '$date'");
      }

      db_update('workbench_moderation_node_history')
        ->fields(array(
          'stamp' => $timestamp,
        ))
        ->condition('nid', $node->nid, '=')
        ->condition('vid', $node->vid, '=')
        ->execute();
    }

  }

  /**
   * @Then the moderation state of :name should be :state
   *
   * Assert the moderation state of a named entity.
   *
   * @param String $name A named entity (title)
   * @param String $state The moderation state the node should be currently at.
   * @throws \Exception
   */
  public function assertModerationState($name, $state) {

    $possible_states = workbench_moderation_state_labels();
    $state_key = array_search($state, $possible_states);
    if (!$state_key) {
      $possible_states = implode(", ", $possible_states);
      throw new \Exception("State '$state' is not available. All possible states are [$possible_states].");
    }

    $current_state_key = $this->getModerationState($name);
    if ($current_state_key !== $state_key) {
      throw new \Exception("State is not '$state', but instead it's $possible_states[$current_state_key].");
    }
  }

  /**
   * Get the current moderation state of a named node.
   *
   * @param String $name A named entity in the entity store.
   * @return String state_key
   * @throws \Exception
   */
  public function getModerationState($name) {
    $node = $this->getModerationNode($name);
    $my_revision = $node->workbench_moderation['my_revision'];
    return $my_revision->$my_revision->state;
  }

  /**
   * Grab a named node from the entity store and add moderation fields to it.
   *
   * @param String $name A named entity in the entity store.
   * @return \StdClass Node with additional moderation fields.
   * @throws \Exception
   */
  public function getModerationNode($name) {
    /** @var \EntityDrupalWrapper $wrapper */
    $wrapper = $this->getEntityStore()->retrieve_by_name($name);

    if ($wrapper === FALSE) {
      throw new \Exception("No entity with the name '$name' was found. Make sure it's created in the step.");
    }
    if ($wrapper->type() !== 'node') {
      $entity_type = $wrapper->type();
      throw new \Exception("Only nodes types are supported by workbench_moderation, but $entity_type type given.");
    }
    if (!workbench_moderation_node_type_moderated($wrapper->getBundle())) {
      $types = implode(', ', workbench_moderation_moderate_node_types());
      throw new \Exception("Nodes type '{$wrapper->getBundle()}' is not a moderated type. Types enabled are [$types]'.");
    }

    $node = $wrapper->raw();
    workbench_moderation_node_data($node);

    return $node;
  }

  /**
   * @Then I click the :link next to :title
   */
  public function iClickTheLinkNextToTitle($link, $title) {
    $items = $this->getSession()->getPage()->findAll('xpath', "//span[contains(@class,'views-dkan-workflow-tree-title')]/a[text()=' " . $title . "']/../../span[contains(@class, 'views-dkan-workflow-tree-action')]/a[text()='" . $link . "']");
    if (empty($items)) {
      throw new \Exception("Link '$link' not found on the page.");
    }
    $url = reset($items)->getAttribute('href');
    $session = $this->getSession();
    $session->visit($this->locatePath($url));
  }

}

