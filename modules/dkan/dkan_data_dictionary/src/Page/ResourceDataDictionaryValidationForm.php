<?php

namespace Dkan\DataDictionary\Page;

use Dkan\DataDictionary\Resource;
use Dkan\DataDictionary\ValidationReportControllerD7;

/**
 * Class Page.
 *
 * Generates the page that we use to manage the movement of resources
 * into the datastore.
 */
class ResourceDataDictionaryValidationForm implements FormInterface {

  const BATCH_ITERATIONS = 1;
  const BATCH_TIME_LIMIT = 5;

  protected $resource;

  /**
   * {@inheritdoc}
   */
  public function __construct($node) {
    /* @var $resource \Dkan\DataDictionary\Resource */
    $this->resource = Resource::createFromDrupalNode($node);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array $form_state) {
    $form['container'] = array(
      '#type' => 'container',
    );

    try {
      $controller = new ValidationReportControllerD7();
      $vrs = $controller->load(array(), array('entity_id' => $this->resource->getIdentifier()));

      $vr = NULL;
      if (!empty($vrs)) {
        $vr = array_pop($vrs);
      }

      if (empty($vr)) {
        $form['container']['report'] = array(
          '#markup' => t("No Reports Found."),
        );
      }
      else {
        $report = $vr->reportFormatterView();
        $form['container']['report'] = $report;
      }

      $validate = array();
      if (empty($this->resource->getDataDictSchemaType())
      || empty($this->resource->getDataDictSchema())) {
        // TODO improve. add link to docs?
        $validate = array(
          '#type' => 'submit',
          '#value' => t('Validate (Missing Schema Info)'),
          '#disabled' => TRUE,
        );

      }
      else {
        $validate = array(
          '#type' => 'submit',
          '#value' => t('Validate'),
          '#disabled' => FALSE,
        );
      }

      $form['container']['validate'] = $validate;
    }
    catch (\Exception $e) {
      // TODO log and update form page content.
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array $form_state) {
    if (empty($describedby_spec = $this->resource->getDataDictSchemaType())
      || empty($describedby_schema = $this->resource->getDataDictSchema())) {
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array $form_state) {
    try {
      $batch = array(
        'operations' => [],
        'finished' => [$this, 'batchFinished'],
        'title' => t('Importing.'),
        'init_message' => t('Starting Import.'),
        'progress_message' => t('Processed @current out of @total.'),
        'error_message' => t('An error occurred during import.'),
      );

      for ($i = 0; $i < self::BATCH_ITERATIONS; $i++) {
        $batch['operations'][] = [[$this, 'batchProcess'], [$this->resource]];
      }

      batch_set($batch);
    }
    catch (\Exception $e) {
      // TODO log exception.
    }
  }

  /**
   * Batch event handler.
   */
  public function batchProcess($resource, &$context) {
    stream_wrapper_restore("https");
    stream_wrapper_restore("http");

    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = 1;
    }

    if (!isset($context['sandbox']['manager'])) {
      $describedby_spec = $resource->getDataDictSchemaType();
      $validatorWrapper = dkan_data_dictionary_dictionary_load($describedby_spec);
      $manager = $validatorWrapper->getDataDictionaryManager($this->resource);

      $context['sandbox']['manager'] = $manager;
    }

    $step = 20;
    $context['sandbox']['manager']->validate($step);

    $context['sandbox']['progress']++;

    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Batch event handler.
   */
  public function batchFinished($success, $results, $operations) {
    drupal_set_message(t("The batch process completed successfully."));
  }

  /**
   * Setting up the batch process for importing a file.
   */
  private function batchConfiguration(ManagerInterface $manager) {
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dkan_data_dictionary_resource_report_form';
  }

}
