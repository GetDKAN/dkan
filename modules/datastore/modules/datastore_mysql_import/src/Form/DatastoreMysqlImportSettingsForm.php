<?php

namespace Drupal\datastore_mysql_import\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Datastore MySQL Import settings form.
 *
 * @package Drupal\datastore\Form
 * @codeCoverageIgnore
 */
class DatastoreMysqlImportSettingsForm extends ConfigFormBase {


  /**
   * Constructs form.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'datastore_mysql_import_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['datastore.mysql_import.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('datastore.mysql_import.settings');
    $form['remove_empty_rows'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable removal of empty rows in dataset.'),
      '#description' => $this->t('Unlike the chunk harvester, which ignores empty rows in a CSV, the MySQL importer will import empty rows.'),
      '#default_value' => $config->get('remove_empty_rows'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('datastore.mysql_import.settings')
      ->set('remove_empty_rows', $form_state->getValue('remove_empty_rows'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
