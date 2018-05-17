<?php

/**
 * @file
 * ApiTest.
 */

use DKAN\Client;

/**
 * Class ApiTest.
 */
class ApiTest extends \PHPUnit_Framework_TestCase {

  private $client;

  /**
   * Constructor.
   */
  public function __construct($name = NULL, array $data = [], $dataName = '') {
    parent::__construct($name, $data, $dataName);
    global $base_url;
    $this->client = new Client("{$base_url}/api/dataset");
    $this->client->login('admin', 'admin');
  }

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass() {
    // We need this module for the testResourceRedirect test.
    module_enable(array('dkan_dataset_test'));
  }

  /**
   * Test required fields.
   */
  public function testRequiredFields() {
    try {
      $this->client->nodeCreate((object) []);
    }
    catch (\Exception $e) {
      $message = $e->getMessage();
      $this->assertContains("406", $message);
      $this->assertContains("Node type is required", $message);
    }
  }

  /**
   * Test options.
   */
  public function testOptions() {
    try {
      $this->client->nodeCreate((object) [
        'title' => 'PHPUNIT Test Dataset',
        'type' => 'dataset',
        'field_license' => ['und' => [0 => ['value' => "blah"]]]
      ]);
    }
    catch (\Exception $e) {
      $message = $e->getMessage();
      $this->assertContains("Invalid option for field field_license", $message);
    }
  }

  /**
   * Test options.
   */
  public function testCustomValidation() {
    try {
      module_enable
      $this->client->nodeCreate((object) [
        'title' => 'PHPUNIT Custom Validation',
        'body' => ['und' => [0 => ['value => "PHPUNIT Test Dataset Custom Validation"']]]
        'type' => 'dataset',
      ]);
    }
    catch (\Exception $e) {
      $message = $e->getMessage();
      $this->assertContains("Test validation rejection", $message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function tearDownAfterClass() {
    // Clean enabled modules.
    module_disable(array('dkan_dataset_test'));
  }
}
