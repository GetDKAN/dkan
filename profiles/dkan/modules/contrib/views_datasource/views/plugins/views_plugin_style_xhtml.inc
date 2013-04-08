<?php
/**
 * @file
 * Implements views_plugin_style for views_xhtml
 */

/**
 * Implements views_plugin_style
 */
class views_plugin_style_xhtml extends views_plugin_style {
  /**
   * Set default options
   */
  function option_definition() {
    $options = parent::option_definition();
    $author_default = isset($GLOBALS['user']->name) ? $GLOBALS['user']->name : variable_get('anonymous', t('Anonymous'));
    $options['format'] = array('default' => 'hcard', 'translatable' => FALSE);
    $options['field_output'] = array('default' => 'normal', 'translatable' => FALSE);
    $options['plaintext_output'] = array('default' => TRUE, 'translatable' => FALSE);
    //$options['skip_empty_fields'] = array('default' => TRUE, 'translatable' => FALSE);
    $options['escape_as_CDATA'] = array('default' => 'no', 'translatable' => FALSE);
    $options['content_type'] = array('default' => 'text/xhtml', 'translatable' => FALSE);
    $options['header'] = array('default' => NULL, 'translatable' => FALSE);
    $options['author'] = array('default' => $author_default, 'translatable' => FALSE);
    $options['using_views_api_mode'] = array('default' => FALSE, 'translatable' => FALSE);
    //$options['action_on_missing_field'] = array('default' => 'none', 'translatable' => FALSE);
    return $options;
  }

  /**
   * Provide a form for setting options.
   */
  function options_form(&$form, &$form_state) {
    $form['format'] = array(
      '#type' => 'radios',
      '#title' => t('Microformat'),
      '#options' => views_xhtml_views_formats(),
      '#default_value' => $this->options['format'],
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
    /*
    $form['skip_empty_fields'] = array(
      '#type'           => 'checkbox',
      '#title'          => t('Skip adding fields that are empty.'),
      '#default_value'  => $this->options['skip_empty_fields'],
      '#description'    => t('For each row in the view, skip adding a field if the field value is NULL or blank.'),
    );*/
    $form['escape_as_CDATA'] = array(
      '#type' => 'radios',
      '#title' => t('Escape row content as CDATA'),
      '#description' => t('For each row in the view, escape the row field content as XML CDATA sections.'),
      '#options' => array('yes' => t('Yes'), 'no' => t('No')),
      '#default_value' => $this->options['escape_as_CDATA'],
      //'#process' => array('views_process_dependency'),
      //'#dependency' => array('edit-style-options-element-output-options' => array('nested')),
    );
    $form['header'] = array(
      '#type' => 'textarea',
      '#title' => t('XML document header'),
      '#description' => t('You can use this to place any additional lines you want after the document declaration (like !DOCTYPE.)'),
      '#default_value' => $this->options['header'],
    );
    $form['content_type'] = array(
      '#type' => 'radios',
      '#title' => ('Content-Type'),
      '#options' => array(
         'default' => t('Default: text/xhtml'),
         'text/xml' => t('text/xml'),
         'text/html' => t('text/html'),
      ),
      '#default_value' => $this->options['content_type'],
      '#description' => t('The Content-Type header that will be sent with the XHTML output.'),
    );
    $form['author'] = array(
      '#type' => 'textfield',
      '#title' => 'View author',
      '#description' => 'The Drupal user name of the person who authored this view.',
      '#default_value' => $this->options['author'],
      );
    $form['using_views_api_mode'] = array(
      '#type' => 'checkbox',
      '#title' => t('Views API mode'),
      '#default_value' => $this->options['using_views_api_mode'],
      '#description' => t('Not using View API mode means the JSON gets output directly and the server ceases normal page processing.  Using it means the server does not cease processing after outputting the JSON.  This allows the Views API to be used with the view without having to prematurely terminate page processing.'),
    );
  }

  /**
   * Implements views_style_plugin::theme_functions(). Returns an array of theme functions to use.
   * for the current style plugin
   * @return array
   */
  function theme_functions() {
    $options = $this->options;
    $hook = 'views_views_xhtml_style_' . $options['format'];
    return views_theme_functions($hook, $this->view, $this->display);
  }

  /**
   * Implements views_style_plugin::additional_theme_functions(). Returns empty array.
   * @return array
   */
  function additional_theme_functions() {
    return array();
  }

  /**
   * Implements view_style_plugin::render()
   */
  function render() {
    $view = $this->view;
    $options = $this->options;
    $field = $view->field;

    $rows = array();
    foreach ($view->result as $count => $row) {
      $view->row_index = $count;
      $rows[] = _views_xhtml_render_fields($view, $row);
    }
    unset($view->row_index);

    return theme($this->theme_functions(), array('view' => $view, 'options' => $options, 'rows' => $rows));
  }
}
