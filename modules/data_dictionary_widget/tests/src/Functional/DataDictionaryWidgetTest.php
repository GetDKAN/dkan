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
    $permissions = ['administer data dictionary settings', 'create data content', 'administer site configuration', 'administer content types', 'bypass node access', 'use dkan_publishing transition publish'];
    $this->drupalLogin($this->drupalCreateUser($permissions));
    $session = $this->assertSession();

    $this->drupalGet('node/add/data', ['query' => ['schema' => 'data-dictionary']]);
    $this->assertSession()->statusCodeEquals(200);
    $session->addressEquals('node/add/data?schema=data-dictionary');
    $session->elementTextContains('css', '.page-title', 'Create Data');
    $session->elementExists('css', '.field--widget-data-dictionary-widget');
    $session->elementExists('css', '#edit-field-json-metadata-0-identifier');
    $session->elementExists('css', '#edit-field-json-metadata-0-title');
    $session->elementExists('css', '#edit-field-json-metadata-0-dictionary-fields-add-row-button');
    $session->elementExists('css', '#edit-field-json-metadata-0-indexes-add-row-button');

    $page = $this->getSession()->getPage();

    // Title is not hidden, so we have to fill it some data.
    $page->fillField('title[0][value]', 'Test Data Set');

    // Fill the actual data dictionary title.
    $test_data_dictionary_title = 'Test Data Dictionary';
    $page->fillField('field_json_metadata[0][title]', $test_data_dictionary_title);

    // Fill the data dictionary fields.
    $page->pressButton('Add field');
    $test_data_dictionary_field_name = 'test';
    $test_data_dictionary_field_title = 'Test Title';
    $test_data_dictionary_field_type = 'string';
    $test_data_dictionary_field_format = 'default';
    $test_data_dictionary_field_description = 'Test Description';
    $page->fillField('field_json_metadata[0][dictionary_fields][field_collection][group][name]', $test_data_dictionary_field_name);
    $page->fillField('field_json_metadata[0][dictionary_fields][field_collection][group][title]', $test_data_dictionary_field_title);
    $page->selectFieldOption('field_json_metadata[0][dictionary_fields][field_collection][group][type]', $test_data_dictionary_field_type);
    $page->selectFieldOption('field_json_metadata[0][dictionary_fields][field_collection][group][format]', $test_data_dictionary_field_format);
    $page->fillField('field_json_metadata[0][dictionary_fields][field_collection][group][description]', $test_data_dictionary_field_description);
    $page->pressButton('Add');

    // Assert the values are being displayed.
    $session->pageTextContains($test_data_dictionary_field_name);
    $session->pageTextContains($test_data_dictionary_field_title);
    $session->pageTextContains('Data Type: ' . $test_data_dictionary_field_type);
    $session->pageTextContains('Format: ' . $test_data_dictionary_field_format);
    $session->pageTextContains('Description: ' .  $test_data_dictionary_field_description);

    // Edit data dictionary fields and confirm fields have the same values.
    $page->pressButton('Edit');
    $session->fieldValueEquals('field_json_metadata[0][dictionary_fields][data][0][field_collection][name]', $test_data_dictionary_field_name);
    $session->fieldValueEquals('field_json_metadata[0][dictionary_fields][data][0][field_collection][title]', $test_data_dictionary_field_title);
    $session->fieldValueEquals('field_json_metadata[0][dictionary_fields][data][0][field_collection][type]', $test_data_dictionary_field_type);
    $session->fieldValueEquals('field_json_metadata[0][dictionary_fields][data][0][field_collection][format]', $test_data_dictionary_field_format);
    $session->elementTextContains('css', 'textarea[name="field_json_metadata[0][dictionary_fields][data][0][field_collection][description]"]', $test_data_dictionary_field_description);
    $page->pressButton('Cancel');

    // Fill the indexes fields.
    $page->pressButton('Add index');
    $test_index_title = 'Test Index';
    $test_index_type = 'index';
    $test_index_name = 'test_index';
    $test_index_length = 20;
    $page->fillField('field_json_metadata[0][indexes][field_collection][group][index][description]', $test_index_title);
    $page->selectFieldOption('field_json_metadata[0][indexes][field_collection][group][index][type]', $test_index_type);
    // Add an index field.
    // Use the name to distinguish the button from the data dictionary fields.
    $page->pressButton('add_index_field');
    $page->fillField('field_json_metadata[0][indexes][fields][field_collection][group][index][fields][name]', $test_index_name);
    $page->fillField('field_json_metadata[0][indexes][fields][field_collection][group][index][fields][length]', $test_index_length);
    // Need to distinguish the add button on the index fields vs the one for data dictionary fields.
    $page->pressButton('Save field to index');
    $page->pressButton('Submit Index');

    // Assert the values are being displayed.
    $session->pageTextContains($test_index_title);
    $session->pageTextContains($test_index_type);
    $session->pageTextContains('Field Name: ' . $test_index_name);
    $session->pageTextContains('Field Length: ' . $test_index_length);

    // Edit index and confirm fields have the same values.
    $page->pressButton('Edit index');
    $session->fieldValueEquals('field_json_metadata[0][indexes][edit_index][index_key_0][description]', $test_index_title);
    $session->fieldValueEquals('field_json_metadata[0][indexes][edit_index][index_key_0][type]', $test_index_type);
    $session->pageTextContains($test_index_name);
    $session->pageTextContains($test_index_length);
    $page->pressButton('Cancel Index');

    // Save the data dictionary.
    $page->pressButton('Save');

    $session->pageTextContains($test_data_dictionary_title);
  }

}
