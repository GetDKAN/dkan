<?php
/**
 * Manager metadata.
 */

namespace Dkan\Datastore\Manager;


class Info {
  private $class;
  private $label;

  public function __construct($class, $label) {
    $this->class = $class;
    $this->label = $label;
  }

  public function getClass() {
    return $this->class;
  }

  public function getLabel() {
    return $this->label;
  }

}