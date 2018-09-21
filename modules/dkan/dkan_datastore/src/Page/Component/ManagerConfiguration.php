<?php

namespace Dkan\Datastore\Page\Component;

use Dkan\Datastore\Manager\ManagerInterface;

/**
 * Class ManagerConfiguration.
 *
 * Form component to configure a datastore manager.
 */
class ManagerConfiguration {

  private $datastoreManager;

  /**
   * Constructor.
   */
  public function __construct(ManagerInterface $manager) {
    $this->datastoreManager = $manager;
  }

  /**
   * Get form.
   */
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
          // @codingStandardsIgnoreStart
          '#title' => ucfirst(t("{$property}")),
          // @codingStandardsIgnoreEnd
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
          // @codingStandardsIgnoreStart
          '#title' => ucfirst(t("{$property}")),
          // @codingStandardsIgnoreEnd
          '#default_value' => $default_value,
        ];
      }
    }

    return $form;
  }

  /**
   * Submit.
   */
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
