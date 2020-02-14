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

    // Get the system's list of available encodings.
    $options = mb_list_encodings();
    // Make the key/values the same in the array.
    $options = array_combine($options, $options);
    // Sort alphabetically not-case sensitive.
    natcasesort($options);

    foreach ($this->datastoreManager->getConfigurableProperties() as $property => $default_value) {
      $propety_label = ucfirst(str_replace("_", " ", $property));
      if ($property == "encoding") {
        $form['import_options']["datastore_manager_config_{$property}"] = array(
          '#type' => 'select',
          // @codingStandardsIgnoreStart
          '#title' => t('Character encoding of file'),
          // @codingStandardsIgnoreEnd
          '#options' => $options,
          '#states' => [
            'visible' => [
              ':input[name="datastore_managers_selection"]' => [
                '!value' => '\Dkan\Datastore\Manager\FastImport\FastImport'
              ]
            ]
          ],
          '#default_value' => $default_value,
        );
      }
      elseif ($property == "delimiter") {
        $form['import_options']["datastore_manager_config_{$property}"] = array(
          '#type' => 'select',
          // @codingStandardsIgnoreStart
          '#title' => t($propety_label),
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
      elseif ($property == "trailing_delimiter") {
        $form['import_options']["datastore_manager_config_{$property}"] = array(
          '#type' => 'checkbox',
          // @codingStandardsIgnoreStart
          '#title' => t($propety_label),
          // @codingStandardsIgnoreEnd
          '#default_value' => $default_value,
        );
      }
      else {
        $form['import_options']["datastore_manager_config_{$property}"] = [
          '#type' => 'textfield',
          // @codingStandardsIgnoreStart
          '#title' => t($propety_label),
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
        if ($pname == "trailing_delimiter") {
          $configurable_properties[$pname] = ($v == 1) ? TRUE : FALSE;
        }
        else {
          $configurable_properties[$pname] = $v;
        }
      }
    }

    $this->datastoreManager->setConfigurableProperties($configurable_properties);
    $this->datastoreManager->saveState();
  }

}
