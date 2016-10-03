<?php

/**
 * @file
 * Contains the list style plugin.
 */

/**
 * Style plugin to render each item in a slideshow of an ordered or unordered list.
 *
 * @ingroup views_style_plugins
 */
class views_dkan_workflow_tree_plugin_style extends views_plugin_style_list {

  /**
   * Set default options
   */
  function option_definition() {
    $options = parent::option_definition();
    $options['class'] = array('default' => '');
    $options['wrapper_class'] = array('default' => 'item-list');
    $options['main_field'] = array('default' => '');
    $options['parent_field'] = array('default' => '');
    $options['vbo_field'] = array('default' => '');
    $options['vid_field'] = array('default' => '');

    return $options;
  }

  /**
   * Render the given style.
   */
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $fields = array('' => t('<None>'));

    foreach ($this->display->handler->get_handlers('field') as $field => $handler) {
      if ($label = $handler->label()) {
        $fields[$field] = $label;
      }
      else {
        $fields[$field] = $handler->ui_name();
      }
    }

    $events = array('click' => t('On Click'), 'mouseover' => t('On Mouseover'));

    // Not applicable for views_dkan_workflow_tree
    unset($form['wrapper_class']);
    unset($form['class']);
    unset($form['type']);
    unset($form['main_field']);
    unset($form['parent_field']);

    $form['vbo_field'] = array(
      '#type' => 'select',
      '#title' => t('VBO field'),
      '#options' => $fields,
      '#default_value' => $this->options['vbo_field'],
      '#description' => t('Select the vbo field with each record.'),
      '#required' => TRUE,
    );

    $form['vid_field'] = array(
      '#type' => 'select',
      '#title' => t('VID field'),
      '#options' => $fields,
      '#default_value' => $this->options['vid_field'],
      '#description' => t('Select the field that contains the filtered Node VID.'),
    );
  }
}
