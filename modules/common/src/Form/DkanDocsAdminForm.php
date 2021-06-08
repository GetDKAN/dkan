<?php

namespace Drupal\common\Form;

use Drupal\common\Plugin\DkanApiDocsGenerator;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * DKAN Docs admin page.
 *
 * @package Drupal\common\Form
 * @codeCoverageIgnore
 */
class DkanDocsAdminForm extends ConfigFormBase {

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dkan_api_form';
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#markup' => $this->t(
        'Select properties from the dataset schema to be available as individual objects. 
        Each property will be assigned a unique identifier in addition to its original schema value.'
      ),
    ];
    $form['api_docs'] = [
      '#type' => 'openapi_ui',
      '#openapi_ui_plugin' => 'swagger',
      '#openapi_schema' => DkanApiDocsGenerator::create(\Drupal::getContainer())->buildSpec(),
    ];
    $form['#theme'] = 'system_config_form';
    return $form;
  }

}
