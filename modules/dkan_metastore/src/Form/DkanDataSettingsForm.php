<?php

namespace Drupal\dkan_metastore\Form;

use Drupal\Core\Config\Config;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\dkan_metastore\SchemaPropertiesHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Metastore data settings form.
 *
 * @package Drupal\dkan_metastore\Form
 * @codeCoverageIgnore
 */
class DkanDataSettingsForm extends ConfigFormBase {

  /**
   * SchemaPropertiesHelper service.
   *
   * @var \Drupal\dkan_metastore\SchemaPropertiesHelper
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
   * @param \Drupal\dkan_metastore\SchemaPropertiesHelper $schemaHelper
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
      'dkan_metastore.settings',
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
    $config = $this->config('dkan_metastore.settings');

    $form['description'] = $this->getDescriptionMarkup();
    $form['redirect_to_datasets'] = $this->getRedirectCheckbox($config);
    $form['unset_download_url_if_empty'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Unset download URL if empty'),
      '#default_value' => $config->get('unset_download_url_if_empty') ?? 0,
      '#description' => $this->t('If enabled, and a dataset contains a distribution[].downloadURL property, the property will be unset if it is empty. For DCAT-US dataset schemas, this prevents a validation error in rare cases when a resource mapper entity is inadvertently deleted, leaving an empty downloadURL that fails validation. Leave this unchecked unless you are experiencing fatal validation errors on dataset load.'),
    ];
    $form['html_allowed_properties'] = $this->getHtmlAllowedProperties($config);
    $form['html_allowed_html'] = $this->getHtmlAllowedHtml($config);
    $form['property_list'] = $this->getPropertyList($config);
    $form['orphan'] = $this->getOrphanCleanupFields($config);
    $form['disable_json_validation'] = $this->getValidationCheckbox($config);

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
   * Builds the checkbox form element for disabling json validation.
   *
   * @param \Drupal\Core\Config\Config $config
   *   The metastore settings configuration.
   *
   * @return array
   *   The form element array.
   */
  private function getValidationCheckbox(Config $config) {
    return [
      '#type' => 'checkbox',
      '#title' => $this->t('Temporarily disable JSON validation.'),
      '#default_value' => $config->get('disable_json_validation') ?? FALSE,
      '#description' => $this->t('If you are having a problem due to invalid data and are unable to remove it, use this option to temporarily disable JSON validation and allow the invalid data to be removed. Use with caution, and consider fixing the underlying data issue as soon as possible.'),
    ];
  }

  /**
   * Builds the text box for allowed HTML elements.
   *
   * @param \Drupal\Core\Config\Config $config
   *   The metastore settings configuration.
   *
   * @return array
   *   The form element array.
   */
  private function getHtmlAllowedHtml(Config $config) {
    return [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed tags and elements for properties that allow HTML'),
      '#description' => $this->t('Comma-separated lists of allowed tags. Attributes can
        be allowed on specific tags by appending them in square braces.
        (Example: "p,br,a[href]")'),
      '#default_value' => $config->get('html_allowed_html') ?: '',
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
      '#default_value' => $config->get('html_allowed_properties') ?: [
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
   * Builds the fields for orphan handling.
   *
   * @param \Drupal\Core\Config\Config $config
   *   The metastore settings configuration.
   *
   * @return array
   *   The form element array.
   */
  private function getOrphanCleanupFields(Config $config) {
    return [
      '#type' => 'fieldset',
      '#title' => $this->t('When a dataset is deleted, the properties selected above
       will be unpublished but remain in the system. Use the options below to delete
       them'),
      'delete' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Delete referenced content after a dataset is deleted'),
        '#default_value' => $config->get('orphan.delete') ?? 0,
      ],
      'retain_for' => [
        '#type' => 'number',
        '#title' => $this->t('Number of days to keep referenced content before deletion'),
        '#default_value' => $config->get('orphan.retain_for') ?? 0,
        '#min' => 0,
        '#max' => 999,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('dkan_metastore.settings')
      ->set('redirect_to_datasets', $form_state->getValue('redirect_to_datasets'))
      ->set('unset_download_url_if_empty', $form_state->getValue('unset_download_url_if_empty'))
      ->set('property_list', $form_state->getValue('property_list'))
      ->set('html_allowed_properties', $form_state->getValue('html_allowed_properties'))
      ->set('html_allowed_html', $form_state->getValue('html_allowed_html'))
      ->set('orphan.delete', $form_state->getValue('delete'))
      ->set('orphan.retain_for', $form_state->getValue('retain_for'))
      ->set('disable_json_validation', $form_state->getValue('disable_json_validation'))
      ->save();

    // Rebuild routes, without clearing all caches.
    $this->routeBuilder->rebuild();
  }

}
