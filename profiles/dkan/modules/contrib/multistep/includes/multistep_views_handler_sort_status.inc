<?php

/**
 * Field handler that returns the completion status of a multistep node.
 */
class multistep_views_handler_sort_status extends views_handler_sort {
  /**
   * Called to add the sort to a query.
   */
  function query() {
    // Add the field.
    $this->query->add_orderby('multistep', 'status', $this->options['order']);
  }

  function option_definition() {
    $options = parent::option_definition();

    $options['order'] = array('default' => 'ASC');

    return $options;
  }

  /**
   * Display whether or not the sort order is ascending or descending
   */
  function admin_summary() {
    switch ($this->options['order']) {
      case 'ASC':
      case 'asc':
      default:
        $type = t('Incomplete last');
        break;
      case 'DESC';
      case 'desc';
      $type = t('Incomplete first');
      break;
    }
    return '<span class="views-ascending"><span>' . $type . '</span></span>';
  }

  /**
   * Basic options for all sort criteria
   */
  function options_form(&$form, &$form_state) {
    $form['order'] = array(
      '#type' => 'radios',
      '#title' => t('Sort order'),
      '#options' => array('DESC' => t('Incomplete at the top'), 'ASC' => t('Incomplete at the bottom')),
      '#default_value' => $this->options['order'],
    );
  }
}
