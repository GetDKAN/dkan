<?php

namespace Drupal\Tests\metastore\Kernel;

use Drupal\Tests\common\Kernel\ConfigFormTestBase;
use Drupal\metastore\Form\DataDictionarySettingsForm;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;
use Drupal\Core\Form\FormState;
use Drupal\Component\Render\FormattableMarkup;

/**
 * Data Dictionary Settings Form class test.
 *
 * @group Form
 */
class DataDictionarySettingsFormTest extends ConfigFormTestBase {

  /**
   * Modules to enable.
   *
   * @var string[]
   */
  public static $modules = ['metastore', 'common', 'node', 'user'];

  /**
   * {@inheritdoc}
   */
  public function provideFormData(): array {
    return [
      [
        [
          'dictionary_mode' => [
            '#value' => DataDictionaryDiscoveryInterface::MODE_SITEWIDE,
            '#config_name' => DataDictionarySettingsForm::SETTINGS,
            '#config_key' => 'data_dictionary_mode',
          ],
          'sitewide_dictionary_id' => [
            '#value' => 'test',
            '#config_name' => DataDictionarySettingsForm::SETTINGS,
            '#config_key' => 'data_dictionary_sitewide',
          ],
        ],
      ],
      [
        [
          'dictionary_mode' => [
            '#value' => DataDictionaryDiscoveryInterface::MODE_NONE,
            '#config_name' => DataDictionarySettingsForm::SETTINGS,
            '#config_key' => 'data_dictionary_mode',
          ],
        ],
      ],
    ];
  }

    /**
   * Test submitting config form.
   *
   * @param array $form_values
   *   Form values to test.
   *
   * @dataProvider provideFormData
   */
  public function testConfigForm(array $form_values) {
    // Programmatically submit the given values.
    foreach ($form_values as $form_key => $data) {
      $values[$form_key] = $data['#value'];
    }
    $form_state = (new FormState())->setValues($values);
    \Drupal::formBuilder()->submitForm($this->form, $form_state);

    // Check that the form returns an error when expected, and vice versa.
    $errors = $form_state->getErrors();
    $valid_form = empty($errors);
    $args = [
      '%values' => print_r($values, TRUE),
      '%errors' => $valid_form ? t('None') : implode(' ', $errors),
    ];
    $this->assertTrue($valid_form, new FormattableMarkup('Input values: %values<br/>Validation handler errors: %errors', $args));

    foreach ($form_values as $data) {
      $this->assertEquals($data['#value'], $this->config($data['#config_name'])->get($data['#config_key']));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->form = new DataDictionarySettingsForm($this->container->get('config.factory'), $this->container->get('messenger'), $this->container->get('dkan.metastore.service'));
  }

}
