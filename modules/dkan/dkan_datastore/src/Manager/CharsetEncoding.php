<?php


namespace Dkan\Datastore\Manager;

use \Exception;

/**
 * Class CharsetEncoding
 *
 * Handles the encoding of data that is not in UTF-8.
 *
 * @package Dkan\Datastore\Manager
 */
class CharsetEncoding {

  /**
   * Strings representing the destination character set.
   */
  const DEST_ENCODING = [
    'PHP' => 'UTF-8',
    'MYSQL' => 'utf8'
  ];

  /** @var \Dkan\Datastore\Manager\ManagerInterface */
  private $manager;

  public function __construct($manager) {
    $this->manager = $manager;
  }

  /**
   * Gets a list of character sets in the selected naming convention
   * @param string $type
   *  The naming convention ('PHP' or 'MYSQL'
   *
   * @return array|false|string[]
   *   Character set names.
   */
  public static function getEncodings($type) {
    $list = [];
    switch ($type) {
      case 'PHP':
        $list = mb_list_encodings();
        // Make the key/values the same in the array.
        $list = array_combine($list, $list);
        break;
      case 'MYSQL':
        $list = db_query('SELECT CHARACTER_SET_NAME, DESCRIPTION FROM INFORMATION_SCHEMA.CHARACTER_SETS')->fetchAllKeyed();
        break;
    }

    natsort($list);
    return $list;
  }

  /**
   * Select list(s) of encodings, appropriate to available importers.
   *
   * @todo Make Chosen update on #states change.
   * Everything works fine if only one import manager is enabled, but if
   * more than one is enabled, Chosen never loads values for the hidden encoding select.
   *
   * @param array $default_value
   *   The default encoding to use.
   *
   * @return array
   *  Form elements for select lists.
   */
  public static function getForm(array $default_value) {
    $managers_info = dkan_datastore_managers_info();
    $form = [];

    /* @var $manager_info \Dkan\Datastore\Manager\Info */
    foreach ($managers_info as $manager_info) {
      $select_name = "datastore_manager_config_encoding_" . strtolower($manager_info->getImportType());

      $form[$select_name] = [
        '#type' => 'select',
        // @codingStandardsIgnoreStart
        '#title' => t('Character encoding of file'),
        // @codingStandardsIgnoreEnd
        '#options' => self::getEncodings($manager_info->getImportType()),
        '#default_value' => $default_value[$manager_info->getImportType()],
      ];

      if (count($managers_info) > 1) {
        $form[$select_name]['#states'] = [
          'visible' => [
            ':input[name="datastore_managers_selection"]' => [
              'value' => $manager_info->getClass()
            ]
          ]
        ];
      }
    }
    return $form;
  }

  /**
   * Ensures that a string has the correct encoding
   *
   * @param string $data
   *  A sting in the source charset.
   *
   * @return string
   *  The data string in the destination charset.
   *
   * @throws \Exception
   */
  public function fixEncoding($data) {
    $properties = $this->manager->getConfigurableProperties();

    if (mb_check_encoding($data, $properties['encoding']['PHP'])) {
      if ($properties['encoding']['PHP'] !== self::DEST_ENCODING['PHP']) {
        // Convert encoding. The conversion is to UTF-8 by default to prevent
        // SQL errors.
        $data = mb_convert_encoding($data, self::DEST_ENCODING['PHP'], $properties['encoding']['PHP']);
      }
    }
    else {
      throw new Exception(t('Source file is not in ":encoding" encoding.', array(':encoding' => $properties['encoding']['PHP'])));
    }

    return $data;
  }

  /**
   * Is this form element part of the 'encoding' property?
   *
   * @param $property_name
   *   The property name to test?
   *
   * @return bool
   *   True if it is an encoding property
   */
  static public function isEncodingProperty($property_name) {
    $prefix = 'encoding_';
    return strcmp(substr($property_name, 0, strlen($prefix)), $prefix) === 0;
  }

  /**
   * Adds or updates an encoding property value.
   *
   * @param $configurable_properties
   *   The properties to update.
   *
   * @param $property_name
   *   The name of the form element.
   *
   * @param $value
   *   The value of the form element.
   */
  static public function setEncodingProperty(&$configurable_properties, $property_name, $value) {
    if (preg_match('/encoding_(.*)/', $property_name, $matches)) {
      $type = strtoupper($matches[1]);
      $configurable_properties['encoding'][$type] = $value;
    }
    else {
      $msg = t(':name is not a valid encoding property name', [':name' => $property_name]);
      drupal_set_message($msg, 'error');
    }
  }
}