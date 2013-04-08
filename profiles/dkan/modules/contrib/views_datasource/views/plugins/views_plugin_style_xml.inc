<?php
/**
 * @file
 * Implements views_plugin_style for views_xml
 */

/**
 * Implements views_plugin_style
 */
class views_plugin_style_xml extends views_plugin_style {
  /**
   * Implementation of views_plugin_style::option_definition
   */
  function option_definition() {
    $options = parent::option_definition();
    $author_default = isset($GLOBALS['user']->name) ? $GLOBALS['user']->name : variable_get('anonymous', t('Anonymous'));
    $options['schema'] = array('default' => 'raw', 'translatable' => FALSE);
    $options['root_element'] = array('default' => 'node', 'translatable' => FALSE);
    $options['top_child_object'] = array('default' => 'node', 'translatable' => FALSE);
    $options['field_output'] = array('default' => 'normal', 'translatable' => FALSE);
    $options['plaintext_output'] = array('default' => TRUE, 'translatable' => FALSE);
    //$options['skip_empty_fields'] = array('default' => TRUE, 'translatable' => FALSE);
    $options['element_output'] = array('default' => 'nested', 'translatable' => FALSE);
    $options['escape_as_CDATA'] = array('default' => 'no', 'translatable' => FALSE);
    $options['content_type'] = array('default' => 'text/xml', 'translatable' => FALSE);
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
    $form['schema'] = array(
      '#type' => 'radios',
      '#title' => t('XML schema'),
      '#description' => t('The schema or format of the XML data document.'),
      '#options' => views_xml_views_formats(),
      '#default_value' => $this->options['schema'],
    );
    $form['root_element'] = array(
      '#type' => 'textfield',
      '#title' => t('Root element name'),
      '#default_value' => $this->options['root_element'],
      '#description' => t('The name of the root element in a raw XML document.'),
      //'#process' => array('views_process_dependency'),
      //'#dependency' => array('edit-style-options-schema' => array('raw')),
    );
    $form['top_child_object'] = array(
      '#type' => 'textfield',
      '#title' => t('Top-level child element name'),
      '#default_value' => $this->options['top_child_object'],
      '#description' => t('The name of each top-level child object in a raw XML document. e.g node or user or forum_post'),
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
    $form['element_output']  = array(
      '#type' => 'radios',
      '#title' => t('Element output'),
      '#description' => t('For each row in the view, fields can be output as either nested XML child elements, or attributes of the raw XML element.'),
      '#options' => array('nested' => t('Nested'), 'attributes' => t('Attributes')),
      '#default_value' => $this->options['element_output'],
    );
    $form['escape_as_CDATA']  = array(
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
      '#title'  => t('Content-Type'),
      '#options' => array(
        'default' => t('Default: text/xml for raw documents, text/html for OPML, application/atom+xml for Atom.'),
        'text/xml' => t('text/xml'),
        'text/html' => t('text/html'),
        'text/x-opml' => t('text/x-opml'),
      ),
      '#default_value' => $this->options['content_type'],
      '#description' => t('The Content-Type header that will be sent with the XML output.'),
    );
    $form['author'] = array(
      '#type' => 'textfield',
      '#title' => t('View author'),
      '#description' => t('The Drupal user name of the person who authored this view.'),
      '#default_value' => $this->options['author'],
      );
    $form['using_views_api_mode'] = array(
      '#type' => 'checkbox',
      '#title' => t('Views API mode'),
      '#default_value' => $this->options['using_views_api_mode'],
      '#description' => t('Not using View API mode means the XML gets output directly and the server ceases normal page processing.  Using it means the server does not cease processing after outputting the JSON.  This allows the Views API to be used with the view without having to prematurely terminate page processing.'),
    );
    /*
    $form['action_on_missing_field'] = array(
      '#type'           => 'radios',
      '#title'          => t('Action on missing field'),
      '#options' => array('none' => t('Take no action if a required field is missing.'), 'warn' => t('Warn about the missing fields in preview mode.'), 'stop'=>t('Stop page processing and send an error message if a required field is missing.')),
      '#default_value'  => $this->options['action_on_missing_field'],
      '#description'    => t('Action to take if no field can be mapped to a required element or schema for the selected XML schema.'),
    );*/
  }

  /**
   * Implementation of views_style_plugin::theme_functions(). Returns an array of theme functions to use.
   * for the current style plugin
   * @return array
   */
  function theme_functions() {
    $options = $this->options;
    $hook = 'views_views_xml_style_' . $options['schema'];
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
   * Implementation of view_style_plugin::render()
   */
  function render() {
    $view = $this->view;
    $options = $this->options;
    $field = $view->field;

    $rows = array();
    foreach ($view->result as $count => $row) {
      $view->row_index = $count;
      $rows[] = _views_xml_render_fields($view, $row);
    }
    unset($view->row_index);

    return theme($this->theme_functions(), array('view' => $view, 'options' => $options, 'rows' => $rows));
  }
}
