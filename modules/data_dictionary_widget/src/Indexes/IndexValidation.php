<?php

namespace Drupal\data_dictionary_widget\Indexes;

use Drupal\Core\Form\FormStateInterface;

/**
 * Validation of the index fields form.
 */
class IndexValidation {

  /**
   * Validation callback for a index fields form.
   *
   * If index field name and length is empty, validation will trigger.
   *
   * @param array $element
   *   The form element being validated.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $form
   *   The complete form structure.
   */
  public static function indexFieldsValidation(array $element, FormStateInterface $form_state, array &$form) {
    $index_fields_fieldset = $form["field_json_metadata"]["widget"][0]["indexes"]["field_collection"]["group"]["index"]["fields"] ?? NULL;
    $fields = $form["field_json_metadata"]["widget"][0]["indexes"]["fields"]["data"]["#rows"] ?? NULL;

    if ($index_fields_fieldset && !$fields) {
        $form_state->setError($index_fields_fieldset, t('At least one index field is required.'));
    }
  }

}
