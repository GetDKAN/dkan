<?php

namespace Drupal\json_form_widget;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\metastore\SchemaRetriever;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Component\Uuid\Php;

/**
 * Class SchemaUiHandler.
 */
class SchemaUiHandler implements ContainerInjectionInterface {

  /**
   * Schema.
   *
   * @var object
   */
  public $schemaUi;

  /**
   * SchemaRetriever.
   *
   * @var \Drupal\metastore\SchemaRetriever
   */
  protected $schemaRetriever;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $loggerFactory;

  /**
   * Uuid Service.
   *
   * @var \Drupal\Component\Uuid\Php
   */
  protected $uuidService;

  /**
   * StringHelper Service.
   *
   * @var \Drupal\json_form_widget\StringHelper
   */
  protected $stringHelper;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.metastore.schema_retriever'),
      $container->get('logger.factory'),
      $container->get('uuid'),
      $container->get('json_form.string_helper')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(SchemaRetriever $schema_retriever, LoggerChannelFactory $logger_factory, Php $uuid, StringHelper $string_helper) {
    $this->schemaRetriever = $schema_retriever;
    $this->schemaUi = FALSE;
    $this->loggerFactory = $logger_factory;
    $this->uuidService = $uuid;
    $this->stringHelper = $string_helper;
  }

  /**
   * Set schema.
   */
  public function setSchemaUi($schema_name) {
    try {
      $schema_ui = $this->schemaRetriever->retrieve($schema_name . '.ui');
      $this->schemaUi = json_decode($schema_ui);
    }
    catch (\Exception $exception) {
      $this->loggerFactory->get('json_form_widget')->notice("The UI Schema for $schema_name does not exist.");
    }
  }

  /**
   * Get schema UI.
   */
  public function getSchemaUi() {
    return $this->schemaUi;
  }

  /**
   * Apply schema UI to form.
   */
  public function applySchemaUi($form) {
    if ($this->schemaUi) {
      foreach ((array) $this->schemaUi as $property => $spec) {
        // Apply schema UI on base field.
        $form[$property] = $this->applyOnBaseField($spec, $form[$property]);
        // Handle property specification from schema UI.
        $form[$property] = $this->handlePropertySpec($property, $spec, $form[$property]);
      }
    }
    return $form;
  }

  /**
   * Helper function for handling Schema UI specs.
   */
  public function handlePropertySpec($property, $spec, $element, $in_array = FALSE) {
    if ($in_array) {
      $element = $this->applyOnBaseField($spec, $element);
    }
    // Handle UI specs for array items.
    if (isset($spec->items)) {
      $fields = array_keys((array) $spec->items);
      foreach ($element[$property] as &$item) {
        $item = $this->applyOnArrayFields($property, $spec->items, $item, $fields);
      }
    }
    if (isset($spec->properties)) {
      $element[$property] = $this->applyOnBaseField($spec, $element[$property]);
      $element = $this->applyOnObjectFields($property, $spec->properties, $element);
    }
    return $element;
  }

  /**
   * Apply schema UI to simple fields.
   */
  public function applyOnBaseField($spec, $element) {
    if (isset($spec->{"ui:options"})) {
      $element = $this->updateWidgets($spec->{"ui:options"}, $element);
      $element = $this->disableFields($spec->{"ui:options"}, $element);
      $element = $this->addPlaceholders($spec->{"ui:options"}, $element);
      $element = $this->changeFieldDescriptions($spec->{"ui:options"}, $element);
      $element = $this->changeFieldTitle($spec->{"ui:options"}, $element);
    }
    return $element;
  }

  /**
   * Apply schema UI to object fields.
   */
  public function applyOnObjectFields($property, $spec, $element) {
    foreach ((array) $spec as $field => $sub_spec) {
      if (isset($element[$property][$field])) {
        $element[$property][$field] = $this->applyOnBaseField($sub_spec, $element[$property][$field]);
      }
    }
    return $element;
  }

  /**
   * Apply schema UI to array fields.
   */
  public function applyOnArrayFields($property, $spec, $element, $fields) {
    foreach ($fields as $field) {
      if (isset($element[$property][$field])) {
        $element[$property][$field] = $this->handlePropertySpec($field, $spec->{$field}, $element[$property][$field], TRUE);
      }
      else {
        $element = $this->applyOnBaseField($spec, $element);
      }
    }
    return $element;
  }

  /**
   * Helper function for handling widgets.
   */
  public function updateWidgets($spec, $element) {
    if (isset($spec->widget)) {
      return $this->getConfiguredWidget($spec, $element);
    }
    else {
      return $element;
    }
  }

  /**
   * Helper function for getting element with configured widget.
   */
  public function getConfiguredWidget($spec, $element) {
    switch ($spec->widget) {
      case 'hidden':
        $element['#access'] = FALSE;
        break;

      case 'textarea':
        $element['#type'] = 'textarea';
        $element = $this->getTextareaOptions($spec, $element);
        break;

      case 'dkan_uuid':
        $element['#default_value'] = !empty($element['#default_value']) ? $element['#default_value'] : $this->uuidService->generate();
        $element['#access'] = FALSE;
        break;

      case 'upload_or_link':
        $element = $this->handleUploadOrLink($element, $spec);
        break;

      case 'options':
        $element = $this->handleDropdown($element, $spec);
        break;
    }
    return $element;
  }

  /**
   * Handle configuration for dropdown elements.
   */
  public function handleDropdown($element, $spec) {
    if (isset($spec->titleProperty)) {
      if (isset($element[$spec->titleProperty])) {
        $element[$spec->titleProperty] = $this->getDropdownElement($element[$spec->titleProperty], $spec);
      }
    }
    else {
      $element = $this->getDropdownElement($element, $spec);
    }
    return $element;
  }

  /**
   * Helper function to build a dropdown element.
   */
  public function getDropdownElement($element, $spec) {
    $element['#type'] = $this->getSelectType($spec);
    $element['#options'] = $this->getDropdownOptions($spec->source);
    if ($element['#type'] === 'select_or_other_select') {
      $element = $this->handleSelectOtherDefaultValue($element, $element['#options']);
      $element['#input_type'] = isset($spec->other_type) ? $spec->other_type : 'textfield';
    }
    return $element;
  }

  /**
   * Helper function to get type of pick list.
   */
  public function getSelectType($spec) {
    if ($spec->type === 'select_other') {
      return 'select_or_other_select';
    }
    return 'select';
  }

  /**
   * Helper function to get options for dropdowns.
   */
  public function getDropdownOptions($source) {
    if ($source->enum) {
      return $this->stringHelper->getSelectOptions($source);
    }
  }

  /**
   * Helper function to add the value of other to current list of options.
   */
  private function handleSelectOtherDefaultValue($element, $options) {
    if (!empty($element['#default_value'])) {
      if (!array_key_exists($element['#default_value'], $options)) {
        $element['#options'][$element['#default_value']] = $element['#default_value'];
      }
    }
    return $element;
  }

  /**
   * Handle configuration for upload_or_link elements.
   */
  public function handleUploadOrLink($element, $spec) {
    $element['#type'] = 'upload_or_link';
    $element['#upload_location'] = 'public://uploaded_resources';
    if (isset($element['#default_value'])) {
      $element['#uri'] = $element['#default_value'];
      unset($element['#default_value']);
    }
    if (isset($spec->extensions)) {
      $element['#upload_validators']['file_validate_extensions'][] = $spec->extensions;
    }
    return $element;
  }

  /**
   * Helper function for getting textarea options.
   */
  private function getTextareaOptions($spec, $element) {
    if (isset($spec->rows)) {
      $element['#rows'] = $spec->rows;
    }
    if (isset($spec->cols)) {
      $element['#cols'] = $spec->cols;
    }
    return $element;
  }

  /**
   * Helper function for disabling fields.
   */
  public function disableFields($spec, $element) {
    if (isset($spec->disabled)) {
      $element['#disabled'] = TRUE;
    }
    return $element;
  }

  /**
   * Helper function for adding placeholders.
   */
  public function addPlaceholders($spec, $element) {
    if (isset($spec->placeholder)) {
      $element['#attributes']['placeholder'] = $spec->placeholder;
    }
    return $element;
  }

  /**
   * Helper function for changing help text.
   */
  public function changeFieldDescriptions($spec, $element) {
    if (isset($spec->description)) {
      $element['#description'] = $spec->description;
    }
    return $element;
  }

  /**
   * Helper function for changing help text.
   */
  public function changeFieldTitle($spec, $element) {
    if (isset($spec->title)) {
      $element['#title'] = $spec->title;
    }
    return $element;
  }

}
