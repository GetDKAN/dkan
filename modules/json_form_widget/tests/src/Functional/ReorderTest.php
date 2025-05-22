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

  const MOVE_UP = 'move-up';
  const MOVE_DOWN = 'move-down';
  const REMOVE = 'remove';

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
    $this->addDistribution();
    $assert->statusCodeEquals(200);
    // Now we have two distributions.
    $this->assertDistributionExists(0, TRUE);
    $this->assertDistributionExists(1, TRUE);
    $this->distributionAction(0, self::REMOVE);
    // Now we have one again.
    $this->assertDistributionExists(0, TRUE);
    $this->assertDistributionExists(1, FALSE);

    // Add a distribution again.
    $this->addDistribution();
    $assert->statusCodeEquals(200);
    // Enter a title and remote URL for each
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-0-distribution-title"]')
      ->setValue('DKANTEST distribution 0 title text');
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl-file-url-remote"]')
      ->setValue('https://example.com/dkan-test-distribution-0.csv');
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-1-distribution-title"]')
      ->setValue('DKANTEST distribution 1 title text');
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-1-distribution-downloadurl-file-url-remote"]')
      ->setValue('https://example.com/dkan-test-distribution-1.csv');

    // Now move the first distribution to the second position.
    $this->distributionAction(0, self::MOVE_DOWN);

    // Assert that the title and URL of the first distribution is now in the second position.
    $this->assertCorrectTitle(1, 0);
    // Note: this should really show the full URL, the theme logic that controls
    // this depends on file usage being in place so fails when on a new dataset
    // form.
    $this->assertCorrectFileName(1, 0, FALSE);

    // Assert that the title and URL of the second distribution is now in the first position.
    $this->assertCorrectTitle(0, 1);
    $this->assertCorrectFileName(0, 1, FALSE);
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
    $this->distributionAction(1, self::MOVE_UP);
    // Assert that the title and URL of the original first distribution is
    // now back in the first position.
    $this->assertCorrectTitle(0, 0);
    $this->assertCorrectFileName(0, 0, TRUE);
    // Get the URL to edit this dataset, so we find our way back later.
    $edit_url = $this->getSession()->getCurrentUrl();
    $this->submitForm([], 'Save');
    $assert->statusCodeEquals(200);
    $this->drupalGet($edit_url);
    $this->assertCorrectTitle(0, 0);
    $this->assertCorrectFileName(0, 0, TRUE);
    $this->assertCorrectTitle(1, 1);
    $this->assertCorrectFileName(1, 1, TRUE);

    // SCENARIO THREE: REMOVE DISTRIBUTION, ADD NEW DISTRIBUTION

    $this->drupalGet($edit_url);
    $this->distributionAction(1, self::REMOVE);
    $assert->statusCodeEquals(200);
    $this->assertDistributionExists(1, FALSE);
    $this->addDistribution();
    $assert->statusCodeEquals(200);
    // Assert that the title and URL fields are empty.
    $this->assertFieldEmpty(1, 'title');
    $this->assertFieldEmpty(1, 'downloadurl-file-url-remote');
    // There is no managed file.
    $this->assertNull(
      $page->find('css', '#edit-field-json-metadata-0-value-distribution-distribution-1-distribution-downloadurl a')
    );

    // SCENARIO FOUR: ADD NEW DISTRIBUTION AND REORDER

    $this->drupalGet($edit_url);
    $this->assertCorrectTitle(0, 0);
    $this->assertCorrectFileName(0, 0, TRUE);
    $this->assertCorrectTitle(1, 1);
    $this->assertCorrectFileName(1, 1, TRUE);
    // Add a new distribution.
    $this->addDistribution();
    $assert->statusCodeEquals(200);
    // Assert that the title and URL fields are empty.
    $this->assertFieldEmpty(2, 'title');
    $this->assertFieldEmpty(2, 'downloadurl-file-url-remote');
    // Add a title and URL.
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-2-distribution-title"]')
      ->setValue('DKANTEST distribution 2 title text');
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-2-distribution-downloadurl-file-url-remote"]')
      ->setValue('https://example.com/dkan-test-distribution-2.csv');
    // Now move the third distribution to the second position.
    $this->distributionAction(2, self::MOVE_UP);
    // Assert that the title and URL of the third distribution are now in the
    // second position.
    $this->assertCorrectTitle(1, 2);
    $this->assertCorrectFileName(1, 2, TRUE);
    // Move the thrd distribution back down to the third position, and assert
    // that now the original third distribution values are back in the third
    // position.
    $this->distributionAction(1, self::MOVE_DOWN);
    $this->assertCorrectTitle(2, 2);
    $this->assertCorrectFileName(2, 2, TRUE);

    // Reset, and now we're going to move it up two positions and back down
    // again.
    $this->drupalGet($edit_url);
    $this->addDistribution();
    $assert->statusCodeEquals(200);
    // Assert that the title and URL fields are empty.
    $this->assertFieldEmpty(2, 'title');
    $this->assertFieldEmpty(2, 'downloadurl-file-url-remote');
    // Add a title and URL.
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-2-distribution-title"]')
      ->setValue('DKANTEST distribution 2 title text');
    $page->find('css', '[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-2-distribution-downloadurl-file-url-remote"]')
      ->setValue('https://example.com/dkan-test-distribution-2.csv');
    // Now move the third distribution to the first position.
    $this->distributionAction(2, self::MOVE_UP);
    $this->distributionAction(1, self::MOVE_UP);
    // Assert that the title and URL of the third distribution are now in the
    // first position, and the other two moved one down.
    $this->assertCorrectTitle(0, 2);
    $this->assertCorrectFileName(0, 2, TRUE);
    $this->assertCorrectTitle(1, 0);
    $this->assertCorrectFileName(1, 0, TRUE);
    $this->assertCorrectTitle(2, 1);
    $this->assertCorrectFileName(2, 1, TRUE);

    // Move the first distribution back down to the third position, and assert
    // that now the original first distribution values are back in the first
    // position.
    $this->distributionAction(0, self::MOVE_DOWN);
    $this->distributionAction(1, self::MOVE_DOWN);
    $this->assertCorrectTitle(0, 0);
    $this->assertCorrectFileName(0, 0, TRUE);
    $this->assertCorrectTitle(1, 1);
    $this->assertCorrectFileName(1, 1, TRUE);
    $this->assertCorrectTitle(2, 2);
    $this->assertCorrectFileName(2, 2, TRUE);

    // SCENARIO FIVE: REMOVE FILE, REPLACE URL AND REORDER BEFORE SAVING

    $this->drupalGet($edit_url);
    $this->assertCorrectTitle(0, 0);
    $this->assertCorrectFileName(0, 0, TRUE);
    $this->assertCorrectTitle(1, 1);
    $this->assertCorrectFileName(1, 1, TRUE);
    // Remove the file from the first distribution.
    $page->find('css', '[id^="edit-field-json-metadata-0-value-distribution-distribution-1-distribution-downloadurl-remove-button"]')->click();
    $assert->statusCodeEquals(200);
    // Add a new remove file URL.
    $page->find('css', '[id^="edit-field-json-metadata-0-value-distribution-distribution-1-distribution-downloadurl-file-url-remote"]')->setValue('https://example.com/dkan-test-distribution-2.csv');
    // Now move the second distribution to the first position.
    $this->distributionAction(1, self::MOVE_UP);
    // Assert that the title and URL of the second distribution are now in the first position.
    $this->assertCorrectTitle(0, 1);
    $this->assertCorrectFileName(0, 2, TRUE);
    // Assert that the title and URL of the first distribution are now in the second position.
    $this->assertCorrectTitle(1, 0);
    $this->assertCorrectFileName(1, 0, TRUE);
    // Submit the form, reopen and make sure the changes are saved.
    $this->submitForm([], 'Save');
    $assert->statusCodeEquals(200);
    $this->drupalGet($edit_url);
    $this->assertCorrectTitle(0, 1);
    $this->assertCorrectFileName(0, 2, TRUE);
    $this->assertCorrectTitle(1, 0);
    $this->assertCorrectFileName(1, 0, TRUE);
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

  /**
   * Asserts that the file name in the download URL is correct.
   *
   * Note we need to deal with occasionally having just the filename, due to
   * issues with the theme logic for the managed file field.
   *
   * @param int $elementIndex
   *   The index of the element to check.
   * @param int $filenameIndex
   *   The index of the file name to check.
   * @param bool $fullUrl
   *   Whether the full URL is expected.
   */
  protected function assertCorrectFileName(int $elementIndex, int $filenameIndex, bool $fullUrl = FALSE) {
    $expectedFileName = sprintf('dkan-test-distribution-%d.csv', $filenameIndex);
    if ($fullUrl === TRUE) {
      $expectedFileName = 'https://example.com/' . $expectedFileName;
    }
    $selector = sprintf('#edit-field-json-metadata-0-value-distribution-distribution-%d-distribution-downloadurl a', $elementIndex);
    $this->assertEquals($expectedFileName, $this->getSession()->getPage()->find('css', $selector)->getText());
  }

  /**
   * Asserts that a field is empty.
   *
   * @param int $elementIndex
   *   The index of the element to check.
   * @param string $nameFragment
   *   The fragment of the field name (e.g., "title" or "downloadurl-file-url-remote").
   */
  protected function assertFieldEmpty(int $elementIndex, string $nameFragment) {
    $selector = sprintf('[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-%d-distribution-%s"]', $elementIndex, $nameFragment);
    $this->assertEquals('', $this->getSession()->getPage()->find('css', $selector)->getValue());
  }

  /**
   * Asserts whether a distribution element exists.
   *
   * @param int $elementIndex
   *   The index of the distribution element to check.
   * @param bool $exists
   *   Whether the distribution element should exist.
   */
  protected function assertDistributionExists(int $elementIndex, bool $exists) {
    $selector = sprintf('[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-%d-distribution"]', $elementIndex);
    if ($exists === TRUE) {
      $this->assertNotNull($this->getSession()->getPage()->find('css', $selector));
    }
    else {
      $this->assertNull($this->getSession()->getPage()->find('css', $selector));
    }
  }

  /**
   * Adds a new distribution by clicking the add button.
   */
  protected function addDistribution() {
    $this->getSession()->getPage()->find('css', '[id^="edit-field-json-metadata-0-value-distribution-array-actions-actions-add"]')->click();
  }

  /**
   * Performs an action on a distribution element.
   *
   * @param int $elementIndex
   *   The index of the distribution element.
   * @param string $action
   *   The action to perform. Must be one of the defined constants.
   */
  protected function distributionAction(int $elementIndex, string $action) {
    $selector = sprintf('[data-drupal-selector="edit-field-json-metadata-0-value-distribution-distribution-%d-distribution-actions-%s"]', $elementIndex, $action);
    $button = $this->getSession()->getPage()->find('css', $selector);
    if ($button) {
      $button->click();
    }
    else {
      throw new \Exception(sprintf('Action button for element %d and action %s not found.', $elementIndex, $action));
    }
  }

}