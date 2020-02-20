<?php

namespace Dkan\Datastore\Manager;

/**
 * Class Info.
 *
 * Manager metadata.
 */
class Info implements \JsonSerializable {
  private $class;
  private $machineName;
  private $label;
  private $importType;

  /**
   * Info constructor.
   */
  public function __construct($class, $machine_name, $label, $import_type) {
    $this->class = $class;
    $this->label = $label;
    $this->machineName = $machine_name;
    $this->importType = $import_type;
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
   * Getter.
   */
  public function getImportType() {
    return $this->importType;
  }

  /**
   * {@inheritdoc}
   */
  public function jsonSerialize() {
    return [
      'class' => $this->class,
      'machine_name' => $this->machineName,
      'label' => $this->label,
      'import_type' => $this->importType,
    ];
  }

}
