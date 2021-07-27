<?php

namespace Drupal\datastore\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\metastore\SchemaPropertiesHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DatastoreSettingsForm.
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
    $form['fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Triggering properties'),
      '#description' => $this->t('Property to trigger update of the datastore.'),
    ];
    $form['fieldset']['triggering_properties'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Datastore triggering properties'),
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
      ->set('triggering_properties', $form_state->getValue('triggering_properties'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
