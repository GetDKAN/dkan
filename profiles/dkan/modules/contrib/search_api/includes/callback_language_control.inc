<?php

/**
 * Search API data alteration callback that filters out items based on their
 * bundle.
 */
class SearchApiAlterLanguageControl extends SearchApiAbstractAlterCallback {

  /**
   * Construct a data-alter callback.
   *
   * @param SearchApiIndex $index
   *   The index whose items will be altered.
   * @param array $options
   *   The callback options set for this index.
   */
  public function __construct(SearchApiIndex $index, array $options = array()) {
    $options += array(
      'lang_field' => '',
      'languages' => array(),
    );
    parent::__construct($index, $options);
  }

  /**
   * Check whether this data-alter callback is applicable for a certain index.
   *
   * Only returns TRUE if the system is multilingual.
   *
   * @param SearchApiIndex $index
   *   The index to check for.
   *
   * @return boolean
   *   TRUE if the callback can run on the given index; FALSE otherwise.
   *
   * @see drupal_multilingual()
   */
  public function supportsIndex(SearchApiIndex $index) {
    return drupal_multilingual();
  }

  /**
   * Display a form for configuring this data alteration.
   *
   * @return array
   *   A form array for configuring this data alteration.
   */
  public function configurationForm() {
    $form = array();

    $wrapper = $this->index->entityWrapper();
    $fields[''] = t('- Use default -');
    foreach ($wrapper as $key => $property) {
      if ($key == 'search_api_language') {
        continue;
      }
      $type = $property->type();
      // Only single-valued string properties make sense here. Also, nested
      // properties probably don't make sense.
      if ($type == 'text' || $type == 'token') {
        $info = $property->info();
        $fields[$key] = $info['label'];
      }
    }

    if (count($fields) > 1) {
      $form['lang_field'] = array(
        '#type' => 'select',
        '#title' => t('Language field'),
        '#description' => t("Select the field which should be used to determine an item's language."),
        '#options' => $fields,
        '#default_value' => $this->options['lang_field'],
      );
    }

    $languages[LANGUAGE_NONE] = t('Language neutral');
    $list = language_list('enabled') + array(array(), array());
    foreach (array($list[1], $list[0]) as $list) {
      foreach ($list as $lang) {
        $name = t($lang->name);
        $native = $lang->native;
        $languages[$lang->language] = ($name == $native) ? $name : "$name ($native)";
        if (!$lang->enabled) {
          $languages[$lang->language] .= ' [' . t('disabled') . ']';
        }
      }
    }
    $form['languages'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Indexed languages'),
      '#description' => t('Index only items in the selected languages. ' .
          'When no language is selected, there will be no language-related restrictions.'),
      '#options' => $languages,
      '#default_value' => $this->options['languages'],
    );

    return $form;
  }

  /**
   * Submit callback for the form returned by configurationForm().
   *
   * This method should both return the new options and set them internally.
   *
   * @param array $form
   *   The form returned by configurationForm().
   * @param array $values
   *   The part of the $form_state['values'] array corresponding to this form.
   * @param array $form_state
   *   The complete form state.
   *
   * @return array
   *   The new options array for this callback.
   */
  public function configurationFormSubmit(array $form, array &$values, array &$form_state) {
    $values['languages'] = array_filter($values['languages']);
    return parent::configurationFormSubmit($form, $values, $form_state);
  }

  /**
   * Alter items before indexing.
   *
   * Items which are removed from the array won't be indexed, but will be marked
   * as clean for future indexing. This could for instance be used to implement
   * some sort of access filter for security purposes (e.g., don't index
   * unpublished nodes or comments).
   *
   * @param array $items
   *   An array of items to be altered, keyed by item IDs.
   */
  public function alterItems(array &$items) {
    foreach ($items as $i => &$item) {
      // Set item language, if a custom field was selected.
      if ($field = $this->options['lang_field']) {
        $wrapper = $this->index->entityWrapper($item);
        if (isset($wrapper->$field)) {
          try {
            $item->search_api_language = $wrapper->$field->value();
          }
          catch (EntityMetadataWrapperException $e) {
            // Something went wrong while accessing the language field. Probably
            // doesn't really matter.
          }
        }
      }
      // Filter out items according to language, if any were selected.
      if ($languages = $this->options['languages']) {
        if (empty($languages[$item->search_api_language])) {
          unset($items[$i]);
        }
      }
    }
  }

}
