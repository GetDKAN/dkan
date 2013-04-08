#!/usr/bin/env php
<?php


/**
 * Generate content for a Drupal 7 database to test og_update_7001.
 *
 * @todo: Currently we use Drush for producing the script, find out why I wasn't
 * able to use just php-cli.
 *
 * Run this script at the root of an existing Drupal 6 installation.
 * Steps to use this generation script:
 * - Install and enable Organic groups module.
 * - Execute script to create the content by running
 *     drush php-script generate-og-d7-content-update-7001.php
 * - Download script from http://drupal.org/node/838438#comment-4208914 and
 *   place in root of Drupal installation.
 * - Execute script to dump the database by running from the command line of the
 *   Drupal 7 ROOT directory
 *     drush php-script dump-database-d7.sh > drupal-7.og.update_7001.database.php
 *
 * This scripts produces the following scenario:
 * - Create three users with users ID 2 - 4.
 * - Create group node.
 * - Associate user ID 2 to group with state active (group manager).
 * - Associate user ID 3 to group with state active, and creation timestamp set
 *   to "1000000000".
 * - Associate user ID 4 to group with state pending.
 * - Associate user ID 5 to group with state blocked.
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
$modules_to_enable          = array('og', 'entity');

// Bootstrap Drupal.
include_once './includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
module_enable(array('og', 'entity'));

// Run cron after installing
drupal_cron_run();

// Create users with ID 2 to 5.
$uids = array();
foreach (range(2, 5) as $i) {
  $values = array(
    'name' => 'og_test_user' . $i,
    'mail' => 'og_test_user' . $i . '@example.com',
    'pass' => user_password(5),
    'status' => 1,
  );

  $account = entity_create('user', $values);
  entity_save('user', $account);
  $uids[] = $account;
}


// Create a group node.
og_create_field(OG_GROUP_FIELD, 'node', 'article');
$values = array(
  'uid' => $uids[0]->uid,
  'type' => 'article',
  'title' => 'Group node',
);
$node = entity_create('node', $values);
$node->{OG_GROUP_FIELD}[LANGUAGE_NONE][0]['value'] = 1;
entity_save('node', $node);

// Explicitly set the created timestamp.
$node->{OG_GROUP_FIELD}[LANGUAGE_NONE][0]['created'] = 1000000000;
entity_save('node', $node);

// Assign users to group.
$group = og_get_group('node', $node->nid);

$items = array(
  1 => OG_STATE_ACTIVE,
  2 => OG_STATE_PENDING,
  3 => OG_STATE_BLOCKED,
);

foreach ($items as $key => $state) {
  og_group($group->gid, 'user', $uids[$key], $state);
}