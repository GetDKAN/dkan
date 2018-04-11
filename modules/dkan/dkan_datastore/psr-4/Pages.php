<?php

namespace Dkan\Datastore;


use Dkan\Datastore\Manager\Factory;
use Dkan\Datastore\Manager\ManagerInterface;

class Pages {
  private $node;

  public function __construct($node) {
    $this->node = $node;
  }

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

  private function getDatastoreManager() {
    $resource = $this->getResource();

    try {
      $datastore_manager = Factory::create($resource);
    }
    catch (\Exception $e){
      return NULL;
    }

    return $datastore_manager;
  }

  private function chooseAManagerForm($form) {
    $managers_info = dkan_datastore_managers_info();

    $options = [];

    /* @var $manager_info \Dkan\Datastore\Manager\Info */
    foreach ($managers_info as $manager_info) {
      $options[$manager_info->getClass()] = $manager_info->getLabel();
    }
    $form['datastore_managers_selection'] = array(
      '#type' => 'select',
      '#title' => t('Choose a Datastore Manager:'),
      '#options' => $options,
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t("Save"),
    );

    return $form;
  }

  public function importForm($form, &$form_state) {

    $datastore_manager = $this->getDatastoreManager();

    if (!$datastore_manager) {
      $form = $this->chooseAManagerForm($form);
    }
    else {

      $form = $this->setStatusInfo($form, $datastore_manager);

      foreach ($datastore_manager->getConfigurableProperties() as $property => $default_value) {
        $form["datastore_manager_{$property}"] = [
          '#type' => 'textfield',
          '#title' => "{$property}",
          '#default_value' => $default_value,
        ];
      }

      $form['actions'] = array('#type' => 'actions');
      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => t("Import"),
      );

    }

    return $form;
  }

  public function importFormSubmit($form, &$form_state) {
    $values = $form_state['values'];
    $resource = $this->getResource();

    if (isset($values['datastore_managers_selection'])) {
      $class = $values['datastore_managers_selection'];


      /* @var $datastore_manager \Dkan\Datastore\Manager\ManagerInterface */
      $datastore_manager = Factory::create($resource, $class);

      if ($datastore_manager) {
        drupal_set_message("Your datastore manager choice has been saved.");
      }
      else {
        drupal_set_message("The datastore manger {$class} could not be initialized.");
      }
    } else {
      /* @var $datastore_manager \Dkan\Datastore\Manager\ManagerInterface */
      $datastore_manager = Factory::create($resource);

      $configurable_properties = [];
      foreach ($values as $property_name => $value) {
        if(substr_count($property_name, "datastore_manager_") > 0) {
          if (!empty($value)) {
            $property_name = str_replace("datastore_manager_", "", $property_name);
            $configurable_properties[$property_name] = $value;
          }
        }
      }

      $datastore_manager->setConfigurableProperties($configurable_properties);

      $datastore_manager->initializeStorage();
    }
  }

  private function setStatusInfo($form, ManagerInterface $datastore_manager) {
    $state = $datastore_manager->getStatus();

    $records_imported = $datastore_manager->numberOfRecordsImported();

    $class = get_class($datastore_manager);
    $strings[] = "<b>Importer:</b> {$class}";
    $strings[] = "<b>Records Imported:</b> {$records_imported}";

    foreach ($state as $s) {
      $strings[] = $this->datastoreStateToString($s);
    }

    $form['status'] = [
      '#type' => 'item',
      '#title' => t('Status:'),
      '#markup' => "<br>" . implode("<br>", $strings),
    ];

    return $form;
  }

  private function datastoreStateToString($state) {
    switch($state) {
      case ManagerInterface::STORAGE_UNINITIALIZED:
        return "<b>Storage:</b> Uninitialized";
      case ManagerInterface::STORAGE_INITIALIZED:
        return "<b>Storage:</b> Initialized";
      case ManagerInterface::DATA_IMPORT_UNINITIALIZED:
        return "<b>Data Importing:</b> Uninitialized";
      case ManagerInterface::DATA_IMPORT_IN_PROGRESS:
        return "<b>Data Importing:</b> In Progress";
      case ManagerInterface::DATA_IMPORT_DONE:
        return "<b>Data Importing:</b> Done";
      case ManagerInterface::DATA_IMPORT_ERROR:
        return "<b>Data Importing:</b> Error";
    }
  }

  public function deleteForm($form, &$form_state){
    $datastore_manager = $this->getDatastoreManager();
    if ($datastore_manager) {
      drupal_set_message("Delete operation not available.");
    }
    else {
      drupal_set_message("Choose a datastore manager.");
    }
    drupal_goto("/node/{$this->node->nid}/datastore");
  }

  public function deleteFormSubmit($form, &$form_state) {
  }

  public function dropForm($form, &$form_state) {
    /* @var $datastore_manager \Dkan\Datastore\Manager\ManagerInterface */
    $datastore_manager = $this->getDatastoreManager();
    if ($datastore_manager) {
      $state = $datastore_manager->getStatus();

      if ($state['storage'] == ManagerInterface::STORAGE_INITIALIZED) {
        $form['drop_info'] = array(
          '#markup' => "This operation will destroy the db table and all the data previously imported."
        );

        $form['actions'] = array('#type' => 'actions');
        $form['actions']['submit'] = array(
          '#type' => 'submit',
          '#value' => t("Drop"),
        );

        return $form;
      }
      else {
        drupal_set_message("An uninitialized storage can't be dropped.");
      }
    }
    else {
      drupal_set_message("Choose a datastore manager.");
    }
    drupal_goto("/node/{$this->node->nid}/datastore");
  }

  public function dropFormSubmit($form, &$form_state) {
    $nid = $this->node->nid;
    $datastore_manager = $this->getDatastoreManager();
    $datastore_manager->drop();

    $form_state['redirect'] = "node/{$nid}/datastore";

    drupal_set_message("The datastore for node {$nid} has been successfully dropped.");
  }

  public function unlockForm($form, &$form_state) {
    $datastore_manager = $this->getDatastoreManager();
    if ($datastore_manager) {
      drupal_set_message("Unlock operation not available.");
    }
    else {
      drupal_set_message("Choose a datastore manager.");
    }
    drupal_goto("/node/{$this->node->nid}/datastore");
  }

  public function unlockFormSubmit($form, &$form_state) {
  }

  // Move this.
  public function apiForm($form, &$form_state) {
    return $this->setupFormCommons($form);
  }
}