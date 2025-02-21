<?php

namespace Drupal\metastore_admin\Tests\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the redirect_to_datasets functionality.
 *
 * This test ensures that when the "Redirect to datasets view after form submit"
 * setting is enabled, users are redirected to the dataset listing page after
 * submitting a dataset form. When disabled, users remain on the dataset node page.
 *
 * @group dkan
 * @group metastore
 * @group metastore_admin
 * @group functional
 */
class RedirectToDatasetsTest extends BrowserTestBase {

  protected static $modules = [
    'dkan',
    'metastore',
    'metastore_admin',
    'node',
  ];

  protected $defaultTheme = 'stark';

  /**
   * @todo Remove this when we drop support for Drupal 10.0.
   */
  protected $strictConfigSchema = FALSE;

  /**
   * Tests dataset form submission when redirect_to_datasets is enabled.
   */
  public function testDatasetRedirect() {
    /** @var \Drupal\metastore\MetastoreService $metastore_service */
    $metastore_service = $this->container->get('dkan.metastore.service');

    $this->drupalLogin(
    // @todo Figure out least possible admin permissions.
      $this->drupalCreateUser([], NULL, TRUE)
    );
    $assert = $this->assertSession();

    // 07_admin_dataset_json_form.spec.js : User can create and edit a dataset
    // with the json form UI.
    //
    // Since we don't have JavaScript, we can't use select2 or select_or_other
    // to add publisher or keyword entities. We create them here with arbitrary
    // UUIDs so that we can post the names to the form.
    $publisher_name = uniqid();
    $metastore_service->post('publisher',
      $metastore_service->getValidMetadataFactory()->get(
        json_encode((object) [
          'identifier' => '9deadc2f-50e0-512a-af7c-4323697d530d',
          'data' => ['name' => $publisher_name],
        ]), 'publisher', ['method' => 'POST'])
    );
    // We need a keyword.
    $keyword_data = uniqid();
    $metastore_service->post('keyword',
      $metastore_service->getValidMetadataFactory()->get(json_encode((object) [
        'identifier' => '05b2e74a-eb23-585b-9c1c-4d023e21e8a5',
        'data' => $keyword_data,
      ]), 'keyword', ['method' => 'POST'])
    );

    // Enable redirect option.
    $this->config('metastore.settings')
      ->set('redirect_to_datasets', TRUE)
      ->save();

    // Create new dataset, populate required fields.
    $this->drupalGet('node/add/data');
    $assert->statusCodeEquals(200);

    $dataset_title = 'DKANTEST dataset title';
    $this->submitForm([
      'edit-field-json-metadata-0-value-title' => $dataset_title,
      'edit-field-json-metadata-0-value-description' => 'DKANTEST dataset description.',
      'edit-field-json-metadata-0-value-accesslevel' => 'public',
      'edit-field-json-metadata-0-value-modified-date' => '2020-02-02',
      'edit-field-json-metadata-0-value-publisher-publisher-name' => $publisher_name,
      'edit-field-json-metadata-0-value-contactpoint-contactpoint-fn' => 'DKANTEST Contact Name',
      'edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail' => 'dkantest@test.com',
      'edit-field-json-metadata-0-value-keyword-keyword-0' => $keyword_data,
    ], 'Save');

    // Assert that the redirect happened.
    $assert->statusCodeEquals(200);
    $assert->addressEquals('admin/dkan/datasets');
    $assert->pageTextContains('Data ' . $dataset_title . ' has been created.');

    // Disable redirect option.
    $this->config('metastore.settings')
      ->set('redirect_to_datasets', FALSE)
      ->save();

    $this->drupalGet('admin/content');
    $assert->statusCodeEquals(200);
    $this->clickLink($dataset_title);
    $this->drupalGet($this->getSession()->getCurrentUrl() . '/edit');

    $this->submitForm([
      'edit-field-json-metadata-0-value-description' => 'Updated Dataset Description.',
    ], 'Save');

    // Assert that the user lands on the dataset node page (not redirected).
    $assert->statusCodeEquals(200);
    $assert->addressMatches('/node\/\d+$/');
    $assert->pageTextContains('Data ' . $dataset_title . ' has been updated.');
  }

}
