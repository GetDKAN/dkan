<?php

namespace Drupal\datastore_mysql_import\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Datastore MySQL Import settings form.
 *
 * @package Drupal\datastore\Form
 * @codeCoverageIgnore
 */
class DatastoreMysqlImportSettingsForm extends ConfigFormBase {

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
    return ['datastore_mysql_import.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('datastore_mysql_import.settings');
    $form['remove_empty_rows'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable removal of empty rows in dataset.'),
      '#description' => $this->t('Unlike the chunk harvester, which ignores empty rows in a CSV, the MySQL importer will import empty rows.'),
      '#default_value' => $config->get('remove_empty_rows'),
    ];
    $form['load_data_chunk_size'] = [
      '#type' => 'textfield',
      '#title' => $this->t('LOAD DATA chunk size.'),
      '#description' => $this->t('Large CSV data resources will be chunked and imported using LOAD DATA. This is the size of the chunk in records.'),
      '#default_value' => $config->get('load_data_chunk_size'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('datastore_mysql_import.settings')
      ->set('remove_empty_rows', $form_state->getValue('remove_empty_rows'))
      ->set('load_data_chunk_size', $form_state->getValue('load_data_chunk_size'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
