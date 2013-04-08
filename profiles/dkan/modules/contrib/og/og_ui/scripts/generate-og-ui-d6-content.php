#!/usr/bin/env php
<?php


/**
 * Generate content for a Drupal 6 database to test the upgrade process of
 * Organic groups UI.
 *
 * @todo: Currently we use Drush for producing the script, find out why I wasn't
 * able to use just php-cli.
 *
 * Run this script at the root of an existing Drupal 6 installation.
 * Steps to use this generation script:
 * - Install Drupal 6.
 * - Install and enable Organic groups module.
 * - Copy from Drupal 7 includes/utility.inc to Drupal 6 includes folder (the
 *   include file is version agnostic).
 * - Execute script to create the content by running
 *     drush php-script generate-og-ui-d6-content.php
 *  - Execute script to dump the database by running
 *      drush php-script dump-database-d6.sh > drupal-6.og-ui.database.php
 *   from the command line of the Drupal 6 ROOT directory.
 * - Since Organic groups module is a contrib module, it needs to be disabled
 *   for the upgrade path, thus open the result file with a text editor and
 *   under the {system} table, change the "status" value of the 'og' insertion
 *   to 0.
 *
 * This scripts produces the following scenario:
 * - Nid 1: Group with "Open" selective state.
 * - Nid 2: Group with "Moderated" selective state.
 * - Nid 3: Group with "Invite only" selective state.
 * - Nid 4: Group with "Closed" selective state.
 */

// Define settings.
$cmd = 'index.php';
$_SERVER['HTTP_HOST']       = 'default';
$_SERVER['PHP_SELF']        = '/index.php';
$_SERVER['REMOTE_ADDR']     = '127.0.0.1';
$_SERVER['SERVER_SOFTWARE'] = NULL;
$_SERVER['REQUEST_METHOD']  = 'GET';
$_SERVER['QUERY_STRING']    = '';
$_SERVER['PHP_SELF']        = $_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_USER_AGENT'] = 'console';
$modules_to_enable          = array('og', 'user', 'node');

// Bootstrap Drupal.
include_once './includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

// Enable requested modules
include_once './modules/system/system.admin.inc';
$form = system_modules();
foreach ($modules_to_enable as $module) {
  $form_state['values']['status'][$module] = TRUE;
}
$form_state['values']['disabled_modules'] = $form['disabled_modules'];
system_modules_submit(NULL, $form_state);
unset($form_state);

// Run cron after installing
drupal_cron_run();

// Create new content types.
module_load_include('inc','node','content_types');
$form_state = array( 'values' => array() );
$form_state['values']['name'] = 'test-group';
$form_state['values']['type'] = 'test_group';
$form_state['values']['og_content_type_usage'] = 'group';
drupal_execute('node_type_form',$form_state);

$form_state = array(  'values'=>array() );
$form_state['values']['name'] = 'test-post-group';
$form_state['values']['type'] = 'test_post_group';
$form_state['values']['og_content_type_usage'] = 'group_post_standard';
drupal_execute('node_type_form',$form_state);

// Create users with ID 3 to 7.
$user_ids=array();
foreach (range(3, 7) as $i) {
  $user_values = array();
  $user_values['name'] = 'og_test_user' . $i;
  $user_values['mail'] = 'og_test_user' . $i . '@example.com';
  $user_values['pass'] = user_password(5);
  $user_values['status'] = 1;
  $user = user_save(NULL,$user_values);
  $user_ids[$i] = $user->uid;
}

/**
 * Organic groups content generation interface.
 */
interface ogContent {
  //Returns a list of groups configuration arrays.
  public function groupList($user_ids);

  // Returns a list of posts configuation arrays
  // Groups nids generated in groupList are provided in groups parameter.
  public function postList($user_ids, $groups);

  // Performs actions of the generated groups and posts.
  public function groupActions($user_ids, $groups, $posts);
}

/**
 * Groups with different selective state (e.g. open, moderated, etc'.).
 */
class ogGroupSelectiveState implements ogContent {
  public function groupList($user_ids) {
    $list = array();

    foreach (og_selective_map() as $key => $value) {
      $list[] = array(
        'title' => 'group-selective-state-' . $key,
        'uid' => $user_ids[3],
        'body' => 'Group with selective state set to ' . $value,
        'og_description' => 'Group with selective state set.',
        'og_selective' => $key,
      );
    }

    return $list;
  }

  public function postList($user_ids, $groups) {
    return array();
  }

  public function groupActions($user_ids, $groups, $posts) {}
}

// Start content generation.
$og_content_config = array();
$og_content_config[] = new ogGroupSelectiveState();

foreach ($og_content_config as $content_config){
  $groups = array_map('og_group_node' , $content_config->groupList($user_ids) );
  $posts = array_map('og_post_node', $content_config->postList($user_ids, $groups));
  $content_config->groupActions($user_ids, $groups, $posts);
}

/**
 * Create a group node.
 */
function og_group_node($values = array()) {
  $node=(object)$values;
  $node->type = 'test_group';
  node_save($node);
  return $node->nid;
}

/**
 * Create a group post.
 */
function og_post_node($values = array()){
  $node=(object)$values;
  $node->type = 'test_post_group';
  node_save($node);
  return $node->nid;
}
