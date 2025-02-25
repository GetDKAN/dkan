<?php

namespace Drupal\json_form_widget\Tests\Functional;

use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\common\Traits\QueueRunnerTrait;

/**
 * Test the json form widget.
 *
 * This test replaces Cypress test:
 * - 11_admin_dataset_file_upload.spec.js
 *
 * @group dkan
 * @group json_form_widget
 * @group functional
 */
class AdminDatasetFileUploadTest extends BrowserTestBase {

  use QueueRunnerTrait;

  protected static $modules = [
    'dkan',
    'datastore',
    'json_form_widget',
    'node',
  ];

  protected $defaultTheme = 'stark';

  /**
   * @todo Remove this when we drop support for Drupal 10.0.
   */
  protected $strictConfigSchema = FALSE;

  /**
   * Test creating datasets.
   *
   * 11_admin_dataset_file_upload.spec.js : Admin dataset file upload : Create
   * dataset with remote file.
   */
  public function testCreateDatasetWithRemoteFile() {
    /** @var \Drupal\metastore\MetastoreService $metastore_service */
    $metastore_service = $this->container->get('dkan.metastore.service');

    $this->drupalLogin(
    // @todo Figure out least possible admin permissions.
      $this->drupalCreateUser([], NULL, TRUE)
    );

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

    $dataset_title = uniqid();
    $file_url = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';

    $assert = $this->assertSession();

    // 11_admin_dataset_file_upload.spec.js : Create dataset with remote file :
    // create the dataset, can fill up the form with distribution and submit.
    $this->drupalGet('node/add/data');
    $assert->statusCodeEquals(200);

    $page = $this->getSession()->getPage();

    // Use the form.
    $this->drupalGet('node/add/data');
    $assert->statusCodeEquals(200);
    $this->submitForm([
      'edit-field-json-metadata-0-value-title' => $dataset_title,
      'edit-field-json-metadata-0-value-description' => 'DKANTEST distribution description.',
      'edit-field-json-metadata-0-value-accesslevel' => 'public',
      'edit-field-json-metadata-0-value-modified-date' => '2020-02-02',
      'edit-field-json-metadata-0-value-publisher-publisher-name' => $publisher_name,
      'edit-field-json-metadata-0-value-contactpoint-contactpoint-fn' => 'DKANTEST Contact Name',
      'edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail' => 'dkantest@test.com',
      'edit-field-json-metadata-0-value-keyword-keyword-0' => $keyword_data,
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-title' => 'distribution title test',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-description' => 'distribution description test',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format' => 'csv',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl-file-url-type-remote' => 'remote',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl-file-url-remote' => $file_url,
    ], 'Save');
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('Data ' . $dataset_title . ' has been created.');

    // Queues to do import.
    $this->runQueues(['localize_import', 'datastore_import']);
    // Did our file import?
    $this->assertDatasetWasImported($dataset_title);

    // 11_admin_dataset_file_upload.spec.js : Create dataset with remote file :
    // uploaded dataset files show remote link on edit.
    $this->drupalGet('admin/dkan/datasets');
    $this->submitForm([
      'edit-title' => $dataset_title,
    ], 'Filter');
    $assert->statusCodeEquals(200);

    $page->find('css', '.views-field-nothing > a')->click();
    $assert->statusCodeEquals(200);

    $assert->elementContains('css', 'h1', 'Edit Data');
    $assert->elementAttributeContains(
      'css',
      '#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl a',
      'href',
      $file_url
    );
  }

  /**
   * Test creating datasets.
   *
   * 11_admin_dataset_file_upload.spec.js : Admin dataset file upload : Create
   * dataset with file upload.
   */
  public function testCreateDatasetWithFileUpload() {
    /** @var \Drupal\metastore\MetastoreService $metastore_service */
    $metastore_service = $this->container->get('dkan.metastore.service');

    $this->drupalLogin(
    // @todo Figure out least possible admin permissions.
      $this->drupalCreateUser([], NULL, TRUE)
    );

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

    // Title for our dataset.
    $dataset_title = uniqid();
    // The file we'll upload.
    $upload_file = realpath(dirname(__DIR__, 4) . '/datastore/tests/data/Bike_Lane.csv');

    $assert = $this->assertSession();

    // 11_admin_dataset_file_upload.spec.js : Create dataset with remote file :
    // create the dataset, can fill up the form with distribution and submit.
    $this->drupalGet('node/add/data');
    $assert->statusCodeEquals(200);

    $page = $this->getSession()->getPage();

    // Use the form.
    $this->drupalGet('node/add/data');
    $assert->statusCodeEquals(200);
    // Add our file to the form.
    $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl-upload')
      ->attachFile('file://' . $upload_file);
    $this->submitForm([
      'edit-field-json-metadata-0-value-title' => $dataset_title,
      'edit-field-json-metadata-0-value-description' => 'DKANTEST distribution description.',
      'edit-field-json-metadata-0-value-accesslevel' => 'public',
      'edit-field-json-metadata-0-value-modified-date' => '2020-02-02',
      'edit-field-json-metadata-0-value-publisher-publisher-name' => $publisher_name,
      'edit-field-json-metadata-0-value-contactpoint-contactpoint-fn' => 'DKANTEST Contact Name',
      'edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail' => 'dkantest@test.com',
      'edit-field-json-metadata-0-value-keyword-keyword-0' => $keyword_data,
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-title' => 'distribution title test',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-description' => 'distribution description test',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format' => 'csv',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl-file-url-type-upload' => 'upload',
    ], 'Save');
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('Data ' . $dataset_title . ' has been created.');

    // Queues to do import.
    $this->runQueues(['localize_import', 'datastore_import']);
    // Did our file import?
    $this->assertDatasetWasImported($dataset_title);

    // 11_admin_dataset_file_upload.spec.js : Create dataset with remote file :
    // uploaded dataset files show remote link on edit.
    $this->drupalGet('admin/dkan/datasets');
    $this->submitForm([
      'edit-title' => $dataset_title,
    ], 'Filter');
    $assert->statusCodeEquals(200);

    // Click on 'edit'.
    $page->find('css', '.views-field-nothing > a')->click();
    $assert->statusCodeEquals(200);

    // Find the URL.
    $assert->elementContains('css', 'h1', 'Edit Data');
    $uploaded_file_url = $this->baseUrl . '/' . PublicStream::basePath() . '/uploaded_resources/' . basename($upload_file);
    $assert->elementAttributeContains(
      'css',
      '#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl a',
      'href',
      $uploaded_file_url
    );
  }

  protected function assertDatasetWasImported(string $dataset_title) {
    // Get the UUID for the dataset title.
    /** @var \Drupal\node\NodeStorage $node_storage */
    $node_storage = $this->container->get('entity_type.manager')
      ->getStorage('node');
    $node_ids = $node_storage->getQuery()
      ->condition('type', 'data')
      ->condition('title', $dataset_title)
      ->accessCheck(FALSE)
      ->execute();
    $uuid = ($node_storage->load(reset($node_ids)))->uuid();

    // Get the import status for the dataset.
    /** @var \Drupal\common\DatasetInfo $info_service */
    $info_service = $this->container->get('dkan.common.dataset_info');
    $info = $info_service->gather($uuid);
    $this->assertEquals(
      'done',
      $info['latest_revision']['distributions'][0]['importer_status'] ?? 'not at all done'
    );
  }

}
