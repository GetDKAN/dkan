<?php

namespace Drupal\datastore\Form;

use Drupal\common\DataResource;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * DKAN resource settings form.
 *
 * @package Drupal\datastore\Form
 * @codeCoverageIgnore
 */
class ResourceSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'resource_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['datastore.settings', 'metastore.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['resources'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Purge dataset resources'),
      '#description' => $this->t('Upon dataset publication, delete older revision resources if they are no longer necessary.'),
    ];
    $form['resources']['purge_table'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Datastore table'),
      '#default_value' => $this->config('datastore.settings')->get('purge_table'),
    ];
    $form['resources']['purge_file'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('File'),
      '#default_value' => $this->config('datastore.settings')->get('purge_file'),
    ];
    $form['delete_local_resource'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Delete local resource'),
      '#default_value' => $this->config('datastore.settings')->get('delete_local_resource'),
      '#description' => $this->t('Delete local copy of remote files after the datastore import is complete'),
    ];
    $form['resource_perspective_display'] = [
      '#type' => 'select',
      '#title' => $this->t('Resource download url display'),
      '#description' => $this->t('Choose to display either the source or local path to a resource file in the 
        metadata. Note that "Local URL" display only makes sense if "Delete local resource" is unchecked.'),
      '#options' => [
        DataResource::DEFAULT_SOURCE_PERSPECTIVE => $this->t('Source'),
        'local_url' => $this->t('Local URL'),
      ],
      '#default_value' => $this->config('metastore.settings')->get('resource_perspective_display')
      ?: DataResource::DEFAULT_SOURCE_PERSPECTIVE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('datastore.settings')
      ->set('purge_table', $form_state->getValue('purge_table'))
      ->set('purge_file', $form_state->getValue('purge_file'))
      ->set('delete_local_resource', $form_state->getValue('delete_local_resource'))
      ->save();
    $this->config('metastore.settings')
      ->set('resource_perspective_display', $form_state->getValue('resource_perspective_display'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
