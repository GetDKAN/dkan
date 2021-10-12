<?php

namespace Drupal\datastore\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datastore\Controller\QueryController;
use Drupal\metastore\SchemaPropertiesHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Datastore settings form.
 *
 * @package Drupal\datastore\Form
 * @codeCoverageIgnore
 */
class DatastoreSettingsForm extends ConfigFormBase {

  /**
   * SchemaPropertiesHelper service.
   *
   * @var \Drupal\metastore\SchemaPropertiesHelper
   */
  private $schemaHelper;

  /**
   * Constructs form.
   *
   * @param \Drupal\metastore\SchemaPropertiesHelper $schemaHelper
   *   The schema properties helper service.
   */
  public function __construct(SchemaPropertiesHelper $schemaHelper) {
    $this->schemaHelper = $schemaHelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.metastore.schema_properties_helper'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'datastore_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['datastore.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['rows_limit'] = [
      '#type' => 'number',
      '#min' => 1,
      '#title' => $this->t('Rows limit'),
      '#default_value' => $this->config('datastore.settings')->get('rows_limit'),
      '#description' => $this->t('Maximum number of rows the datastore endpoints can return 
        in a single request. Caution: setting too high can lead to timeouts or memory issues. 
        Default 500; values above 20,000 not recommended.'),
    ];

    $form['triggering_properties'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Datastore triggering properties'),
      '#description' => $this->t('Metadata properties whose change will trigger a re-import of 
        an associated resource to the datastore.'),
      '#options' => $this->schemaHelper->retrieveSchemaProperties('dataset'),
      '#default_value' => $this->config('datastore.settings')->get('triggering_properties'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('datastore.settings')
      ->set('rows_limit', $form_state->getValue('rows_limit') ?: QueryController::DEFAULT_ROWS_LIMIT)
      ->set('triggering_properties', $form_state->getValue('triggering_properties'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
