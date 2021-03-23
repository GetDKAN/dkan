<?php

namespace Drupal\json_form_widget;

use Drupal\Component\Uuid\Php;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\metastore\Service;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WidgetRouter.
 */
class WidgetRouter implements ContainerInjectionInterface {

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
   * Metastore Service.
   *
   * @var \Drupal\metastore\Service
   */
  protected $metastore;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('uuid'),
      $container->get('json_form.string_helper'),
      $container->get('dkan.metastore.service')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(Php $uuid, StringHelper $string_helper, Service $metastore) {
    $this->uuidService = $uuid;
    $this->stringHelper = $string_helper;
    $this->metastore = $metastore;
  }

  /**
   * Helper function for getting element with configured widget.
   */
  public function getConfiguredWidget($spec, $element) {
    if ($spec->widget == 'hidden') {
      $element['#access'] = FALSE;
    }
    elseif ($spec->widget == 'textarea') {
      $element['#type'] = 'textarea';
      $element = $this->getTextareaOptions($spec, $element);
    }
    elseif ($spec->widget == 'dkan_uuid') {
      $element['#default_value'] = !empty($element['#default_value']) ? $element['#default_value'] : $this->uuidService->generate();
      $element['#access'] = FALSE;
    }
    elseif ($spec->widget == 'upload_or_link') {
      $element = $this->handleUploadOrLink($element, $spec);
    }
    elseif ($spec->widget == 'options') {
      $element = $this->handleDropdown($element, $spec);
    }
    return $element;
  }

  /**
   * Handle configuration for dropdown elements.
   */
  public function handleDropdown($element, $spec) {
    if (isset($spec->titleProperty)) {
      if (isset($element[$spec->titleProperty])) {
        $element[$spec->titleProperty] = $this->getDropdownElement($element[$spec->titleProperty], $spec, $spec->titleProperty);
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
  public function getDropdownElement($element, $spec, $titleProperty = FALSE) {
    $element['#type'] = $this->getSelectType($spec);
    $element['#options'] = $this->getDropdownOptions($spec->source, $titleProperty);
    if ($element['#type'] === 'select_or_other_select') {
      $element = $this->handleSelectOtherDefaultValue($element, $element['#options']);
      $element['#input_type'] = isset($spec->other_type) ? $spec->other_type : 'textfield';
    }
    if ($element['#type'] === 'select2') {
      $element['#multiple'] = $spec->multiple ? TRUE : FALSE;
      $element['#autocreate'] = $spec->allowCreate ? TRUE : FALSE;
    }
    return $element;
  }

  /**
   * Helper function to get type of pick list.
   */
  public function getSelectType($spec) {
    if (isset($spec->type) && $spec->type === 'select_other') {
      return 'select_or_other_select';
    }
    elseif (isset($spec->type) && $spec->type === 'autocomplete') {
      return 'select2';
    }
    return 'select';
  }

  /**
   * Helper function to get options for dropdowns.
   */
  public function getDropdownOptions($source, $titleProperty = FALSE) {
    $options = [];
    if (isset($source->enum)) {
      $options = $this->stringHelper->getSelectOptions($source);
    }
    if (isset($source->metastoreSchema)) {
      $options = $this->getOptionsFromMetastore($source, $titleProperty);
    }
    return $options;
  }

  /**
   * Helper function to get options from metastore.
   */
  public function getOptionsFromMetastore($source, $titleProperty = FALSE) {
    $options = [];
    $values = $this->metastore->getAll($source->metastoreSchema);
    foreach ($values as $value) {
      if ($titleProperty) {
        $options[$value->data->{$titleProperty}] = $value->data->{$titleProperty};
      }
      else {
        $options[$value->data] = $value->data;
      }
    }
    return $options;
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

}
