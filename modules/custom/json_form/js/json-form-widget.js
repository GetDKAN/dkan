/**
 * @file
 * JSON Form implementation.
 */

(function ($, Drupal) {

  'use strict';


  /**
   * Use this behavior as a template for custom Javascript.
   */
  Drupal.behaviors.json_form_widget = {
    attach: function (context, settings) {
      //console.log(settings);
      try {
        if (widgetGlobalConfigured(settings)) {
          $.each(settings.jsonFormFieldWidget, function(key, jsonFormFieldWidgetElement) {
            if (widgetConfigured(jsonFormFieldWidgetElement)) {
              initJsonFormWidget(jsonFormFieldWidgetElement.identifier, jsonFormFieldWidgetElement, context);
            }
          });
        }
      }
      catch (e) {
        console.error(e);
      }
    }
  };

  /**
   * Check global JSON Form widget is configured.
   *
   * @param array settings
   *
   * @returns bool
   *   Is configured?
   */
  function widgetGlobalConfigured(settings) {
    return typeof (settings.jsonFormFieldWidget) != 'undefined' && settings.jsonFormFieldWidget != null;
  }

  /**
   * Check JSON Form widget is configured.
   *
   * @param array settings
   *
   * @returns bool
   *   Is configured?
   */
  function widgetConfigured(settings) {

    return typeof (settings.schema) != 'undefined' && settings.schema != null
      && typeof (settings.identifier) != 'undefined' && settings.identifier != null;
  }

  /**
   * Init JSON Form.
   *
   * @param object schem
   *   Json form schema.
   * @param object context
   *   Context.
   */
  function initJsonFormWidget(identifier, config, context) {
    $('#' + identifier, context).once('json-form-widget').each(function () {
      buildJsonForm(identifier, config);
    });
  }

  /**
   * Build widget.
   *
   * @param string identifier
   *   Identifier.
   * @param object config
   *   Widget config.
   */
  function buildJsonForm(identifier, config) {
    var BrutusinForms = brutusin["json-forms"];
    var container = document.getElementById(identifier);
    
    var jsonform = BrutusinForms.create(config.schema);
    var initialValue = $(config.textarea).val();
    jsonform.render(container, JSON.parse(initialValue));
    var event_data = {
      'jsonform': jsonform,
      'textarea': config.textarea,
    };
    syncJsonFormElement('#' + identifier + ' input', event_data);
    syncJsonFormElement('#' + identifier + ' select', event_data);
    syncJsonFormButton('#' + identifier + ' button', event_data);

    $('#' + identifier).on('DOMNodeInserted', event_data, onJsonFormNewElement);
  }

  /**
   * Hooks into inputs and selects from json form to sync the textfield.
   *
   * @param object event
   */
  function onJsonFormNewElement(event) {
    $(this).find('input').each(function (key, value) {
      syncJsonFormElement(value, event.data);
    });
    $(this).find('select').each(function (key, value) {
      syncJsonFormElement(value, event.data);
    });
  }

  /**
   * Sync buttons ft.
   *
   * @param string element
   *   Button element.
   * @param object event_data
   *   Event data.
   */
  function syncJsonFormButton(element, event_data) {
    $(element).once('json-form-widget-sync').on('click', event_data, refreshWidget);
  }

  /**
   * Sync input elements with the textfield.
   *
   * @param string element
   *   Element.
   * @param {type} event_data
   *   Event data.
   */
  function syncJsonFormElement(element, event_data) {
    $(element).once('json-form-widget-sync').on('change', event_data, refreshWidget);
  }

  /**
   * Refresh json form with textarea.
   *
   * @param object event
   */
  function refreshWidget(event) {
    $(event.data.textarea).val(JSON.stringify(event.data.jsonform.getData()));
  }

})(jQuery, Drupal);
