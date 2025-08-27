<?php

namespace Drupal\Tests\data_dictionary_widget\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Data_Dictionary_Widget test.
 *
 * @coversDefaultClass \Drupal\data_dictionary_widget\Plugin\Field\FieldWidget\DataDictionaryWidget
 *
 * @group data_dictionary_widget
 * @group functional
 * @group functional1
 */
class DataDictionaryWidgetTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'data_dictionary_widget',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'claro';

  /**
   * {@inheritdoc}
   *
   * Set strictConfigSchema to FALSE, so that we don't end up checking the
   * config schema of contrib dependencies.
   */
  protected $strictConfigSchema = FALSE;

  /**
   * Test the behavior of the Data-Dictionary-Widget.
   */
  public function testDataDictionaryWidgetBehavior() {
    $this->drupalLogin(
        $this->drupalCreateUser([], NULL, TRUE)
      );

    $session = $this->assertSession();
    $this->drupalGet('node/add/data', ['query' => ['schema' => 'data-dictionary']]);
    $session->addressEquals('node/add/data?schema=data-dictionary');
    $this->assertSession()->statusCodeEquals(200);

    $page = $this->getSession()->getPage();
    $page->find('css', '[id^="edit-title-0-value"]')->setValue('Test Dictionary');
    $page->find('css', '[id^="edit-field-json-metadata-0-title"]')->setValue('Test Dictionary');

    // Add a new field to the dictionary.
    $page->find('css', '[id^="edit-field-json-metadata-0-dictionary-fields-add-row-button"]')->click();
    $page->find('css', '[id^="edit-field-json-metadata-0-dictionary-fields-field-collection-group-name"]')->setValue('Test Name');
    $page->find('css', '[id^="edit-field-json-metadata-0-dictionary-fields-field-collection-group-title"]')->setValue('Test Title');
    $page->find('css', '[id^="edit-field-json-metadata-0-dictionary-fields-field-collection-group-description"]')->setValue('Test Desc');
    $page->find('css', '[id^="edit-field-json-metadata-0-dictionary-fields-field-collection-group-actions-save-settings"]')->click();

    // Edit the description field and confirm it has updated.
    $page->find('css', '[id^="edit_0"]')->press();
    $page->find('css', '[id^="edit-field-json-metadata-0-dictionary-fields-edit-fields-0-update-field-actions-save-update"]');
    $page->find('css', '[id^="edit-field-json-metadata-0-dictionary-fields-edit-fields-0-update-field-actions-cancel-updates"]');
    $page->find('css', '[id^="edit-field-json-metadata-0-dictionary-fields-edit-fields-0-update-field-actions-delete-field"]');
    $page->find('css', '[id^="edit-field-json-metadata-0-dictionary-fields-edit-fields-0-description"]')->setValue('Test Desc Update');
    $page->find('css', '[id^="edit-field-json-metadata-0-dictionary-fields-edit-fields-0-update-field-actions-save-update"]')->press();
    $tableValue = $page->find('css', '[id^="field-json-metadata-dictionary-edit-field"]')->getText();
    $this->assertSame('Test Name Test Title Data Type: string Format: default Description: Test Desc Update', $tableValue, "Expected text not found in tbody.");

    // Confirm successful save.
    $page->find('css', '[id^="edit-submit"]')->click();
    $this->assertSession()->pageTextContains('Data Test Dictionary has been created');
  }

}
