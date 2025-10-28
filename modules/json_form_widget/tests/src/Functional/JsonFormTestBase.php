<?php

namespace Drupal\Tests\json_form_widget\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the json form widget.
 *
 * This test replaces Cypress test:
 * - 07_admin_dataset_json_form.spec.js
 *
 * @group dkan
 * @group json_form_widget
 * @group functional
 */
abstract class JsonFormTestBase extends BrowserTestBase {

  protected static $modules = [
    'dkan',
    'json_form_widget',
    'node',
  ];

  protected $defaultTheme = 'stark';

  protected string $publisherName;
  protected string $keywordData;
  protected string $datasetTitle;

  public function setUp(): void {
    parent::setUp();
    /** @var \Drupal\metastore\MetastoreService $metastore_service */
    $metastore_service = $this->container->get('dkan.metastore.service');

    $this->drupalLogin(
    // @todo Figure out least possible admin permissions.
      $this->drupalCreateUser([], NULL, TRUE)
    );

    $this->publisherName = uniqid();
    $metastore_service->post('publisher',
      $metastore_service->getValidMetadataFactory()->get(
        json_encode((object) [
          'identifier' => '9deadc2f-50e0-512a-af7c-4323697d530d',
          'data' => ['name' => $this->publisherName],
        ]), 'publisher', ['method' => 'POST'])
    );
    // We need a keyword.
    $this->keywordData = uniqid();
    $metastore_service->post('keyword',
      $metastore_service->getValidMetadataFactory()->get(json_encode((object) [
        'identifier' => '05b2e74a-eb23-585b-9c1c-4d023e21e8a5',
        'data' => $this->keywordData,
      ]), 'keyword', ['method' => 'POST'])
    );
    $this->datasetTitle = 'DKANTEST dataset title';
  }

}
