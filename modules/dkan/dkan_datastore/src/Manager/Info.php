<?php
/**
 * Manager metadata.
 */

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

  /**
   * Info constructor.
   */
  public function __construct($class, $machine_name, $label) {
    $this->class = $class;
    $this->label = $label;
    $this->machineName = $machine_name;
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
   * {@inheritdoc}
   */
  public function jsonSerialize() {
    return [
      'class' => $this->class,
      'machine_name' => $this->machineName,
      'label' => $this->label
    ];
  }

}
