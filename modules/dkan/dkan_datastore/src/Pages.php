<?php

namespace Dkan\Datastore;

use Dkan\Datastore\Manager\Factory;
use Dkan\Datastore\Manager\ManagerInterface;
use Dkan\Datastore\Manager\Manager;

/**
 * Class Pages.
 */
class Pages {

  const BATCH_ITERATIONS = 1;
  const BATCH_TIME_LIMIT = 5;

  private $node;

  /**
   * Pages constructor.
   */
  public function __construct($node) {
    $this->node = $node;
  }

  /**
   * Private method.
   */
  private function getResource() {
    try {
      $resource = Resource::createFromDrupalNode($this->node);
    }
    catch (\Exception $e) {
      drupal_set_message("The datastore does not support node {$this->node->nid}: {$e->getMessage()}");
      drupal_goto("/node/{$this->node->nid}");
    }

    return $resource;
  }

  /**
   * Private method.
   */
  private function getDatastoreManager() {
    $resource = $this->getResource();

    try {
      $datastore_manager = Factory::create($resource);
    }
    catch (\Exception $e) {
      return NULL;
    }

    return $datastore_manager;
  }

  /**
   * Create form element for chosing datastore manager. This is currently
   * included in the impoort form and not a separate form page.
   */
  private function chooseManagerForm($form, $datastore_manager = NULL) {
    $managers_info = dkan_datastore_managers_info();
    $class = isset($datastore_manager) ? get_class($datastore_manager) : NULL;
    $options = [];

    /* @var $manager_info \Dkan\Datastore\Manager\Info */
    foreach ($managers_info as $manager_info) {
      $options[$manager_info->getClass()] = $manager_info->getLabel();
    }
    $form['datastore_managers_selection'] = array(
      '#type' => 'select',
      '#title' => t('Change datastore importer:'),
      '#options' => $options,
      '#default_value' => "\\$class",
    );

    return $form;
  }

  /**
   * Import form.
   */
  public function importForm($form, &$form_state) {
    if (!isset($form_state['storage']['drop'])) {
      $datastore_manager = $this->getDatastoreManager();
      $status = $datastore_manager->getStatus();

      $form += $this->setStatusInfo($form, $datastore_manager);
      $form += $this->chooseManagerForm($form, $datastore_manager);

      $form['import_options'] = [
        '#type' => 'fieldset',
        '#title' => t('Import options'),
        '#collapsible' => FALSE,
      ];
      foreach ($datastore_manager->getConfigurableProperties() as $property => $default_value) {
        if ($property == "delimiter") {
          $form['import_options']["datastore_manager_{$property}"] = array(
            '#type' => 'select',
            '#title' => ucfirst(t("{$property}")),
            '#options' => array(
              "," => ",",
              ";" => ";",
              "|" => "|",
              "\t" => "TAB",
            ),
            '#default_value' => $default_value,
          );
        }
        else {
          $form['import_options']["datastore_manager_{$property}"] = [
            '#type' => 'textfield',
            '#title' => ucfirst(t("{$property}")),
            '#default_value' => $default_value,
          ];
        }
      }

      $form['actions'] = array('#type' => 'actions');
      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => t("Import"),
      );

      if (in_array($status['data_import'], [Manager:: DATA_IMPORT_IN_PROGRESS, Manager::DATA_IMPORT_DONE])) {
        $form['actions']['drop'] = array(
          '#type' => 'submit',
          '#value' => t("Drop"),
          '#submit' => array('dkan_datastore_drop_submit'),
        );
      }
    }
    else {
      $form = $this->dropForm($form, $form_state);
    }
    return $form;
  }

  /**
   * Form Submit.
   */
  public function importFormSubmit($form, &$form_state) {
    $message = "";

    $values = $form_state['values'];
    $resource = $this->getResource();

    if (isset($values['datastore_managers_selection'])) {
      $class = $values['datastore_managers_selection'];

      /* @var $datastore_manager \Dkan\Datastore\Manager\ManagerInterface */
      $datastore_manager = Factory::create($resource, $class);

      if (!$datastore_manager) {
        $message = "The datastore manger {$class} could not be initialized.";
        return;
      }
    }
    else {
      /* @var $datastore_manager \Dkan\Datastore\Manager\ManagerInterface */
      $datastore_manager = Factory::create($resource);
    }

    $configurable_properties = [];
    foreach ($values as $property_name => $value) {
      if (substr_count($property_name, "datastore_manager_") > 0) {
        if (!empty($value)) {
          $property_name = str_replace("datastore_manager_", "", $property_name);
          $configurable_properties[$property_name] = $value;
        }
      }
    }

    $datastore_manager->setConfigurableProperties($configurable_properties);
    $datastore_manager->saveState();

    if (!empty($message)) {
      drupal_set_message($message);
    }
    else {
      $this->batchConfiguration($datastore_manager);
    }
  }

  private function batchConfiguration($datastore_manager) {
    /* @var $datastore_manager ManagerInterface */
    $datastore_manager->setImportTimelimit(self::BATCH_TIME_LIMIT);
    $batch = array(
      'operations' => [],
      'finished' => [$this, 'batchFinished'],
      'title' => t('Importing.'),
      'init_message' => t('Starting Import.'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message' => t('An error occurred during import.'),
    );

    for ($i = 0; $i < self::BATCH_ITERATIONS; $i++) {
      $batch['operations'][] = [[$this, 'batchProcess'], [$datastore_manager]];
    }

    batch_set($batch);
  }

  public function batchProcess($datastore_manager, &$context) {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = 1;
    }
    /* @var $datastore_manager ManagerInterface */
    $datastore_manager->import();
    $context['sandbox']['progress']++;

    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  public function batchFinished($success, $results, $operations) {
    drupal_set_message("Import finished");
  }

  /**
   * Private method.
   */
  private function setStatusInfo($form, ManagerInterface $datastore_manager) {
    $state = $datastore_manager->getStatus();
    $stringSubs = [
      '%class' => get_class($datastore_manager),
      '%records' => $datastore_manager->numberOfRecordsImported(),
      '%import' => $this->datastoreStateToString($state['data_import']),
    ];

    $statusInfo = t("<dt>Importer</dt><dd>%class</dd>", $stringSubs);
    $statusInfo .= t("<dt>Records Imported</dt><dd>%records</dd>", $stringSubs);
    $statusInfo .= t("<dt>Data Importing</dt><dd>%import</dd>", $stringSubs);

    $form['status'] = [
      '#type' => 'item',
      '#title' => t('Datastore Status'),
      '#markup' => "<dl>$statusInfo</dl>"
    ];

    return $form;
  }

  /**
   * Private method.
   */
  private function datastoreStateToString($state) {
    switch ($state) {
      case ManagerInterface::STORAGE_UNINITIALIZED:
        return t("Uninitialized");

      case ManagerInterface::STORAGE_INITIALIZED:
        return t("Initialized");

      case ManagerInterface::DATA_IMPORT_UNINITIALIZED:
        return t("Uninitialized");

      case ManagerInterface::DATA_IMPORT_IN_PROGRESS:
        return t("In Progress");

      case ManagerInterface::DATA_IMPORT_DONE:
        return t("Done");

      case ManagerInterface::DATA_IMPORT_ERROR:
        return t("Error");

      case ManagerInterface::DATA_IMPORT_READY:
        return t("Ready");
    }
  }

  /**
   * Drop form.
   */
  public function dropForm($form, &$form_state) {
    $node = $form['#node'];

    $question = t('Are you sure you want to drop this datastore?');
    $path = 'node/' . $node->nid . '/datastore';
    $description = t('This operation will destroy the db table and all the data previously imported.');
    $yes = t('Drop');
    $no = t('Cancel');
    $name = 'drop';

    return confirm_form($form, $question, $path, $description, $yes, $no, $name);
  }

  /**
   * Form Submit.
   */
  public function dropFormSubmit($form, &$form_state) {
    $nid = $this->node->nid;
    $datastore_manager = $this->getDatastoreManager();
    $datastore_manager->drop();

    $form_state['redirect'] = "node/{$nid}/datastore";

    drupal_set_message(t("The datastore for %title has been successfully dropped.", ['%title' => $this->node->title]));
  }

  /**
   * Api Form.
   *
   * @todo this does not belong here.
   */
  public function apiForm($form, &$form_state) {
    return $this->setupFormCommons($form);
  }

}
