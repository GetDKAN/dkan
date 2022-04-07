<?php

namespace Drupal\json_form_widget;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\metastore\SchemaRetriever;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

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
      $container->get('dkan.metastore.schema_retriever'),
      $container->get('logger.factory'),
      $container->get('json_form.widget_router')
    );
  }

  /**
   * Constructor.
   *
   * @param Drupal\metastore\SchemaRetriever $schema_retriever
   *   SchemaRetriever service.
   * @param Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory service.
   * @param WidgetRouter $widget_router
   *   WidgetRouter service.
   */
  public function __construct(SchemaRetriever $schema_retriever, LoggerChannelFactory $logger_factory, WidgetRouter $widget_router) {
    $this->schemaRetriever = $schema_retriever;
    $this->schemaUi = FALSE;
    $this->loggerFactory = $logger_factory;
    $this->widgetRouter = $widget_router;
  }

  /**
   * Set schemaUi.
   *
   * @param mixed $schema_name
   *   The schema name.
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
  public function handlePropertySpec($property, $spec, $element, bool $in_array = FALSE) {
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
  public function applyOnBaseField($spec, array $element) {
    if (isset($spec->{"ui:options"})) {
      $element = $this->updateWidgets($spec->{"ui:options"}, $element);
      $element = $this->disableFields($spec->{"ui:options"}, $element);
      $element = $this->addPlaceholders($spec->{"ui:options"}, $element);
      $element = $this->changeFieldDescriptions($spec->{"ui:options"}, $element);
      $element = $this->changeFieldTitle($spec->{"ui:options"}, $element);
      if (isset($spec->{"ui:options"}->hideActions)) {
        $element = $this->widgetRouter->flattenArrays($spec->{"ui:options"}, $element);
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
   *
   * @return array
   *   Render array for the element with schema UI applied.
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
   *
   * @param mixed $spec
   *   Object with spec for UI options.
   * @param array $element
   *   Element to apply UI options.
   *
   * @return array
   *   Element with configurations about widget.
   */
  public function updateWidgets($spec, array $element) {
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
  public function disableFields($spec, array $element) {
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
  public function addPlaceholders($spec, array $element) {
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
  public function changeFieldDescriptions($spec, array $element) {
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
  public function changeFieldTitle($spec, array $element) {
    if (isset($spec->title)) {
      $element['#title'] = $spec->title;
    }
    return $element;
  }

}
