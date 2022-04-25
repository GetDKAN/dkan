<?php

namespace Drupal\Tests\metastore_admin\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Core\Url;

/**
 * Test the bulk operations action.
 *
 * @group metastore_admin
 */
class HideCurrentRevisionActionTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stable';


  /**
   * An admin user used for this test.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * The permissions of the admin user.
   *
   * @var string[]
   */
  protected $adminUserPermissions = [
    'use dkan_publishing transition hidden',
    'Moderated content bulk publish',
    'Moderated content bulk unpublish',
    'Moderated content bulk archive',
  ];

  /**
   * The modules to load to run the test.
   *
   * @var array
   */
  protected static $modules = [
    'user',
    'dkan',
    'metastore_admin',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser($this->adminUserPermissions);
    $this->drupalLogin($this->adminUser);

  }

  /**
   * Test that the options we expect in the form are present.
   */
  public function testVboOptionsExist() {
    $this->drupalGet('admin/dkan/datasets');
    $this->assertResponse(200);

    // Check that our select field displays on the form,
    $this->assertFieldByName('action');

    // Check that all of our options are available.
    $options = [
      'Archive current revision',
      'Hide current revision',
      'Delete content',
      'Publish latest revision',
    ];

    foreach ($options as $option) {
      $this->assertOption('edit-action', $option);
    }

    // Check that Pin is not an option.
    $this->assertNoOption('edit-action', 'Pin Content');
  }

}
