<?php

/**
 * @file
 * Display gravatar
 */

class gravatar_views_handler_gravatar extends views_handler_field {

  function construct() {
    parent::construct();
    $this->additional_fields['mail'] = 'mail';
    $this->additional_fields['picture'] = 'picture';
  }

  function option_definition() {
    $options = parent::option_definition();

    $options['picture'] = array('default' => FALSE, 'bool' => TRUE);
    return $options;
  }

  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);
    $form['picture'] = array(
      '#type' => 'checkbox',
      '#default_value' => $this->options['picture'],
      '#title' => t('Override with uploaded user picture?'),
      '#description' => t('Use user picture instead of Gravatar if a user has uploaded their own picture.'),
    );
  }

  function render($values) {
    if(isset($values->users_mail)) {
      if ($this->options['picture'] && $values->users_picture) {
        $file = file_load($values->users_picture);
        $path = file_create_url($file->uri);
      }
      else {
        $path = gravatar_get_gravatar($values->users_mail);
      }
      $output = theme('image', array('path' => $path));
      return $output;
    }
  }
}
