<?php

namespace Drupal\datastore\Form;

use Drupal\common\DataResource;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\RedundantEditableConfigNamesTrait;
use Drupal\datastore\Service\ResourceLocalizer;

/**
 * DKAN resource settings form.
 *
 * @package Drupal\datastore\Form
 * @codeCoverageIgnore
 */
class ResourceSettingsForm extends ConfigFormBase {
  use RedundantEditableConfigNamesTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'resource_settings_form';
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
      '#config_target' => 'datastore.settings:purge_table',
    ];
    $form['resources']['purge_file'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('File'),
      '#config_target' => 'datastore.settings:purge_file',
    ];
    $form['delete_local_resource'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Delete local resource'),
      '#config_target' => 'datastore.settings:delete_local_resource',
      '#description' => $this->t('Delete local copy of remote files after the datastore import is complete'),
    ];
    $form['drop_datastore_on_post_import_error'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Drop the datastore table if the post import queue reports an error.'),
      '#config_target' => 'datastore.settings:drop_datastore_on_post_import_error',
      '#description' => $this->t('The datastore import queue brings in all columns as strings. The post import
      queue will alter the table according to the data dictionary, if there is a problem during this step the
      error will be posted to the Datastore Import Status dashboard, and the datastore table will keep all
      data typed as strings. Check this box if you prefer that the table be dropped if there is a problem
      in the post import stage.'),
    ];
    $form['resource_perspective_display'] = [
      '#type' => 'select',
      '#title' => $this->t('Resource download url display'),
      '#description' => $this->t('Choose to display either the source or local path to a resource file in the
        metadata. Note that "Local URL" display only makes sense if "Delete local resource" is unchecked.'),
      '#options' => [
        DataResource::DEFAULT_SOURCE_PERSPECTIVE => $this->t('Source'),
        ResourceLocalizer::LOCAL_URL_PERSPECTIVE => $this->t('Local URL'),
      ],
      '#config_target' => 'metastore.settings:resource_perspective_display',
    ];
    return parent::buildForm($form, $form_state);
  }

}
