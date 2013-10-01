<?php
/**
 * @file
 * Implements views_plugin_style for views_json
 */

/**
 * Implements views_plugin_style
 */
class views_plugin_style_json extends views_plugin_style {
  /**
   * Implements views_plugin_style::option_definition
   */
  function option_definition() {
    $options = parent::option_definition();
    $options['root_object'] = array('default' => 'nodes', 'translatable' => FALSE);
    $options['top_child_object'] = array('default' => 'node', 'translatable' => FALSE);
    $options['field_output'] = array('default' => 'normal', 'translatable' => FALSE);
    $options['plaintext_output'] = array('default' => TRUE, 'translatable' => FALSE);
    $options['remove_newlines'] = array('default' => NULL, 'translatable' => FALSE);
    $options['format'] = array('default' => 'simple', 'translatable' => FALSE);
    $options['jsonp_prefix'] = array('default' => NULL, 'translatable' => FALSE);
    $options['content_type'] = array('default' => 'default', 'translatable' => FALSE);
    $options['using_views_api_mode'] = array('default' => TRUE, 'translatable' => FALSE);
    
    // Encoding options, to be used by json_encode().
    $options['encoding'] = array('contains' => array(
      'object_arrays' => array('default' => NULL, 'translatable' => FALSE, 'bitmask' => 'JSON_FORCE_OBJECT'),
      'numeric_strings' => array('default' => NULL, 'translatable' => FALSE, 'bitmask' => 'JSON_NUMERIC_CHECK'),
      'bigint_string' =>array('default' => NULL, 'translatable' => FALSE, 'bitmask' => 'JSON_BIGINT_AS_STRING'),
      'pretty_print' =>array('default' => NULL, 'translatable' => FALSE, 'bitmask' => 'JSON_PRETTY_PRINT'),
      'unescaped_slashes' => array('default' => NULL, 'translatable' => FALSE, 'bitmask' => 'JSON_UNESCAPED_SLASHES'),
      'unescaped_unicode' => array('default' => NULL, 'translatable' => FALSE, 'bitmask' => 'JSON_UNESCAPED_UNICODE'),
      'char_encoding' => array('default' => NULL, 'translatable' => FALSE, 'bitmasks' => array(
          'JSON_HEX_TAG' => 'JSON_HEX_TAG', 'JSON_HEX_APOS' => 'JSON_HEX_APOS', 'JSON_HEX_QUOT' => 'JSON_HEX_QUOT', 'JSON_HEX_AMP' => 'JSON_HEX_AMP'
        )
      ),
    ));
    // Provide Views a flatted copy of 'encoding' in a format it knows how to save and retrieve user data to.
    foreach($options['encoding']['contains'] as $key => $encoding_option) {
      $options[$key] = $encoding_option;
    }
    return $options;
  }

  /**
   * Provide a form for setting options.
   */
  function options_form(&$form, &$form_state) {
    $form['root_object'] = array(
    '#type' => 'textfield',
    '#title' => t('Root object name'),
    '#default_value' => $this->options['root_object'],
    '#description' => t('The name of the root object in the JSON document. e.g nodes or users or forum_posts'),
      //'#process' => array('views_process_dependency'),
      //'#dependency' => array('radios:schema' => array('raw')),
    );
    $form['top_child_object'] = array(
      '#type' => 'textfield',
      '#title' => t('Top-level child object'),
      '#default_value' => $this->options['top_child_object'],
      '#description' => t('The name of each top-level child object in the JSON document. e.g node or user or forum_post'),
    );
    $form['field_output']  = array(
      '#type' => 'radios',
      '#title' => t('Field output'),
      '#description' => t('For each row in the view, fields can be output as either the field rendered by Views, or by the raw content of the field.'),
      '#options' => array('normal' => t('Normal'), 'raw' => t('Raw')),
      '#default_value' => $this->options['field_output'],
    );
    $form['plaintext_output'] = array(
      '#type' => 'checkbox',
      '#title' => t('Plaintext output'),
      '#default_value' => $this->options['plaintext_output'],
      '#description' => t('For each row in the view, strip all markup from the field output.'),
    );
    $form['remove_newlines'] = array(
      '#type' => 'checkbox',
      '#title' => t('Remove newlines'),
      '#default_value' => $this->options['remove_newlines'],
      '#description' => t('Strip newline characters from the field output.'),
    );
    $form['format'] = array(
      '#type' => 'radios',
      '#title' => t('JSON data format'),
      '#options' => views_json_views_formats(),
      '#default_value' => $this->options['format'],
      '#description' => t('What object format will be used for JSON output.'),
    );
    $form['jsonp_prefix'] = array(
      '#type' => 'textfield',
      '#title' => t('JSONP prefix'),
      '#default_value' => $this->options['jsonp_prefix'],
      '#description' => t('If used the JSON output will be enclosed with parentheses and prefixed by this label, as in the JSONP format.'),
    );

    $form['content_type'] = array(
      '#type' => 'radios',
      '#title' => t('Content-Type'),
      '#options' => array(
        'default' => t('Default: application/json'),
        'text/json' => t('text/json'),
        'application/javascript' => t('application/javascript'),
      ),
      '#default_value' => $this->options['content_type'],
      '#description' => t('The Content-Type header that will be sent with the JSON output.')
    );
    $form['using_views_api_mode'] = array(
      '#type' => 'checkbox',
      '#title' => t('Views API mode'),
      '#default_value' => $this->options['using_views_api_mode'],
      '#description' => t('Not using View API mode means the JSON gets output directly and the server ceases normal page processing.  Using it means the server does not cease processing after outputting the JSON.  This allows the Views API to be used with the view without having to prematurely terminate page processing.'),
    );
    
    // JSON encoding options.
    $form['object_arrays'] = array(
      '#type' => 'checkbox',
      '#title' => t('Object arrays'),
      '#default_value' => $this->options['object_arrays'],
      '#description' => t('Outputs an object rather than an array when a non-associative array is used. Especially useful when the recipient of the output is expecting an object and the array is empty.'),
    );
    $form['numeric_strings'] = array(
      '#type' => 'checkbox',
      '#title' => t('Numeric strings'),
      '#default_value' => $this->options['numeric_strings'],
      '#description' => t('Encodes numeric strings as numbers.'),
    );
    $form['bigint_string'] = array(
      '#type' => 'checkbox',
      '#title' => t('Numeric strings'),
      '#default_value' => $this->options['bigint_string'],
      '#description' => t('Encodes large integers as their original string value.'),
    );
    $form['pretty_print'] = array(
      '#type' => 'checkbox',
      '#title' => t('Pretty print'),
      '#default_value' => $this->options['pretty_print'],
      '#description' => t('Use whitespace in returned data to format it.'),
    );
    $form['unescaped_slashes'] = array(
      '#type' => 'checkbox',
      '#title' => t('Unescaped slashes'),
      '#default_value' => $this->options['unescaped_slashes'],
      '#description' => t("Don't escape forward slashes <b>/</b>."),
    );
    $form['unescaped_unicode'] = array(
      '#type' => 'checkbox',
      '#title' => t('Unescaped unicode'),
      '#default_value' => $this->options['unescaped_unicode'],
      '#description' => t('Encode multibyte Unicode characters literally (default is to escape as \uXXXX). '),
    );
    $form['char_encoding'] = array(
      '#type' => 'select',
      '#title' => t('Hexadecimal (base 16) encoding'),
      '#options' => array(
        'JSON_HEX_TAG' => t('Encode tags'),
        'JSON_HEX_APOS' => t('Encode apostrophe'),
        'JSON_HEX_QUOT' => t('Encode quotes'),
        'JSON_HEX_AMP' => t('Encode ampersand'),
      ),
      '#multiple' => TRUE,
      '#default_value' => $this->options['json_char_encoding'],
      '#description' => t('You can combine multiple options.'),
    );
    
    // Only enable options supported by the current PHP version.
    if(!(PHP_MAJOR_VERSION >= 5 && PHP_MINOR_VERSION >= 4)) {
      $php5_4_ops = array('bigint_string', 'pretty_print', 'unescaped_slashes', 'unescaped_unicode');
      foreach($php5_4_ops as $op) {
        $form[$op]['#disabled'] = TRUE;
        $form[$op]['#description'] .= ' <b>Requires PHP 5.4 or greater</b>.';
      }
    }
  }

  /**
   * Implementation of view_style_plugin::theme_functions(). Returns an array of theme functions to use
   * for the current style plugin
   * @return array
   */
  function theme_functions() {
    $options = $this->options;
    $hook = 'views_views_json_style_' . $options['format'];
    return views_theme_functions($hook, $this->view, $this->display);
  }

  /**
   * Implementation of views_style_plugin::additional_theme_functions(). Returns empty array.
   * @return array
   */
  function additional_theme_functions() {
    return array();
  }

  /**
   * Implementation of view_style_plugin::render()
   */
  function render() {
    $view = $this->view;
    $options = $this->options;
    $field = $view->field;

    $rows = array();
    foreach ($view->result as $count => $row) {
      $view->row_index = $count;
      $rows[] = _views_json_render_fields($view, $row);
    }
    unset($view->row_index);

    return theme($this->theme_functions(), array('view' => $view, 'options' => $options, 'rows' => $rows));
  }
}
