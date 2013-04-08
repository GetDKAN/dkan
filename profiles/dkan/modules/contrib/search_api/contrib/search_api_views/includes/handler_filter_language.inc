<?php

/**
 * @file
 *   Contains the SearchApiViewsHandlerFilterLanguage class.
 */

/**
 * Views filter handler class for handling the special "Item language" field.
 *
 * Definition items:
 * - options: An array of possible values for this field.
 */
class SearchApiViewsHandlerFilterLanguage extends SearchApiViewsHandlerFilterOptions {

  /**
   * Provide a form for setting options.
   */
  public function value_form(&$form, &$form_state) {
    parent::value_form($form, $form_state);
    $form['value']['#options'] = array(
      'current' => t("Current user's language"),
      'default' => t('Default site language'),
    ) + $form['value']['#options'];
  }

  /**
   * Provides a summary of this filter's value for the admin UI.
   */
  public function admin_summary() {
    $tmp = $this->definition['options'];
    $this->definition['options']['current'] = t('current');
    $this->definition['options']['default'] = t('default');
    $ret = parent::admin_summary();
    $this->definition['options'] = $tmp;
    return $ret;
  }

  /**
   * Add this filter to the query.
   */
  public function query() {
    global $language_content;
    foreach ($this->value as $i => $v) {
      if ($v == 'current') {
        $this->value[$i] = $language_content->language;
      }
      elseif ($v == 'default') {
        $this->value[$i] = language_default('language');
      }
    }
    parent::query();
  }

}
