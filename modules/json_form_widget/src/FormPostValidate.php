<?php

namespace Drupal\json_form_widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\NestedArray;

/**
 * Post validation.
 */
class FormPostValidate {

  /**
   * Handles the post-constraint validation event.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function onPostConstraintValidate(array &$form, FormStateInterface $form_state) {
    if (empty($errors = $form_state->getErrors())) {
      return;
    }

    $form_state->clearErrors();

    $form['#attached']['library'][] = 'json_form_widget/style';

    foreach ($errors as $error) {
      $message = $error->__toString();
      $field_name_json = $error->getArguments()['json_field_pointer'];
      $field_name = json_decode($field_name_json, TRUE);

      $full_path = ['field_json_metadata', 'widget', 0, 'value'];
      $full_path = array_merge($full_path, $field_name);

      $element = &NestedArray::getValue($form, $full_path, $key_exists);

      if ($key_exists && is_array($element)) {
        $form_state->setError($element, $message);
      }
    }
  }

}