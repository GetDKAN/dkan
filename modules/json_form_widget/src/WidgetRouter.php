<?php

namespace Drupal\json_form_widget;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\json_form_widget\OptionSource\JsonFormOptionSourcePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * JSON form widget router service.
 */
class WidgetRouter implements ContainerInjectionInterface {

  /**
   * Uuid Service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * StringHelper Service.
   *
   * @var \Drupal\json_form_widget\StringHelper
   */
  protected $stringHelper;

  /**
   * Option source plugin manager.
   */
  protected JsonFormOptionSourcePluginManager $pluginManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   Uuid service.
   * @param \Drupal\json_form_widget\StringHelper $string_helper
   *   String Helper service.
   * @param \Drupal\json_form_widget\OptionSource\JsonFormOptionSourcePluginManager $plugin_manager
   *   Option source plugin manager.
   */
  public function __construct(
    UuidInterface $uuid,
    StringHelper $string_helper,
    JsonFormOptionSourcePluginManager $plugin_manager,
  ) {
    $this->uuidService = $uuid;
    $this->stringHelper = $string_helper;
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('uuid'),
      $container->get('json_form.string_helper'),
      $container->get('plugin.manager.json_form_option_source')
    );
  }

  /**
   * Helper function for getting element with configured widget.
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to apply UI options.
   *
   * @return array
   *   Element with widget configuration based on UI options.
   */
  public function getConfiguredWidget(mixed $spec, array $element) {
    $widgets = $this->getWidgets();
    if (in_array($spec->widget, array_keys($widgets))) {
      $method_name = $widgets[$spec->widget];
      $element = $this->$method_name($spec, $element);
    }
    return $element;
  }

  /**
   * Get list of widgets available and functions to handle each widget.
   *
   * @return array
   *   Associative array of widgets vrs functions to handle the elements.
   */
  public function getWidgets() {
    return [
      'hidden' => 'handleHiddenElement',
      'textarea' => 'handleTextareaElement',
      'dkan_uuid' => 'handleDkanUuidElement',
      'upload_or_link' => 'handleUploadOrLinkElement',
      'list' => 'handleListElement',
      'date' => 'handleDateElement',
      'flexible_datetime' => 'handleDatetimeElement',
      'date_range' => 'handleDateRangeElement',
      'number' => 'handleNumberElement',
    ];
  }

  /**
   * Handle configuration for list elements.
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to convert into list element.
   *
   * @return array
   *   The element configured as a list element.
   */
  public function handleListElement(mixed $spec, array $element) {
    $this->fixOptionSource($spec);
    $title_property = ($spec->source->config->titleProperty ?? FALSE);

    if (isset($title_property, $element[$title_property])) {
      $element[$title_property] = $this->getDropdownElement($element[$title_property], $spec, $title_property);
    }

    if (isset($spec->source->config->returnValue)) {
      $element = $this->getDropdownElement($element, $spec, $title_property);
    }
    elseif (!isset($spec->source->config->titleProperty)) {
      $element = $this->getDropdownElement($element, $spec);
    }

    // If a maxlength was set earlier, remove it as it is not allowed here.
    unset($element['#maxlength']);
    return $element;
  }

  /**
   * Helper function to build a dropdown element.
   *
   * @param mixed $element
   *   Element to apply UI options.
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param mixed $titleProperty
   *   The title property name in which the dropdown should be added (or FALSE).
   *
   * @return array
   *   The dropdown element configured.
   */
  public function getDropdownElement(mixed $element, mixed $spec, mixed $titleProperty = FALSE) {
    // If we're dealing with a fieldset containing a "field" element, it is
    // an array with action buttons, which we should flatten.
    if (isset($element['field']) && $element['#type'] == 'fieldset') {
      $element = ['#required' => ($element['#required'] ?? FALSE)] + $element['field'];
    }

    $element['#type'] = $this->getSelectType($spec);
    $element['#options'] = $this->getDropdownOptions($spec->source, $titleProperty);
    if ($element['#type'] === 'select_or_other_select') {
      $element = $this->handleSelectOtherDefaultValue($element, $element['#options']);
      $element['#input_type'] = $spec->other_type ?? 'textfield';
    }
    $element['#other_option'] = $element['#other_option'] ?? FALSE;

    if ($element['#type'] === 'select2') {
      $element['#multiple'] = ($spec->multiple ?? FALSE) ? TRUE : FALSE;
      $element['#autocreate'] = ($spec->allowCreate ?? FALSE) ? TRUE : FALSE;
    }
    if (isset($element['#autocreate']) && $spec->type !== 'select2') {
      $element['#target_type'] = $this->getTargetType($spec->source);
    }
    return $element;
  }

  /**
   * Fix legacy "metastoreSchema" source property, move titleProperty to config.
   *
   * @param object $spec
   *   The spec object to fix.
   *
   * @return object
   *   The fixed spec object.
   */
  protected function fixOptionSource(object $spec): object {
    if (is_object($spec->source ?? NULL) && is_string($spec->source->metastoreSchema ?? NULL)) {
      $spec->source = (object) [
        'plugin' => 'metastoreSchema',
        'config' => (object) array_filter([
          'schema' => $spec->source->metastoreSchema,
          'titleProperty' => $spec->titleProperty ?? NULL,
          'returnValue' => $spec->source->returnValue ?? NULL,
        ]),
      ];
      unset($spec->titleProperty, $spec->source->metastoreSchema, $spec->source->returnValue);
    }
    return $spec;
  }

  /**
   * Helper function to get type of pick list.
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   *
   * @return string
   *   The type of dropdown element to use.
   */
  public function getSelectType(mixed $spec) {
    if (isset($spec->type) && $spec->type === 'select_other') {
      return 'select_or_other_select';
    }
    elseif (isset($spec->type) && ($spec->type === 'autocomplete' || $spec->type === 'select2')) {
      return 'select2';
    }
    return 'select';
  }

  /**
   * Helper function to get options for dropdowns.
   *
   * @param object $source
   *   Source object from UI options.
   * @param string|false $titleProperty
   *   The title property name in which the dropdown should be added (or FALSE).
   *
   * @return array
   *   Array with options for the dropdown.
   */
  public function getDropdownOptions(object $source, string|false $titleProperty = FALSE) {
    $options = [];
    if (isset($source->enum)) {
      $options = $this->stringHelper->getSelectOptions($source);
    }
    elseif (is_string($source->plugin ?? NULL)) {
      $option_source = $this->pluginManager->createInstance($source->plugin);
      $options = $option_source->getOptions((array) $source->config ?? []);
    }
    return $options;
  }

  /**
   * Get the target type for the dropdown based on the source.
   *
   * @param mixed $source
   *   The source object from UI options.
   *
   * @return string|null
   *   The target type for the dropdown.
   */
  public function getTargetType(mixed $source): ?string {
    if (is_string($source->plugin ?? NULL)) {
      $option_source = $this->pluginManager->createInstance($source->plugin);
      return $option_source->getTargetType((array) $source->config ?? []);
    }
    return NULL;
  }

  /**
   * Helper function to add the value of other to current list of options.
   */
  protected function handleSelectOtherDefaultValue($element, $options) {
    if (!empty($element['#default_value'])) {
      if (!array_key_exists($element['#default_value'], $options)) {
        $element['#options'][$element['#default_value']] = $element['#default_value'];
      }
    }
    return $element;
  }

  /**
   * Handle configuration for upload_or_link elements.
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to convert into upload_or_link.
   *
   * @return array
   *   The element configured as upload_or_link.
   */
  public function handleUploadOrLinkElement(mixed $spec, array $element) {
    $element['#type'] = 'upload_or_link';
    $element['#upload_location'] = 'public://uploaded_resources';
    if (isset($element['#default_value'])) {
      $element['#uri'] = $element['#default_value'];
      unset($element['#default_value']);
    }
    if (isset($spec->extensions)) {
      $element['#upload_validators']['FileExtension'] = ['extensions' => $spec->extensions];
    }
    if (isset($spec->progress_indicator)) {
      $element['#progress_indicator'] = $spec->progress_indicator;
    }
    // If a maxlength was set earlier, remove it as it is not allowed here.
    unset($element['#maxlength']);
    return $element;
  }

  /**
   * Helper function for getting a textarea element.
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to convert into textarea.
   *
   * @return array
   *   The element configured as textarea.
   */
  public function handleTextareaElement(mixed $spec, array $element) {
    $element['#type'] = 'textarea';
    unset($element['#maxlength']);
    if (isset($spec->textFormat)) {
      $element['#type'] = 'text_format';
      $element['#format'] = $spec->textFormat;
      $element['#allowed_formats'] = [
        $spec->textFormat,
      ];
    }
    if (isset($spec->rows)) {
      $element['#rows'] = $spec->rows;
    }
    if (isset($spec->cols)) {
      $element['#cols'] = $spec->cols;
    }
    return $element;
  }

  /**
   * Helper function for hiding an element.
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to convert into hidden.
   *
   * @return array
   *   The element configured as hidden.
   */
  public function handleHiddenElement(mixed $spec, array $element) {
    $element['#access'] = FALSE;
    return $element;
  }

  /**
   * Helper function for getting a dkan_uuid element.
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to convert into dkan_uuid.
   *
   * @return array
   *   The element configured as dkan_uuid.
   */
  public function handleDkanUuidElement(mixed $spec, array $element) {
    $element['#default_value'] = !empty($element['#default_value']) ? $element['#default_value'] : $this->uuidService->generate();
    $element['#access'] = FALSE;
    return $element;
  }

  /**
   * Helper function for getting a date element.
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to convert into date.
   *
   * @return array
   *   The element configured as date.
   */
  public function handleDateElement(mixed $spec, array $element) {
    $element['#type'] = 'date';
    // If a maxlength was set earlier, remove it as it is not allowed here.
    unset($element['#maxlength']);
    $format = $spec->format ?? 'Y-m-d';
    if (isset($element['#default_value'])) {
      $date = new DrupalDateTime($element['#default_value']);
      $element['#default_value'] = $date->format($format);
    }
    $element['#date_date_format'] = $format;
    return $element;
  }

  /**
   * Helper function for getting a datetime element.
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to convert into datetime.
   *
   * @return array
   *   The element configured as datetime.
   */
  public function handleDatetimeElement(mixed $spec, array $element) {
    $element['#type'] = 'flexible_datetime';
    // If a maxlength was set earlier, remove it as it is not allowed here.
    unset($element['#maxlength']);
    if (isset($element['#default_value'])) {
      $date = new DrupalDateTime($element['#default_value']);
      $element['#default_value'] = $date;
    }
    if (isset($spec->timeRequired) && is_bool($spec->timeRequired)) {
      $element['#date_time_required'] = $spec->timeRequired;
    }
    return $element;
  }

  /**
   * Helper function for getting a date_range element.
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to convert into date_range.
   *
   * @return array
   *   The element configured as date_range.
   */
  public function handleDateRangeElement(mixed $spec, array $element) {
    $element['#type'] = 'date_range';
    // If a maxlength was set earlier, remove it as it is not allowed here.
    unset($element['#maxlength']);
    return $element;
  }

  /**
   * Helper function for getting a number element.
   *
   * @param object $spec
   *   Specification for UI options. Optional properties:
   *   - step: Ensures that the number is an even multiple of step.
   *   - min: Minimum value.
   *   - max: Maximum value.
   * @param array $element
   *   Element to convert into number.
   *
   * @return array
   *   The element configured as number.
   *
   * @see \Drupal\Core\Render\Element\Number
   */
  public function handleNumberElement($spec, array $element) {
    $element['#type'] = 'number';
    if (isset($spec->step)) {
      $element['#step'] = $spec->step;
    }
    if (isset($spec->min)) {
      $element['#min'] = $spec->min;
    }
    if (isset($spec->max)) {
      $element['#max'] = $spec->max;
    }
    return $element;
  }

}
