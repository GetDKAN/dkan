<?php

namespace Drupal\Tests\json_form_widget\Functional;

/**
 * Test the json form widget.
 *
 * This test replaces Cypress test:
 * - 07_admin_dataset_json_form.spec.js
 *
 * @group dkan
 * @group json_form_widget
 * @group functional
 * @group functional2
 */
class AdminDatasetJsonFormTest extends JsonFormTestBase {

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

    // 07_admin_dataset_json_form.spec.js : License field is
    // select_or_other elements in dataset form.
    // These select elements have an '- Other -' option.
    foreach ([
      "#edit-field-json-metadata-0-value-license-select option[value='select_or_other']",
    ] as $locator) {
      $item = $page->find('css', $locator);
      $this->assertEquals('select_or_other', $item->getValue());
    }
    // Assert the existence of the 'other' text element for select_or_other
    // fields.
    foreach ([
      '#edit-field-json-metadata-0-value-license-other.form-url',
    ] as $locator) {
      $this->assertNotNull($page->find('css', $locator));
    }

    // 07_admin_dataset_json_form.spec.js : format field is select elements in dataset form.
    // These select elements have options, but do not have an '- Other -' option.
    foreach ([
      "#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format option",
    ] as $locator) {
      $item = $page->find('css', $locator);
      $this->assertNotEquals('select', $item->getValue());
    }
    // Assert the nonexistence of the 'other' text element for select_or_other
    // fields.
    foreach ([
      '#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format.form-text',
    ] as $locator) {
      $this->assertNull($page->find('css', $locator));
    }

    // Assert that there is no "Add" button for any array fields that are now
    // select2 elements (e.g. Topics or Tags)
    foreach (['keyword', 'theme'] as $field) {
      $this->assertNull($page->find('css', sprintf(
        '#edit-field-json-metadata-0-value-theme-array-actions-actions-add',
        $field
      )));
    }

    // 07_admin_dataset_json_form.spec.js : User can create and edit a dataset
    // with the json form UI. User can delete a dataset.
    $this->drupalGet('node/add/data');
    $assert->statusCodeEquals(200);

    // Quickly test adding and removing a distribution.
    $page->find('css', '[id^="edit-field-json-metadata-0-value-distribution-array-actions-actions-add"]')->click();
    $assert->statusCodeEquals(200);
    // Now we have two distributions.
    $this->assertNotNull($page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-0-distribution"]'));
    $this->assertNotNull($page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-1-distribution"]'));
    $page->find('css', '[id^="edit-field-json-metadata-0-value-distribution-distribution-0-distribution-actions-remove"]')->click();
    // Now we have one again.
    $this->assertNotNull($page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-0-distribution"]'));
    $this->assertNull($page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-1-distribution"]'));

    $this->submitForm([
      'edit-field-json-metadata-0-value-title' => $this->datasetTitle,
      'edit-field-json-metadata-0-value-description' => 'DKANTEST dataset description.',
      'edit-field-json-metadata-0-value-accesslevel' => 'public',
      'edit-field-json-metadata-0-value-modified-date' => '2020-02-02',
      'edit-field-json-metadata-0-value-publisher-publisher-name' => $this->publisherName,
      'edit-field-json-metadata-0-value-contactpoint-contactpoint-fn' => 'DKANTEST Contact Name',
      'edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail' => 'dkantest@test.com',
      'edit-field-json-metadata-0-value-keyword-keyword-0' => $this->keywordData,
    ], 'Save');
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('Data DKANTEST dataset title has been created.');

    // Confirm the default dkan admin view is filtered to show only datasets.
    $this->drupalGet('admin/dkan/datasets');
    foreach ($page->findAll('css', 'tbody tr') as $row) {
      $this->assertStringContainsString(
        'dataset',
        $row->find('css', 'td.views-field-field-data-type')->getText()
      );
    }

    // Filter for our dataset.
    $this->drupalGet('admin/dkan/datasets');
    $this->submitForm(['edit-title' => $this->datasetTitle], 'Filter');

    // Edit the dataset.
    $page->find('css', 'tbody > tr:first-of-type > .views-field-nothing > a')->click();
    $this->assertNotNull($page->find('css', '#edit-field-json-metadata-0-value-title'));
    $assert->fieldValueEquals('edit-field-json-metadata-0-value-title', $this->datasetTitle);
    $dataset_new_title = 'NEW dkantest dataset title';
    $this->submitForm([
      'edit-field-json-metadata-0-value-title' => $dataset_new_title,
      // R/P1Y means Annual.
      'edit-field-json-metadata-0-value-accrualperiodicity' => 'R/P1Y',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-title' => 'DKANTEST distribution title text',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-description' => 'DKANTEST distribution description text',
      'edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format' => 'csv',
    ], 'Save');
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('Data ' . $dataset_new_title . ' has been updated.');

    // User can delete the dataset.
    $this->drupalGet('admin/dkan/datasets');
    $this->submitForm([
      'edit-node-bulk-form-0' => TRUE,
      'edit-action' => 'node_delete_action',
    ], 'Apply to selected items');
    $assert->statusCodeEquals(200);
    // Are you sure?
    $page->find('css', '#edit-submit')->click();
    $assert->pageTextContains('Deleted 1 content item.');
  }

}
