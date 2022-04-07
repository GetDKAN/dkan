<?php

namespace Drupal\metastore\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\metastore\SchemaPropertiesHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Metastore data settings form.
 *
 * @package Drupal\metastore\Form
 * @codeCoverageIgnore
 */
class DkanDataSettingsForm extends ConfigFormBase {

  /**
   * SchemaPropertiesHelper service.
   *
   * @var \Drupal\metastore\SchemaPropertiesHelper
   */
  private $schemaHelper;

  /**
   * Route Builder service.
   *
   * @var \Drupal\Core\Routing\RouteBuilder
   */
  private $routeBuilder;

  /**
   * Constructs form.
   *
   * @param \Drupal\metastore\SchemaPropertiesHelper $schemaHelper
   *   The schema properties helper service.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $routeBuilder
   *   The route builder service.
   */
  public function __construct(SchemaPropertiesHelper $schemaHelper, RouteBuilderInterface $routeBuilder) {
    $this->schemaHelper = $schemaHelper;
    $this->routeBuilder = $routeBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.metastore.schema_properties_helper'),
      $container->get('router.builder')
    );
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'metastore.settings',
    ];
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metastore_settings_form';
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('metastore.settings');
    $options = $this->schemaHelper->retrieveSchemaProperties('dataset');
    $default_values = $config->get('property_list');
    $form['description'] = [
      '#markup' => $this->t(
        'Select properties from the dataset schema to be available as individual objects.
        Each property will be assigned a unique identifier in addition to its original schema value.'
      ),
    ];
    $form['property_list'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Dataset properties'),
      '#options' => $options,
      '#default_value' => $default_values,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('metastore.settings')
      ->set('property_list', $form_state->getValue('property_list'))
      ->save();

    // Rebuild routes, without clearing all caches.
    $this->routeBuilder->rebuild();
  }

}
