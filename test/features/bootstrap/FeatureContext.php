<?php
use Drupal\DKANExtension\Context\RawDKANContext;

include_once 'dkan_dataset_rest_api_crud.php';

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDKANContext
{
  private $dataset_nid;
  protected $old_global_user;
  // This file is only meant for temporary custom step functions or overrides to the dkanextension.
  // Changes should be implemented in dkanextension so that it works across all projects.
  /**
   * @beforeDKANEntityCreate
   */
  public function setGlobalUserBeforeEntity(\Drupal\DKANExtension\Hook\Scope\BeforeDKANEntityCreateScope $scope) {
    // Don't do anything if workbench isn't enabled or this isn't a node.
    $wrapper = $scope->getEntity();
    if (!function_exists('workbench_moderation_moderate_node_types') || $wrapper->type() !== 'node'){
      return;
    }
    $types = workbench_moderation_moderate_node_types();
    $node_type = $wrapper->getBundle();
    // Also don't do anything if this isn't a moderation type.
    if (!in_array($node_type, $types)) {
      return;
    }
    // IF the author is set (there was a logged in user or it was set during creation)
    // See RawDKANEntity::pre_save()
    if (isset($wrapper->author)) {
      // Then set the global user so that stupid workbench is happy.
      global $user;
      // Save a backup of the user (should be anonymous)
      $this->old_global_user = $user;
      $user = $wrapper->author->value();
    }
  }
  /**
   * @afterDKANEntityCreate
   */
  public function removeGlobalUserAfterEntity(\Drupal\DKANExtension\Hook\Scope\AfterDKANEntityCreateScope $scope) {
    // After we've created the entity, set it back the the old global user (anon) so it doesn't pollute other things.
    if (isset($this->old_global_user)) {
      global $user;
      $user = $this->old_global_user;
    }
  }
  /**
   * @When I hover over the admin menu item :item
   */
  public function iHoverOverTheAdminMenuItem($item) {
    $session = $this->getSession();
    $page = $session->getPage();
    $menu = $page->findById('admin-menu-wrapper');
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
    * @Given /^I use the Dataset REST API to create:$/
    */
  public function iUseTheDatasetRestApiToCreate($data) {
    $base_url = $this->getMinkParameter('base_url');
    $endpoint = '/api/dataset';
    $entity = 'node';
    $login_url = $base_url . $endpoint . '/user/login';
    // Get cookie_session and csrf_token.
    $user_login = dkan_dataset_services_user_login($login_url);
    $cookie_session = $user_login['cookie_session'];
    $csrf_token = dkan_dataset_services_get_csrf($cookie_session, $user_login['curl'], $base_url);
    foreach ($data->getHash() as $node_data) {
      // Node data.
      $node_data = array(
        'title' => $node_data['title'],
        'type' => 'dataset',
        'body[und][0][value]' => $node_data['body'],
        'status' => $node_data['status'],
      );
      // Create node.
      $node = dkan_dataset_services_create_node($node_data, $csrf_token, $cookie_session, $base_url, $endpoint, $entity);
      $this->dataset_nid = $node->nid;
    }
    return true;
  }
  /**
   * @Given /^I use the Dataset REST API to update "test dataset":$/
   */
  public function iUseTheDatasetRestApiToUpdateTestDataset($data) {
    $base_url = $this->getMinkParameter('base_url');
    $endpoint = '/api/dataset';
    $entity = 'node';
    $login_url = $base_url . $endpoint . '/user/login';
    // Get cookie_session and csrf_token.
    $user_login = dkan_dataset_services_user_login($login_url);
    $cookie_session = $user_login['cookie_session'];
    $csrf_token = dkan_dataset_services_get_csrf($cookie_session, $user_login['curl'], $base_url);
    foreach ($data->getHash() as $node_data) {
      // Node data.
      $node_data = array(
        'title' => 'Dataset updated',
        'body[und][0][value]' => $node_data['body'],
      );
      // Update node.
      $response = dkan_dataset_services_update_node($node_data, $this->dataset_nid, $csrf_token, $cookie_session, $base_url, $endpoint, $entity);
    }
    return true;
  }
  /**
   * @Given /^I use the Dataset REST API to delete "Dataset updated":$/
   */
  public function iUseTheDatasetRestApiToDeleteDatasetUpdated() {
    $base_url = $this->getMinkParameter('base_url');
    $endpoint = '/api/dataset';
    $entity = 'node';
    $login_url = $base_url . $endpoint . '/user/login';
    // Get cookie_session and csrf_token.
    $user_login = dkan_dataset_services_user_login($login_url);
    $cookie_session = $user_login['cookie_session'];
    $csrf_token = dkan_dataset_services_get_csrf($cookie_session, $user_login['curl'], $base_url);
   //Delete node.
   $response = dkan_dataset_services_delete_node($this->dataset_nid, $csrf_token, $cookie_session, $base_url, $endpoint, $entity);
    return true;
  }
}