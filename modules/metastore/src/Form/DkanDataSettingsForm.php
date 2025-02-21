<?php

namespace Drupal\metastore\Form;

use Drupal\Core\Config\Config;
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
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('metastore.settings');

    $form['description'] = $this->getDescriptionMarkup();
    $form['redirect_to_datasets'] = $this->getRedirectCheckbox($config);
    $form['html_allowed_properties'] = $this->getHtmlAllowedProperties($config);
    $form['property_list'] = $this->getPropertyList($config);

    return parent::buildForm($form, $form_state);
  }

  /**
   * Provides a markup description for the Metastore settings form.
   *
   * @return array
   *   Render array containing the form description.
   */
  private function getDescriptionMarkup() {
    return [
      '#markup' => $this->t('Configure the metastore settings.'),
    ];
  }

  /**
   * Builds the checkbox form element for redirecting after form submission.
   *
   * @param \Drupal\Core\Config\Config $config
   *   The metastore settings configuration.
   *
   * @return array
   *   The form element array.
   */
  private function getRedirectCheckbox(Config $config) {
    return [
      '#type' => 'checkbox',
      '#title' => $this->t('Redirect to datasets view after form submit'),
      '#default_value' => $config->get('redirect_to_datasets'),
      '#description' => $this->t("Disable this option if you want to use Drupal's default or your own custom redirect after submitting a metadata form."),
    ];
  }

  /**
   * Builds the checkboxes for dataset properties that allow HTML.
   *
   * @param \Drupal\Core\Config\Config $config
   *   The metastore settings configuration.
   *
   * @return array
   *   The form element array.
   */
  private function getHtmlAllowedProperties(Config $config) {
    return [
      '#type' => 'checkboxes',
      '#title' => $this->t('Dataset properties that allow HTML'),
      '#description' => $this->t('Metadata properties that may contain
        HTML elements.'),
      '#options' => $this->schemaHelper->retrieveStringSchemaProperties(),
      '#default_value' => $config->get('html_allowed_properties')
      ?: [
        'dataset_description',
        'distribution_description',
      ],
    ];
  }

  /**
   * Builds the checkboxes for dataset properties stored as separate entities.
   *
   * @param \Drupal\Core\Config\Config $config
   *   The metastore settings configuration.
   *
   * @return array
   *   The form element array.
   */
  private function getPropertyList(Config $config) {
    return [
      '#type' => 'checkboxes',
      '#title' => $this->t('Dataset properties to be stored as separate
        entities; use caution'),
      '#description' => $this->t('Select properties from the dataset schema
        to be available as individual objects. Each property will be assigned
        a unique identifier in addition to its original schema value.'),
      '#options' => $this->schemaHelper->retrieveSchemaProperties(),
      '#default_value' => $config->get('property_list'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('metastore.settings')
      ->set('redirect_to_datasets', $form_state->getValue('redirect_to_datasets'))
      ->set('property_list', $form_state->getValue('property_list'))
      ->set('html_allowed_properties', $form_state->getValue('html_allowed_properties'))
      ->save();

    // Rebuild routes, without clearing all caches.
    $this->routeBuilder->rebuild();
  }

}
