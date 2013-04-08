<?php

/**
 * OG behavior handler.
 */
class OgWidgetHandler extends EntityReference_BehaviorHandler_Abstract {

  public function access($field, $instance) {
    return ($field['settings']['handler'] == 'og' || strpos($field['settings']['handler'], 'og_') === 0) && $instance['widget']['type'] == 'og_complex';
  }

  /**
   * Override EntityReferenceHandler::settingsForm().
   */
  public function settingsForm($field, $instance) {
    $form = parent::settingsForm($field, $instance);

    $settings = !empty($instance['settings']['behaviors']['og_widget']) ? $instance['settings']['behaviors']['og_widget'] : array();
    $settings += array(
      'default' => array(
        'widget_type' => 'options_select',
      ),
      'admin' => array(
        'widget_type' => 'entityreference_autocomplete',
      ),
    );

    $field_types = array(
      'default' => array(
        'title' => t('Default widget type'),
        'description' => t('The widget type of the field as it will appear to the user.'),
      ),
      'admin' => array(
        'title' => t('Administrator widget type'),
        'description' => t('The widget type of the field that will appear only to a user with "Administer group" permission.'),
      ),
    );

    module_load_include('inc', 'field_ui', 'field_ui.admin');
    $widget_types = field_ui_widget_type_options('entityreference');
    unset($widget_types['og_complex']);

    foreach ($field_types as $field_type => $value) {
      $form[$field_type]['widget_type'] = array(
        '#type' => 'select',
        '#title' => $value['title'],
        '#required' => TRUE,
        '#options' => $widget_types,
        '#default_value' => $settings[$field_type]['widget_type'],
        '#description' => $value['description'],
      );
    }

    return $form;
  }

}
