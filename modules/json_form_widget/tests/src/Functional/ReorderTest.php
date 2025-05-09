<?php

namespace Drupal\json_form_widget\Tests\Functional;

use Drupal\Tests\json_form_widget\Functional\JsonFormTestBase;

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
class ReorderTest extends JsonFormTestBase {

  /**
   * One mega-test to make it faster. Try lots of scenarios for reordering.
   */
  public function testAdminJsonFormArrayReorder() {
    $this->drupalLogin(
      $this->drupalCreateUser([], NULL, TRUE)
    );
    $assert = $this->assertSession();

    // SCENARIO ONE: NEW DATASET FORM

    $this->drupalGet('node/add/data');
    $assert->statusCodeEquals(200);
    $page = $this->getSession()->getPage();

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

    // Add a distribution again.
    $page->find('css', '[id^="edit-field-json-metadata-0-value-distribution-array-actions-actions-add"]')->click();
    $assert->statusCodeEquals(200);
    // Enter a title and remote URL for each
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-0-distribution-title"]')->setValue('DKANTEST distribution 0 title text');
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl-file-url-remote"]')->setValue('https://example.com/dkan-test-distribution-0.csv');
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-1-distribution-title"]')->setValue('DKANTEST distribution 1 title text');
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-1-distribution-downloadurl-file-url-remote"]')->setValue('https://example.com/dkan-test-distribution-1.csv');

    // Now move the first distribution to the second position.
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-0-distribution-actions-move-down"]')->click();
    
    // Assert that the title and URL of the first distribution is now in the second position.
    $this->assertCorrectTitle(1, 0);
    // Note: this should really show the full URL, the theme logic that controls
    // this depends on file usage being in place so fails when on a new dataset
    // form.
    $this->assertEquals(
      'dkan-test-distribution-0.csv',
      $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-1-distribution-downloadurl a')->getText()
    );

    // Assert that the title and URL of the second distribution is now in the first position.
    $this->assertCorrectTitle(0, 1);
    $this->assertEquals(
      'dkan-test-distribution-1.csv',
      $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl a')->getText()
    );
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

    // SCENARIO TWO: BASIC REORDER EXISTING DATASET FORM, SAVE AND RE-EDIT

    $page->find('css', 'tbody > tr:first-of-type > .views-field-nothing > a')->click();
    // Move the second distribution to the first position.
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-1-distribution-actions-move-up"]')->click();
    // Assert that the title and URL of the original first distribution is
    // now back in the first position.
    $this->assertCorrectTitle(0, 0);
    $this->assertEquals(
      // Now that we're editing an existing dataset, we see the full URL.
      'https://example.com/dkan-test-distribution-0.csv',
      $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl a')->getText()
    );
    // Get the URL to edit this dataset, so we find our way back later.
    $edit_url = $this->getSession()->getCurrentUrl();
    $this->submitForm([], 'Save');
    $assert->statusCodeEquals(200);
    $this->drupalGet($edit_url);
    $this->assertCorrectTitle(0, 0);
    $this->assertEquals(
      'https://example.com/dkan-test-distribution-0.csv',
      $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl a')->getText()
    );
    $this->assertCorrectTitle(1, 1);
    $this->assertEquals(
      'https://example.com/dkan-test-distribution-1.csv',
      $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-1-distribution-downloadurl a')->getText()
    );

    // SCENARIO THREE: REMOVE DISTRIBUTION, ADD NEW DISTRIBUTION

    $this->drupalGet($edit_url);
    $page->find('css', '[id^="edit-field-json-metadata-0-value-distribution-distribution-1-distribution-actions-remove"]')->click();
    $assert->statusCodeEquals(200);
    $this->assertNull($page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-1-distribution"]'));
    $page->find('css', '[id^="edit-field-json-metadata-0-value-distribution-array-actions-actions-add"]')->click();
    $assert->statusCodeEquals(200);
    // Assert that the title and URL fields are empty.
    $this->assertEquals(
      '',
      $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-1-distribution-title"]')->getValue()
    );
    $this->assertEquals(
      '',
      $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-1-distribution-downloadurl-file-url-remote"]')->getValue()
    );
    // There is no managed file.
    $this->assertNull(
      $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-1-distribution-downloadurl a')
    );

    // SCENARIO FIVE: ADD NEW DISTRIBUTION AND REORDER

    $this->drupalGet($edit_url);
    $this->assertCorrectTitle(0, 0);
    $this->assertEquals(
      'https://example.com/dkan-test-distribution-0.csv',
      $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl a')->getText()
    );
    $this->assertCorrectTitle(1, 1);
    $this->assertEquals(
      'https://example.com/dkan-test-distribution-1.csv',
      $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-1-distribution-downloadurl a')->getText()
    );
    // Add a new distribution.
    $page->find('css', '[id^="edit-field-json-metadata-0-value-distribution-array-actions-actions-add"]')->click();
    $assert->statusCodeEquals(200);
    // Assert that the title and URL fields are empty.
    $this->assertEquals(
      '',
      $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-2-distribution-title"]')->getValue()
    );
    $this->assertEquals(
      '',
      $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-2-distribution-downloadurl-file-url-remote"]')->getValue()
    );
    // Add a title and URL.
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-2-distribution-title"]')->setValue('DKANTEST distribution 2 title text');
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-2-distribution-downloadurl-file-url-remote"]')->setValue('https://example.com/dkan-test-distribution-2.csv');
    // Now move the third distribution to the second position.
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-2-distribution-actions-move-up"]')->click();
    // Assert that the title and URL of the third distribution are now in the
    // second position.
    $this->assertCorrectTitle(1, 2);
    $this->assertEquals(
      'https://example.com/dkan-test-distribution-2.csv',
      $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-1-distribution-downloadurl a')->getText()
    );

    // SCENARIO FOUR: REMOVE FILE, REPLACE URL AND REORDER BEFORE SAVING

    $this->drupalGet($edit_url);
    $this->assertCorrectTitle(0, 0);
    $this->assertEquals(
      'https://example.com/dkan-test-distribution-0.csv',
      $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl a')->getText()
    );
    $this->assertCorrectTitle(1, 1);
    $this->assertEquals(
      'https://example.com/dkan-test-distribution-1.csv',
      $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-1-distribution-downloadurl a')->getText()
    );
    // Remove the file from the first distribution.
    $page->find('css', '[id^="edit-field-json-metadata-0-value-distribution-distribution-1-distribution-downloadurl-remove-button"]')->click();
    $assert->statusCodeEquals(200);
    // Add a new remove file URL.
    $page->find('css', '[id^="edit-field-json-metadata-0-value-distribution-distribution-1-distribution-downloadurl-file-url-remote"]')->setValue('https://example.com/dkan-test-distribution-2.csv');
    // Now move the second distribution to the first position.
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-1-distribution-actions-move-up"]')->click();
    // Assert that the title and URL of the second distribution are now in the first position.
    $this->assertCorrectTitle(0, 1);
    $this->assertEquals(
      'https://example.com/dkan-test-distribution-2.csv',
      $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl a')->getText()
    );
    // Assert that the title and URL of the first distribution are now in the second position.
    $this->assertCorrectTitle(1, 0);
    $this->assertEquals(
      'https://example.com/dkan-test-distribution-0.csv',
      $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-1-distribution-downloadurl a')->getText()
    );
    // Submit the form, reopen and make sure the changes are saved.
    $this->submitForm([], 'Save');
    $assert->statusCodeEquals(200);
    $this->drupalGet($edit_url);
    $this->assertCorrectTitle(0, 1);
    $this->assertEquals(
      'https://example.com/dkan-test-distribution-2.csv',
      $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl a')->getText()
    );
    $this->assertCorrectTitle(1, 0);
    $this->assertEquals(
      'https://example.com/dkan-test-distribution-0.csv',
      $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-1-distribution-downloadurl a')->getText()
    );
  }

  /**
   * Asserts that the title of a distribution is correct.
   *
   * @param int $elementIndex
   *   The index of the element to check.
   * @param int $titleIndex
   *   The index of the title to check.
   */
  protected function assertCorrectTitle(int $elementIndex, int $titleIndex) {
    $expected_title = sprintf('DKANTEST distribution %d title text', $titleIndex);
    $selector = sprintf('[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-%d-distribution-title"]', $elementIndex);
    $this->assertEquals($expected_title, $this->getSession()->getPage()->find('css', $selector)->getValue());
  }

}