<?php

namespace Dkan\Datastore\Page\Component;

use Dkan\Datastore\Manager\CharsetEncoding;
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
      $propety_label = ucfirst(str_replace("_", " ", $property));

      if ($property == "encoding") {
        $form['import_options'] += CharsetEncoding::getForm($default_value);
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
        elseif (CharsetEncoding::isEncodingProperty($pname)) {
          CharsetEncoding::setEncodingProperty($configurable_properties, $pname, $v);
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
