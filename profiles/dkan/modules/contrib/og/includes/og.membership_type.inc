<?php

/**
 * @file
 * A class used for group membership types.
 */
class OgMembershipType extends Entity {

  public $name;
  public $description = '';

  public function __construct($values = array()) {
    parent::__construct($values, 'og_membership_type');
  }
}
