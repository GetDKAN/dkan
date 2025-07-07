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
    $form['strict_mode_disabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable strict mode for creating/altering MySQL tables.'),
      '#description' => $this->t('This allows the MySQL importer to temporarily disable innodb_strict_mode. This is useful for datasets with very wide tables, but it may lead to unintended side effects, so use with caution.'),
      '#default_value' => $config->get('strict_mode_disabled') ?? FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('datastore_mysql_import.settings')
      ->set('remove_empty_rows', $form_state->getValue('remove_empty_rows'))
      ->set('strict_mode_disabled', $form_state->getValue('strict_mode_disabled'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
