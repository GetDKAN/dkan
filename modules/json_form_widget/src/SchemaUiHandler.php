<?php

namespace Drupal\json_form_widget;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * JSON form widget schema UI handler service.
 */
class SchemaUiHandler implements ContainerInjectionInterface {

  /**
   * Schema.
   *
   * @var object
   */
  public $schemaUi;

  /**
   * Json form widget logger channel service.
   */
  private LoggerInterface $logger;

  /**
   * WidgetRotuer Service.
   *
   * @var \Drupal\json_form_widget\WidgetRouter
   */
  protected $widgetRouter;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.json_form.logger_channel'),
      $container->get('json_form.widget_router')
    );
  }

  /**
   * Constructor.
   *
   * @param \Psr\Log\LoggerInterface $loggerChannel
   *   Logger channel service.
   * @param WidgetRouter $widget_router
   *   WidgetRouter service.
   */
  public function __construct(
    LoggerInterface $loggerChannel,
    WidgetRouter $widget_router,
  ) {
    $this->schemaUi = FALSE;
    $this->logger = $loggerChannel;
    $this->widgetRouter = $widget_router;
  }

  /**
   * Set schemaUi.
   *
   * @param object|null $ui_schema
   *   The UIschema, as decoded JSON object.
   *
   * @todo Eliminate inconsistencies between "schemaUI" and "uiSchema"
   */
  public function setSchemaUi(?object $ui_schema = NULL) {
    $this->schemaUi = $ui_schema;
  }

  /**
   * Get Schema UI object.
   *
   * @return object
   *   Schema UI object.
   */
  public function getSchemaUi() {
    return $this->schemaUi;
  }

  /**
   * Apply schema UI to form.
   *
   * @param mixed $form
   *   The form to which the Schema UI should be applied.
   *
   * @return array
   *   Form with Schema UI applied.
   */
  public function applySchemaUi(mixed $form) {
    if ($this->schemaUi) {
      $weights = $this->getFieldWeights();
      if (!empty($weights)) {
        uksort($form, function ($a, $b) use ($weights) {
          return ($weights[$a] ?? 0) <=> ($weights[$b] ?? 0);
        });
      }

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
   *
   * @param mixed $property
   *   Name of the property field.
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param mixed $element
   *   Element to apply UI options.
   * @param bool $in_array
   *   Wether the property is inside of an array.
   *
   * @return array
   *   Render array for the element with schema UI applied.
   */
  public function handlePropertySpec(mixed $property, mixed $spec, mixed $element, bool $in_array = FALSE) {
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
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to apply UI options.
   *
   * @return array
   *   Element with widget configuration based on UI options.
   */
  public function applyOnBaseField(mixed $spec, array $element) {
    if (isset($spec->{"ui:options"})) {
      $element = $this->updateWidgets($spec->{"ui:options"}, $element);
      $element = $this->disableFields($spec->{"ui:options"}, $element);
      $element = $this->addPlaceholders($spec->{"ui:options"}, $element);
      $element = $this->changeFieldDescriptions($spec->{"ui:options"}, $element);
      $element = $this->changeFieldTitle($spec->{"ui:options"}, $element);
      if (isset($spec->{"ui:options"}->hideActions)) {
        $element = $this->flattenArrays($spec->{"ui:options"}, $element);
      }
    }
    return $element;
  }

  /**
   * Flatten array elements and unset actions if hideActions is set.
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to apply UI options.
   *
   * @return array
   *   Return flattened element without actions.
   */
  public function flattenArrays(mixed $spec, array $element) {
    unset($element['array_actions']);
    $default_value = [];
    foreach ($element[$spec->child] as $key => $item) {
      $default_value = array_merge($default_value, $this->formatArrayDefaultValue($item));
      if ($key != 0) {
        unset($element[$spec->child][$key]);
      }
    }

    if (isset($element[$spec->child][0]['field'])) {
      $element[$spec->child][0]['field']['#default_value'] = $default_value;
    }
    else {
      $element[$spec->child][0]['#default_value'] = $default_value;
    }
    return $element;
  }

  /**
   * Format default values for arrays (flattened).
   */
  private function formatArrayDefaultValue($item) {
    if (!empty($item['#default_value'])) {
      return [$item['#default_value'] => $item['#default_value']];
    }
    if (!empty($item['field']['#default_value'])) {
      return [$item['field']['#default_value'] => $item['field']['#default_value']];
    }
    return [];
  }

  /**
   * Apply schema UI to object fields.
   *
   * @param mixed $property
   *   Name of the property field.
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param mixed $element
   *   Element to apply UI options.
   *
   * @return array
   *   Render array for the element with schema UI applied.
   */
  public function applyOnObjectFields(mixed $property, mixed $spec, mixed $element) {
    foreach ((array) $spec as $field => $sub_spec) {
      if (isset($element[$property][$field])) {
        $element[$property][$field] = $this->applyOnBaseField($sub_spec, $element[$property][$field]);
      }
    }
    return $element;
  }

  /**
   * Apply schema UI to object fields.
   *
   * @param mixed $property
   *   Name of the property field.
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param mixed $element
   *   Element to apply UI options.
   * @param mixed $fields
   *   List of fields from array.
   *
   * @return array
   *   Render array for the element with schema UI applied.
   */
  public function applyOnArrayFields(mixed $property, mixed $spec, mixed $element, mixed $fields) {
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
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to apply UI options.
   *
   * @return array
   *   Element with configurations about widget.
   */
  public function updateWidgets(mixed $spec, array $element) {
    if (isset($spec->widget)) {
      return $this->widgetRouter->getConfiguredWidget($spec, $element);
    }
    else {
      return $element;
    }
  }

  /**
   * Helper function for disabling fields.
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to apply UI options.
   *
   * @return array
   *   Element with hints about whether it should be disabled.
   */
  public function disableFields(mixed $spec, array $element) {
    if (isset($spec->disabled)) {
      $element['#disabled'] = TRUE;
    }
    return $element;
  }

  /**
   * Helper function for adding placeholders.
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to apply UI options.
   *
   * @return array
   *   Element with placeholder info.
   */
  public function addPlaceholders(mixed $spec, array $element) {
    if (isset($spec->placeholder)) {
      $element['#attributes']['placeholder'] = $spec->placeholder;
    }
    return $element;
  }

  /**
   * Helper function for changing help text.
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to apply UI options.
   *
   * @return array
   *   Element with description/help text.
   */
  public function changeFieldDescriptions(mixed $spec, array $element) {
    if (isset($spec->description)) {
      $element['#description'] = $spec->description;
      $element['#description_display'] = 'before';
    }
    return $element;
  }

  /**
   * Helper function for changing title.
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to apply UI options.
   *
   * @return array
   *   Element with title overriden.
   */
  public function changeFieldTitle(mixed $spec, array $element) {
    if (isset($spec->title)) {
      $element['#title'] = $spec->title;
    }
    return $element;
  }

  /**
   * Extracts weights from the UI schema.
   *
   * @return array
   *   An array of weights by field name.
   */
  public function getFieldWeights(): array {
    $weights = [];

    foreach ((array) $this->schemaUi ?? [] as $property => $spec) {
      $weights[$property] = $spec->{"ui:options"}->weight ?? 0;
    }

    return $weights;
  }

}
