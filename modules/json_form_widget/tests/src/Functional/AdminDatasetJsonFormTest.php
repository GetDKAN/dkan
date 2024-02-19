<?php

namespace Drupal\json_form_widget\Tests\Functional;

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
class AdminDatasetJsonFormTest extends BrowserTestBase {

  protected static $modules = [
    'dkan',
    'json_form_widget',
    'node',
  ];

  protected $defaultTheme = 'stark';

  public function testEmptyForm() {
    $this->drupalLogin(
    // @todo Figure out least possible admin permissions.
      $this->drupalCreateUser(['bypass node access'])
    );
    $assert = $this->assertSession();

    $this->drupalGet('node/add/data');
    $assert->statusCodeEquals(200);

    $node_title = 'dkan data title';

    $this->submitForm([
      'edit-field-json-metadata-0-value-title' => $node_title,
      'edit-field-json-metadata-0-value-description' => 'description',
      'edit-field-json-metadata-0-value-modified-date' => '2024-02-18',
      'edit-field-json-metadata-0-value-contactpoint-contactpoint-fn' => 'contact name',
      'edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail' => 'foo@example.com',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl-file-url-remote' => 'https://demo.getdkan.org/sites/default/files/distribution/cedcd327-4e5d-43f9-8eb1-c11850fa7c55/Bike_Lane.csv',
    ], 'Save', 'node-data-form');

    $this->assertNotEmpty($node = $this->drupalGetNodeByTitle($node_title));
    $this->assertEquals($node_title, $node->get('label')->getValue());
  }

  public function testAdminDatasetJsonForm() {
    $this->drupalLogin(
    // @todo Figure out least possible admin permissions.
      $this->drupalCreateUser([], NULL, TRUE)
    );
    $assert = $this->assertSession();

    // 07_admin_dataset_json_form.spec.js : The dataset form has the correct
    // required fields.
    $this->drupalGet('node/add/data');
    $assert->statusCodeEquals(200);

    $page = $this->getSession()->getPage();

    // These fields should be marked as required.
    foreach ([
      '#edit-field-json-metadata-0-value-title',
      '#edit-field-json-metadata-0-value-description',
      '#edit-field-json-metadata-0-value-accesslevel',
      '#edit-field-json-metadata-0-value-modified-date',
      '#edit-field-json-metadata-0-value-publisher-publisher-name',
      '#edit-field-json-metadata-0-value-contactpoint-contactpoint-fn',
      '#edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail',
    ] as $locator) {
      $this->assertEquals(
        'required',
        $page->find('css', $locator)->getAttribute('required')
      );
    }

    // 07_admin_dataset_json_form.spec.js : License and format fields are
    // select_or_other elements in dataset form.
    // These select elements have an '- Other -' option.
    foreach ([
      "#edit-field-json-metadata-0-value-license-select option[value='select_or_other']",
      "#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format-select option[value='select_or_other']",
    ] as $locator) {
      $item = $page->find('css', $locator);
      $this->assertEquals('select_or_other', $item->getValue());
    }
    // Assert the existence of the 'other' text element for select_or_other
    // fields.
    foreach ([
      '#edit-field-json-metadata-0-value-license-other.form-url',
      '#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format-other.form-text',
    ] as $locator) {
      $this->assertNotNull($page->find('css', $locator));
    }
    // More to do here.
  }

}
