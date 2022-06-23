<?php

namespace Drupal\Tests\common\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;

/**
 * Full generic test suite for any config form.
 *
 * @see https://git.drupalcode.org/project/virtualcare/-/blob/8.x-1.x
 *   For original source class.
 */
abstract class ConfigFormTestBase extends KernelTestBase {

  /**
   * Form ID to use for testing.
   *
   * @var \Drupal\Core\Form\FormInterface
   */
  protected $form;

  /**
   * Values to use for testing.
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
   * @var array
   */
  protected $values;

  /**
   * Test submitting config form ensure the configuration has expected values.
   */
  public function testConfigForm() {
    // Programmatically submit the given values.
    $values = [];
    foreach ($this->values as $form_key => $data) {
      $values[$form_key] = $data['#value'];
    }
    $form_state = (new FormState())->setValues($values);
    \Drupal::formBuilder()->submitForm($this->form, $form_state);

    // Check that the form returns an error when expected, and vice versa.
    $errors = $form_state->getErrors();
    $valid_form = empty($errors);
    $args = [
      print_r($values, TRUE),
      $valid_form ? 'None' : implode(' ', $errors),
    ];
    $this->assertTrue($valid_form, sprintf('Input values: %s<br/>Validation handler errors: %s', $args));

    foreach ($this->values as $data) {
      $this->assertEquals($data['#value'], $this->config($data['#config_name'])->get($data['#config_key']));
    }
  }

}
