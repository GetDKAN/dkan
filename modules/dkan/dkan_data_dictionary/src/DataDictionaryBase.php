<?php

namespace Dkan\DataDictionary;

/**
 * Class Info.
 *
 * Validator metadata.
 */
abstract class DataDictionaryBase implements \JsonSerializable {

  protected $managerClass;
  protected $machineName;
  protected $label;
  protected $allowedFileExtensions;

  /**
   *
   */
  public function __construct($machine_name, $label, array $allowed_file_types = array(), $managerClass = NULL) {
    $this->label = $label;
    $this->machineName = $machine_name;
    $this->allowedFileExtensions = $allowed_file_types;

    if (!empty($managerClass)) {
      // Make sure the implementation exists.
      $this->classIsManager($managerClass);
      $this->managerClass = $managerClass;
    }
  }

  /**
   * Getter.
   */
  public function getDataDictionaryManager(Resource $resource) {
    return new $this->managerClass($this, $resource);
  }

  /**
   *
   */
  public function classIsManager($managerClass) {
    $exists = class_exists($managerClass);
    if (!$exists) {
      throw new \Exception("The class {$managerClass} does not exist.");
    }

    $interfaces = class_implements($managerClass);
    $interface = "Dkan\DataDictionary\DataDictionaryManagerInterface";
    if (!in_array($interface, $interfaces)) {
      throw new \Exception("The class {$managerClass} does not implement the interface {$interface}.");
    }
  }

  /**
   * Report if the schema support validation operatinos.
   */
  public function hasManager() {
    if (!empty($this->managerClass)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Getter.
   */
  public function getManagerClass() {
    return $this->managerClass;
  }

  /**
   * Getter.
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * Getter.
   */
  public function getMachineName() {
    return $this->machineName;
  }

  /**
   * Get Validator allowed file types.
   */
  public function getAllowedFileExtensions() {
    return $this->allowedFileExtensions;
  }

  /**
   * Validate the provided schema.
   *
   * @param string $schema
   *   schema content or file path.
   *
   * @return array Errors list.
   */
  abstract public static function validateSchema($schema);

  /**
   * Build a renderable array for a field value. Called during
   * hook_field_formatter_view().
   */
  abstract public static function dictionaryFormatterView($langcode, $item, $display);

  /**
   * {@inheritdoc}
   */
  public function jsonSerialize() {
    return [
      'managerClass' => $this->managerClass,
      'machine_name' => $this->machineName,
      'label' => $this->label,
    ];
  }

}
