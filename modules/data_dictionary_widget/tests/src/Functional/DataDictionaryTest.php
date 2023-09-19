<?php

namespace Drupal\Tests\data_dictionary_widget\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Data_Dictionary_Widget test.
 * 
 * @covers \Drupal\data_dictionary_widget\Plugin\Field\FieldWidget\DataDictionaryWidget
 * @coversDefaultClass \Drupal\data_dictionary_widget\Plugin\Field\FieldWidget\DataDictionaryWidget
 * 
 * @group dkan
 * @group functional
 */
class DataDictionaryTest extends BrowserTestBase {

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
    $permissions = ['administer data dictionary settings', 'create data content', 'administer site configuration', 'administer content types', 'bypass node access'];
    $this->drupalLogin($this->drupalCreateUser($permissions));
    $session = $this->assertSession();

    $this->drupalGet('node/add/data', ['query' => ['schema' => 'data-dictionary']]);
    $this->assertSession()->statusCodeEquals(200);
    $session->addressEquals('node/add/data?schema=data-dictionary');
    $session->elementTextContains('css', '.page-title', 'Create Data');
    $session->elementExists('css', '.field--widget-data-dictionary-widget');
    $session->elementExists('css', '#edit-field-json-metadata-0-identifier');
    $session->elementExists('css', '#edit-field-json-metadata-0-title');

  }

}
