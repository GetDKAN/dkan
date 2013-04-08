<?php
/**
 * @file
 * Implementation of views_plugin_style for views_xml
 */


/**
 * Implements views_plugin_style
 */
class views_plugin_style_xml_test extends views_plugin_style {

  function option_definition() {
    $options = parent::option_definition();
    $options['schema'] = array('default' => 'raw', 'translatable' => TRUE);
    $options['root_element'] = array('default' => 'node', 'translatable' => TRUE);
    $options['field_output'] = array('default' => 'normal', 'translatable' => TRUE);
    $options['element_output'] = array('default' => 'nested', 'translatable' => TRUE);
    $options['escape_as_CDATA'] = array('default' => 'yes', 'translatable' => TRUE);
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
      '#options' => array('raw' => t('Raw XML'), 'opml' => t('OPML'), 'atom' => t('Atom')),
      '#default_value' => $this->options['schema'],
    );
    $form['root_element'] = array(
      '#type' => 'textfield',
      '#title' => t('Root element name'),
      '#default_value' => $this->options['root_element'],
      '#description' => t('The name of the root element in the XML document.'),
      //'#process' => array('views_process_dependency'),
      //'#dependency' => array('radios:schema' => array('raw')),

    );
    $form['field_output']  = array(
      '#type' => 'radios',
      '#title' => t('Field output'),
      '#description' => t('For each row in the view, fields can be output as either the field rendered by Views, or by the raw content of the field.'),
      '#options' => array('normal' => t('Normal'), 'raw' => t('Raw')),
      '#default_value' => $this->options['field_output'],
    );
    $form['element_output']  = array(
      '#type' => 'radios',
      '#title' => t('Element output'),
      '#description' => t('For each row in the view, fields can be output as either nested XML child elements, or attributes of the XML element.'),
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
      //'#dependency' => array('radio:edit-style-options-element_output' => array('nested')),
    );
  }

  function render() {
    $view = &$this->view;
    $options = $this->options;
    $field = $view->field;
    //views_xml_test_debug_stop($view->result);
    //views_xml_test_debug_stop($field["body"]->options["label"]);
    //view_xml_test_debug($field["body"]-)
    /*
    if (empty($this->row_plugin)) {
      vpr('views_plugin_style_xml_test: Views XML: The row plugin for this style must be the XML element plugin.');
      drupal_set_message('<b style="color:red">Views XML: The row plugin for this style must be the XML element plugin.</b>');
      return;
    }

    if (get_class($this->row_plugin) !== 'views_plugin_row_xml_test') {
      vpr('views_plugin_style_xml_test: The row plugin for this style must be the XML element plugin.');
      drupal_set_message('<b style="color:red">Views XML: The row plugin for this style must be the XML element plugin.</b>');
      return;
    }
    */

    $rows = array();

    foreach ($view->result as $row) {
    //views_xml_test_debug_stop($row);
      $rows[] = _views_xml_test_render_fields($view, $row);
    }
    //views_xml_test_debug_stop($rows);
    return theme($this->theme_functions(), array('view' => $this->view, 'options' => $this->options, 'rows' => $rows));
  }
}
