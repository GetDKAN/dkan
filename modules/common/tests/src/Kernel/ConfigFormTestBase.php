<?php

namespace Drupal\Tests\common\Kernel;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;

/**
 * Generic base config form test.
 *
 * @see Drupal\KernelTests\ConfigFormTestBase
 *   For source of inspiration.
 * @see \Drupal\Tests\metastore\Kernel\DataDictionarySettingsFormTest
 *   For an example implementation.
 */
abstract class ConfigFormTestBase extends KernelTestBase {

  /**
   * Form to use for testing.
   *
   * @var \Drupal\Core\Form\FormInterface
   */
  protected $form;

  /**
   * Form data provider.
   *
   * Contains details for form key, configuration object name, and config key.
   * Example:
   * @code
   *   array(
   *     'user_mail_cancel_confirm_body' => array(
   *       '#value' => $this->randomString(),
   *       '#config_name' => 'user.mail',
   *       '#config_key' => 'cancel_confirm.body',
   *     ),
   *   );
   * @endcode
   *
   * @return array[]
   *   Form test data.
   */
  abstract public function provideFormData(): array;

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

}
