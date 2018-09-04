<?php

namespace Dkan\Datastore\Page\Component;

use Dkan\Datastore\Manager\ManagerInterface;

class ManagerConfiguration {

  private $datastoreManager;

  public function __construct(ManagerInterface $datastore_manager) {
    $this->datastoreManager = $datastore_manager;
  }

  public function getForm() {
    $form = [];
    $form['import_options'] = [
      '#type' => 'fieldset',
      '#title' => t('Import options'),
      '#collapsible' => FALSE,
    ];
    foreach ($this->datastoreManager->getConfigurableProperties() as $property => $default_value) {
      if ($property == "delimiter") {
        $form['import_options']["datastore_manager_config_{$property}"] = array(
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
        $form['import_options']["datastore_manager_config_{$property}"] = [
          '#type' => 'textfield',
          '#title' => ucfirst(t("{$property}")),
          '#default_value' => $default_value,
        ];
      }
    }

    return $form;
  }

  public function submit($value) {
    $configurable_properties = [];
    foreach ($value as $property_name => $v) {
      if (!empty($v)) {
        $pname = str_replace("datastore_manager_config_", "", $property_name);
        $configurable_properties[$pname] = $v;
      }
    }

    $this->datastoreManager->setConfigurableProperties($configurable_properties);
    $this->datastoreManager->saveState();
  }

}