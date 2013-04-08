<?php

/**
 * Views argument handler class for handling all non-fulltext types.
 */
class SearchApiViewsHandlerArgument extends views_handler_argument {

  /**
   * The associated views query object.
   *
   * @var SearchApiViewsQuery
   */
  public $query;

  /**
   * Determine if the argument can generate a breadcrumb
   *
   * @return boolean
   */
  // @todo Change and implement set_breadcrumb()?
  public function uses_breadcrumb() {
    return FALSE;
  }

  /**
   * Provide a list of default behaviors for this argument if the argument
   * is not present.
   *
   * Override this method to provide additional (or fewer) default behaviors.
   */
  public function default_actions($which = NULL) {
    $defaults = array(
      'ignore' => array(
        'title' => t('Display all values'),
        'method' => 'default_ignore',
        'breadcrumb' => TRUE, // generate a breadcrumb to here
      ),
      'not found' => array(
        'title' => t('Hide view / Page not found (404)'),
        'method' => 'default_not_found',
        'hard fail' => TRUE, // This is a hard fail condition
      ),
      'empty' => array(
        'title' => t('Display empty text'),
        'method' => 'default_empty',
        'breadcrumb' => TRUE, // generate a breadcrumb to here
      ),
      'default' => array(
        'title' => t('Provide default argument'),
        'method' => 'default_default',
        'form method' => 'default_argument_form',
        'has default argument' => TRUE,
        'default only' => TRUE, // this can only be used for missing argument, not validation failure
      ),
    );

    if ($which) {
      return isset($defaults[$which]) ? $defaults[$which] : NULL;
    }
    return $defaults;
  }

  public function option_definition() {
    $options = parent::option_definition();

    $options['break_phrase'] = array('default' => FALSE);

    return $options;
  }

  public function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    // Allow passing multiple values.
    $form['break_phrase'] = array(
      '#type' => 'checkbox',
      '#title' => t('Allow multiple values'),
      '#description' => t('If selected, users can enter multiple values in the form of 1+2+3 (for OR) or 1,2,3 (for AND).'),
      '#default_value' => $this->options['break_phrase'],
      '#fieldset' => 'more',
    );
  }

  /**
   * Set up the query for this argument.
   *
   * The argument sent may be found at $this->argument.
   */
  // @todo Provide options to select the operator, instead of always using '='?
  public function query($group_by = FALSE) {
    if (!empty($this->options['break_phrase'])) {
      views_break_phrase($this->argument, $this);
    }
    else {
      $this->value = array($this->argument);
    }

    if (count($this->value) > 1) {
      $filter = $this->query->createFilter(drupal_strtoupper($this->operator));
      // $filter will be NULL if there were errors in the query.
      if ($filter) {
        foreach ($this->value as $value) {
          $filter->condition($this->real_field, $value, '=');
        }
        $this->query->filter($filter);
      }
    }
    else {
      $this->query->condition($this->real_field, reset($this->value));
    }
  }

  /**
   * Get the title this argument will assign the view, given the argument.
   *
   * This usually needs to be overridden to provide a proper title.
   */
  public function title() {
    return t('Search @field for "@arg"', array('@field' => $this->definition['title'], '@arg' => $this->argument));
  }

}
