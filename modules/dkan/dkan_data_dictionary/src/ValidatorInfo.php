<?php

namespace Dkan\DataDictionary;

/**
 * Class Info.
 *
 * Validator metadata.
 */
class ValidatorInfo implements \JsonSerializable {
  private $class;
  private $machineName;
  private $label;
  private $allowedFileTypes;

  /**
   * Info constructor.
   */
  public function __construct($class, $machine_name, $label, array $allowed_file_types = array()) {
    // Make sure the implementation exists.
    $this->checkClass($class);

    $this->class = $class;
    $this->label = $label;
    $this->machineName = $machine_name;
    $this->allowedFileTypes = $allowed_file_types;
  }

  /**
   * Getter.
   */
  public function getValidationManager() {
    return new $this->class($this);
  }

  /**
   *
   */
  public function checkClass($class) {
    $exists = class_exists($class);
    if (!$exists) {
      throw new \Exception("The class {$class} does not exist.");
    }

    $interfaces = class_implements($class);
    $interface = "Dkan\DataDictionary\ValidationManagerInterface";
    if (!in_array($interface, $interfaces)) {
      throw new \Exception("The class {$class} does not implement the interface {$interface}.");
    }
  }

  /**
   * Getter.
   */
  public function getClass() {
    return $this->class;
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
  public function getAllowedFileTypse() {
    return $this->allowedFileTypes;
  }

  /**
   * {@inheritdoc}
   */
  public function jsonSerialize() {
    return [
      'class' => $this->class,
      'machine_name' => $this->machineName,
      'label' => $this->label,
    ];
  }

}
