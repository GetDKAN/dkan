<?php

namespace Drupal\json_form_widget\Tests\Functional;

use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Tests\common\Traits\QueueRunnerTrait;
use Drupal\Tests\json_form_widget\Functional\JsonFormTestBase;

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
class AdminDatasetFileUploadTest extends JsonFormTestBase {

  use QueueRunnerTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'dkan',
    'datastore',
    'json_form_widget',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test creating datasets.
   *
   * 11_admin_dataset_file_upload.spec.js : Admin dataset file upload : Create
   * dataset with remote file.
   */
  public function testCreateDatasetWithRemoteFile() {
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
      'edit-field-json-metadata-0-value-title' => $this->datasetTitle,
      'edit-field-json-metadata-0-value-description' => 'DKANTEST distribution description.',
      'edit-field-json-metadata-0-value-accesslevel' => 'public',
      'edit-field-json-metadata-0-value-modified-date' => '2020-02-02',
      'edit-field-json-metadata-0-value-publisher-publisher-name' => $this->publisherName,
      'edit-field-json-metadata-0-value-contactpoint-contactpoint-fn' => 'DKANTEST Contact Name',
      'edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail' => 'dkantest@test.com',
      'edit-field-json-metadata-0-value-keyword-keyword-0' => $this->keywordData,
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-title' => 'distribution title test',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-description' => 'distribution description test',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format' => 'csv',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl-file-url-type-remote' => 'remote',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl-file-url-remote' => $file_url,
    ], 'Save');
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('Data ' . $this->datasetTitle . ' has been created.');

    // Queues to do import.
    $this->runQueues(['localize_import', 'datastore_import']);
    // Did our file import?
    $this->assertDatasetWasImported($this->datasetTitle);

    // 11_admin_dataset_file_upload.spec.js : Create dataset with remote file :
    // uploaded dataset files show remote link on edit.
    $this->drupalGet('admin/dkan/datasets');
    $this->submitForm([
      'edit-title' => $this->dataset_title,
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
      'edit-field-json-metadata-0-value-title' => $this->datasetTitle,
      'edit-field-json-metadata-0-value-description' => 'DKANTEST distribution description.',
      'edit-field-json-metadata-0-value-accesslevel' => 'public',
      'edit-field-json-metadata-0-value-modified-date' => '2020-02-02',
      'edit-field-json-metadata-0-value-publisher-publisher-name' => $this->publisherName,
      'edit-field-json-metadata-0-value-contactpoint-contactpoint-fn' => 'DKANTEST Contact Name',
      'edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail' => 'dkantest@test.com',
      'edit-field-json-metadata-0-value-keyword-keyword-0' => $this->keywordData,
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-title' => 'distribution title test',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-description' => 'distribution description test',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format' => 'csv',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl-file-url-type-upload' => 'upload',
    ], 'Save');
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('Data ' . $this->datasetTitle . ' has been created.');

    // Queues to do import.
    $this->runQueues(['localize_import', 'datastore_import']);
    // Did our file import?
    $this->assertDatasetWasImported($this->datasetTitle);

    // 11_admin_dataset_file_upload.spec.js : Create dataset with remote file :
    // uploaded dataset files show remote link on edit.
    $this->drupalGet('admin/dkan/datasets');
    $this->submitForm([
      'edit-title' => $this->datasetTitle,
    ], 'Filter');
    $assert->statusCodeEquals(200);

    // Click on 'edit'.
    $page->find('css', '.views-field-nothing > a')->click();
    $assert->statusCodeEquals(200);

    // Find the URL.
    $assert->elementContains('css', 'h1', 'Edit Data');
    $uploaded_file_url = PublicStream::basePath() . '/uploaded_resources/' . basename($upload_file);
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
