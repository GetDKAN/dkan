<?php

namespace Drupal\dkan_datastore\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\dkan_datastore\Controller\QueryController;
use Drupal\dkan_metastore\SchemaPropertiesHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Datastore settings form.
 *
 * @package Drupal\dkan_datastore\Form
 * @codeCoverageIgnore
 */
class DatastoreSettingsForm extends ConfigFormBase {

  /**
   * SchemaPropertiesHelper service.
   *
   * @var \Drupal\dkan_metastore\SchemaPropertiesHelper
   */
  private $schemaHelper;

  /**
   * State service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  private StateInterface $state;

  /**
   * Constructs form.
   *
   * @param \Drupal\dkan_metastore\SchemaPropertiesHelper $schemaHelper
   *   The schema properties helper service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(SchemaPropertiesHelper $schemaHelper, StateInterface $state) {
    $this->schemaHelper = $schemaHelper;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.metastore.schema_properties_helper'),
      $container->get('state'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dkan_datastore_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['dkan_datastore.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['rows_limit'] = [
      '#type' => 'number',
      '#min' => 1,
      '#title' => $this->t('Rows limit'),
      '#default_value' => $this->config('dkan_datastore.settings')->get('rows_limit'),
      '#description' => $this->t('Maximum number of rows the datastore endpoints can return
        in a single request. Caution: setting too high can lead to timeouts or memory issues.
        Default 500; values above 20,000 not recommended.'),
    ];

    $form['response_stream_max_age'] = [
      '#type' => 'number',
      '#title' => $this->t('Response Stream Max-Age'),
      '#default_value' => $this->config('dkan_datastore.settings')->get('response_stream_max_age'),
      '#min' => 0,
      '#description' => $this->t('Set the cache max-age for streaming CSV responses, in seconds. Default: 3600 (1 hour).'),
    ];

    $form['datastore_degraded_performance'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable degraded datastore query mode'),
      '#default_value' => $this->state->get('dkan_datastore.degraded_performance', FALSE),
      '#description' => $this->t('When enabled, datastore query endpoints reject requests with conditions, joins, groupings, sorts, offsets, or limits above the configured rows limit.'),
    ];

    $form['triggering_properties'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Datastore triggering properties'),
      '#description' => $this->t('Metadata properties whose change will trigger a re-import of
        an associated resource to the datastore.'),
      '#options' => $this->schemaHelper->retrieveSchemaProperties('dataset'),
      '#default_value' => $this->config('dkan_datastore.settings')->get('triggering_properties'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('dkan_datastore.settings')
      ->set('rows_limit', $form_state->getValue('rows_limit') ?: QueryController::DEFAULT_ROWS_LIMIT)
      ->set('response_stream_max_age', $form_state->getValue('response_stream_max_age'))
      ->set('triggering_properties', $form_state->getValue('triggering_properties'))
      ->save();
    $this->state->set(
      'dkan_datastore.degraded_performance',
      (bool) $form_state->getValue('datastore_degraded_performance')
    );
    parent::submitForm($form, $form_state);
  }

}
