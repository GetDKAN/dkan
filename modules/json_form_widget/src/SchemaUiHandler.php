<?php

namespace Drupal\json_form_widget;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\metastore\SchemaRetriever;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

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
   */
  public function __construct(SchemaRetriever $schema_retriever, LoggerChannelFactory $logger_factory, WidgetRouter $widget_router) {
    $this->schemaRetriever = $schema_retriever;
    $this->schemaUi = FALSE;
    $this->loggerFactory = $logger_factory;
    $this->widgetRouter = $widget_router;
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
    if (isset($spec->{"ui:options"}->hideActions)) {
      $element = $this->widgetRouter->flattenArrays($spec->{"ui:options"}, $element);
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
      return $this->widgetRouter->getConfiguredWidget($spec, $element);
    }
    else {
      return $element;
    }
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
