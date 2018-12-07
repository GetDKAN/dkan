<?php

/**
 * @file
 * dkan_workflow.features.workbench_email.inc
 */

/**
 * Implements hook_workbench_email_export().
 */
function dkan_workflow_workbench_email_export() {
  $items = array(
    'draft:needs_review:original author' => array(
      'from_name' => 'draft',
      'to_name' => 'needs_review',
      'author' => 1,
      'automatic' => 1,
      'subject' => 'Workbench moderation update ([site:name]) - Status of [node:content-type] "[node:title]" has changed.',
      'message' => '[user:name],

A Workbench Moderator or Supervisor on [site:name] has changed the status of the [node:content-type] "[node:title]" from [workbench-email:email-transition].

For more details, view [node:content-type] at [node:url].

There are three moderation states for content in DKAN: Draft, Needs Review and Published. To change the moderation state of a Dataset that you\'ve created or have permission to edit, click "Edit" and click the "Moderate" button located at the top of the screen. For a Resource, Blog or Page, scroll to the bottom of the page to find the "Publishing Options" menu tab.

To access your Workbench dashboard, log in and click "My Workbench" on the primary site navigation bar.

For assistance or support, please contact a Site Manager or an administrator from your organization.',
      'role' => 'original author',
    ),
    'draft:needs_review:Workflow Moderator' => array(
      'from_name' => 'draft',
      'to_name' => 'needs_review',
      'author' => 0,
      'automatic' => 1,
      'subject' => 'Workbench moderation update ([site:name]) - Status of [node:content-type] "[node:title]" has changed.',
      'message' => '[user:name],

A Workbench Moderator or Supervisor on [site:name] has changed the status of the [node:content-type] "[node:title]" from [workbench-email:email-transition].

For more details, view [node:content-type] at [node:url].

There are three moderation states for content in DKAN: Draft, Needs Review and Published. Items that are not reviewed within 72 hours are filed under "Stale Reviews."

To change the moderation state of a Dataset that you\'ve created or have permission to edit, click "Edit" and click the "Moderate" button located at the top of the screen. For a Resource, Blog or Page, scroll to the bottom of the page to find the "Publishing Options" menu tab.

To access your Workbench dashboard, log in and click "My Workbench" on the primary site navigation bar.

For assistance or support, please contact a Site Manager or an administrator from your organization.',
      'role' => 'Workflow Moderator',
    ),
    'draft:needs_review:Workflow Supervisor' => array(
      'from_name' => 'draft',
      'to_name' => 'needs_review',
      'author' => 0,
      'automatic' => 1,
      'subject' => 'Workbench moderation update ([site:name]) - Status of [node:content-type] "[node:title]" has changed.',
      'message' => '[user:name],

A Workbench Moderator or Supervisor on [site:name] has changed the status of the [node:content-type] "[node:title]" from [workbench-email:email-transition].

For more details, view [node:content-type] at [node:url].

There are three moderation states for content in DKAN: Draft, Needs Review and Published. Items that are not reviewed within 72 hours are filed under "Stale Reviews."

To change the moderation state of a Dataset that you\'ve created or have permission to edit, click "Edit" and click the "Moderate" button located at the top of the screen. For a Resource, Blog or Page, scroll to the bottom of the page to find the "Publishing Options" menu tab.

To access your Workbench dashboard, log in and click "My Workbench" on the primary site navigation bar.

For assistance or support, please contact a Site Manager or an administrator from your organization.',
      'role' => 'Workflow Supervisor',
    ),
    'needs_review:draft:original author' => array(
      'from_name' => 'needs_review',
      'to_name' => 'draft',
      'author' => 1,
      'automatic' => 1,
      'subject' => 'Workbench moderation update ([site:name]) - Status of [node:content-type] "[node:title]" has changed.',
      'message' => '[user:name],

A Workbench Moderator or Supervisor on [site:name] has changed the status of the [node:content-type] "[node:title]" from [workbench-email:email-transition].

For more details, view [node:content-type] at [node:url].

There are three moderation states for content in DKAN: Draft, Needs Review and Published. If your draft has been pushed from Needs Review back to Draft, you may edit and submit it once more for review.

To change the moderation state of a Dataset that you\'ve created or have permission to edit, click "Edit" and click the "Moderate" button located at the top of the screen. For a Resource, Blog or Page, scroll to the bottom of the page to find the "Publishing Options" menu tab.

To access your Workbench dashboard, log in and click "My Workbench" on the primary site navigation bar.

For assistance or support, please contact a Site Manager or an administrator from your organization.',
      'role' => 'original author',
    ),
    'needs_review:draft:Workflow Moderator' => array(
      'from_name' => 'needs_review',
      'to_name' => 'draft',
      'author' => 0,
      'automatic' => 1,
      'subject' => 'Workbench moderation update ([site:name]) - Status of [node:content-type] "[node:title]" has changed.',
      'message' => '[user:name],

A Workbench Moderator or Supervisor on [site:name] has changed the status of the [node:content-type] "[node:title]" from [workbench-email:email-transition].

For more details, view [node:content-type] at [node:url].

There are three moderation states for content in DKAN: Draft, Needs Review and Published. Content that has been pushed from Needs Review back to Draft may be edited and submitted once more for review.

To change the moderation state of a Dataset that you\'ve created or have permission to edit, click "Edit" and click the "Moderate" button located at the top of the screen. For a Resource, Blog or Page, scroll to the bottom of the page to find the "Publishing Options" menu tab.

To access your Workbench dashboard, log in and click "My Workbench" on the primary site navigation bar.

For assistance or support, please contact a Site Manager or an administrator from your organization.',
      'role' => 'Workflow Moderator',
    ),
    'needs_review:draft:Workflow Supervisor' => array(
      'from_name' => 'needs_review',
      'to_name' => 'draft',
      'author' => 0,
      'automatic' => 1,
      'subject' => 'Workbench moderation update ([site:name]) - Status of [node:content-type] "[node:title]" has changed.',
      'message' => '[user:name],

A Workbench Moderator or Supervisor on [site:name] has changed the status of the [node:content-type] "[node:title]" from [workbench-email:email-transition].

For more details, view [node:content-type] at [node:url].

There are three moderation states for content in DKAN: Draft, Needs Review and Published. Content that has been pushed from Needs Review back to Draft may be edited and submitted once more for review.

To change the moderation state of a Dataset that you\'ve created or have permission to edit, click "Edit" and click the "Moderate" button located at the top of the screen. For a Resource, Blog or Page, scroll to the bottom of the page to find the "Publishing Options" menu tab.

To access your Workbench dashboard, log in and click "My Workbench" on the primary site navigation bar.

For assistance or support, please contact a Site Manager or an administrator from your organization.',
      'role' => 'Workflow Supervisor',
    ),
    'needs_review:published:original author' => array(
      'from_name' => 'needs_review',
      'to_name' => 'published',
      'author' => 1,
      'automatic' => 1,
      'subject' => 'Workbench moderation update ([site:name]) - [node:content-type] "[node:title]" is now published',
      'message' => '[user:name],

A Workbench Moderator or Supervisor on [site:name] has changed the status of the [node:content-type] "[node:title]" from [workbench-email:email-transition].

For more details, view [node:content-type] at [node:url].

There are three moderation states for content in DKAN: Draft, Needs Review and Published. If you\'ve published content but wish to remove it from the site, you can change its status back to "Draft" or consult a Moderator or Site Manager.

To change the moderation state of a Dataset that you\'ve created or have permission to edit, click "Edit" and click the "Moderate" button located at the top of the screen. For a Resource, Blog or Page, scroll to the bottom of the page to find the "Publishing Options" menu tab. Removing the checkmark from the "Publish" box un-publishes content, keeping it on the site but removing it from public view.

To access your Workbench dashboard, log in and click "My Workbench" on the primary site navigation bar.

For assistance or support, please contact a Site Manager or an administrator from your organization.',
      'role' => 'original author',
    ),
    'needs_review:published:Workflow Moderator' => array(
      'from_name' => 'needs_review',
      'to_name' => 'published',
      'author' => 0,
      'automatic' => 1,
      'subject' => 'Workbench moderation update ([site:name]) - [node:content-type] "[node:title]" is now published',
      'message' => '[user:name],

A Workbench Moderator or Supervisor on [site:name] has changed the status of the [node:content-type] "[node:title]" from [workbench-email:email-transition].

For more details, view [node:content-type] at [node:url].

There are three moderation states for content in DKAN: Draft, Needs Review and Published. If you\'ve published content but wish to remove it from the site, you can change its status back to "Draft" or see the below instructions on how to un-publish it.

To change the moderation state of a Dataset that you\'ve created or have permission to edit, click "Edit" and click the "Moderate" button located at the top of the screen. For a Resource, Blog or Page, scroll to the bottom of the page to find the "Publishing Options" menu tab. Removing the checkmark from the "Publish" box un-publishes content, keeping it on the site but removing it from public view.

To access your Workbench dashboard, log in and click "My Workbench" on the primary site navigation bar.

For assistance or support, please contact a Site Manager or an administrator from your organization.',
      'role' => 'Workflow Moderator',
    ),
    'needs_review:published:Workflow Supervisor' => array(
      'from_name' => 'needs_review',
      'to_name' => 'published',
      'author' => 0,
      'automatic' => 1,
      'subject' => 'Workbench moderation update ([site:name]) - [node:content-type] "[node:title]" is now published',
      'message' => '[user:name],

A Workbench Moderator or Supervisor on [site:name] has changed the status of the [node:content-type] "[node:title]" from [workbench-email:email-transition].

For more details, view [node:content-type] at [node:url].

There are three moderation states for content in DKAN: Draft, Needs Review and Published. If you\'ve published content but wish to remove it from the site, you can change its status back to "Draft" or see the below instructions on how to un-publish it.

To change the moderation state of a Dataset that you\'ve created or have permission to edit, click "Edit" and click the "Moderate" button located at the top of the screen. For a Resource, Blog or Page, scroll to the bottom of the page to find the "Publishing Options" menu tab. Removing the checkmark from the "Publish" box un-publishes content, keeping it on the site but removing it from public view.

To access your Workbench dashboard, log in and click "My Workbench" on the primary site navigation bar.

For assistance or support, please contact a Site Manager or an administrator from your organization.',
      'role' => 'Workflow Supervisor',
    ),
    'published:needs_review:original author' => array(
      'from_name' => 'published',
      'to_name' => 'needs_review',
      'author' => 1,
      'automatic' => 1,
      'subject' => 'Workbench moderation update ([site:name]) - Status of [node:content-type] "[node:title]" has changed.',
      'message' => '[user:name],

A Workbench Moderator or Supervisor on [site:name] has changed the status of the [node:content-type] "[node:title]" from [workbench-email:email-transition].

For more details, view [node:content-type] at [node:url].

There are three moderation states for content in DKAN: Draft, Needs Review and Published.

To change the moderation state of a Dataset that you\'ve created or have permission to edit, click "Edit" and click the "Moderate" button located at the top of the screen. For a Resource, Blog or Page, scroll to the bottom of the page to find the "Publishing Options" menu tab.

To access your Workbench dashboard, log in and click "My Workbench" on the primary site navigation bar.

For assistance or support, please contact a Site Manager or an administrator from your organization.',
      'role' => 'original author',
    ),
    'published:needs_review:Workflow Moderator' => array(
      'from_name' => 'published',
      'to_name' => 'needs_review',
      'author' => 0,
      'automatic' => 1,
      'subject' => 'Workbench moderation update ([site:name]) - Status of [node:content-type] "[node:title]" has changed.',
      'message' => '[user:name],

A Workbench Moderator or Supervisor on [site:name] has changed the status of the [node:content-type] "[node:title]" from [workbench-email:email-transition].

For more details, view [node:content-type] at [node:url].

There are three moderation states for content in DKAN: Draft, Needs Review and Published.

To change the moderation state of a Dataset that you\'ve created or have permission to edit, click "Edit" and click the "Moderate" button located at the top of the screen. For a Resource, Blog or Page, scroll to the bottom of the page to find the "Publishing Options" menu tab.

To access your Workbench dashboard, log in and click "My Workbench" on the primary site navigation bar.

For assistance or support, please contact a Site Manager or an administrator from your organization.',
      'role' => 'Workflow Moderator',
    ),
    'published:needs_review:Workflow Supervisor' => array(
      'from_name' => 'published',
      'to_name' => 'needs_review',
      'author' => 0,
      'automatic' => 1,
      'subject' => NULL,
      'message' => NULL,
      'role' => 'Workflow Supervisor',
    ),
  );
  return $items;
}
