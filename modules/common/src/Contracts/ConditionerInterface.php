<?php

namespace Drupal\common\Contracts;

interface ConditionerInterface {

  public function conditionByIsEqualTo(string $property, string $value);

}
