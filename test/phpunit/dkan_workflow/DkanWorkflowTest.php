<?php
/**
 * @file
 * Unit tests for dkan_workflow functions.
 */

module_load_include('module', 'dkan_workflow');

/**
 * Base dkan_workflow unit test class.
 */
class DkanWorkflowBaseTest extends PHPUnit_Framework_TestCase {

  /**
   * Verify that email errors get removed from messages.
   *
   * @covers dkan_workflow().
   */
  public function testDkanWorlflowRemoveEmailErrors() {
    // When no email error.
    $error = 'This is not an email error.';
    drupal_set_message($error, 'error');
    $expected = ['error' => [$error]];
    dkan_workflow_remove_email_errors();

    $actual = drupal_get_messages('error');
    $this->assertEquals($actual, $expected);

    // When mixed email error with regular error.
    $email_error = 'This is an email notifications error.';
    $expected = ['error' => [$error]];
    drupal_set_message($error, 'error');
    drupal_set_message($email_error, 'error');
    dkan_workflow_remove_email_errors();

    $actual = drupal_get_messages('error');
    $this->assertEquals($actual, $expected);

    // When only an email error.
    $email_error = 'This is an email notifications error.';
    drupal_set_message($email_error, 'error');
    $expected = [];
    dkan_workflow_remove_email_errors();

    $actual = drupal_get_messages('error');
    $this->assertEquals($actual, $expected);
  }

  public function testGetEmailReceiverUser() {
  }
}
