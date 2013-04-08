<?php

/**
 * Simple custom text box.
 */
class boxes_simple extends boxes_box {
  /**
   * Implementation of boxes_box::options_defaults().
   */
  public function options_defaults() {
    return array(
      'body' => array(
        'value' => '',
        'format' => filter_default_format(),
      ),
    );
  }

  /**
   * Implementation of boxes_box::options_form().
   */
  public function options_form(&$form_state) {
    $format = filter_format_load($this->options['body']['format']);

    if (filter_access($format)) {
      $form = array();
      $form['body'] = array(
        '#type' => 'text_format',
        '#base_type' => 'textarea',
        '#title' => t('Box body'),
        '#default_value' => $this->options['body']['value'],
        '#rows' => 6,
        '#format' => $this->options['body']['format'] ? $this->options['body']['format'] : NULL,
        '#description' => t('The content of the block as shown to the user.'),
      );
      return $form;
    }
  }

  /**
   * Implementation of boxes_box::render().
   */
  public function render() {
    $content = '';
    if (!empty($this->options['body']['value']) && isset($this->options['body']['format'])) {
      $content = check_markup($this->options['body']['value'], $this->options['body']['format'], $langcode = '' /* TODO Set this variable. */, FALSE);
    }
    $title = isset($this->title) ? $this->title : NULL;
    return array(
      'delta' => $this->delta, // Crucial.
      'title' => $title,
      'subject' => check_plain($title),
      'content' => $content,
    );
  }
}
