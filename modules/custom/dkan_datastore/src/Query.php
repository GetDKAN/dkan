<?php

namespace Drupal\dkan_datastore;

use Contracts\Sorter;
use Contracts\Conditioner;
use Contracts\Offsetter;
use Contracts\Limiter;

/**
 *
 */
class Query implements Sorter, Conditioner, Offsetter, Limiter {
  public $thing;
  public $properties = [];
  public $conditions = [];
  public $sort = ['ASC' => [], 'DESC' => []];
  public $limit = NULL;
  public $offset = NULL;

  /**
   *
   */
  public function setThingToRetrieve($id) {
    $this->thing = $id;
  }

  /**
   *
   */
  public function filterByProperty($property) {
    $this->properties[] = $property;
  }

  /**
   *
   */
  public function conditionByIsEqualTo(string $property, string $value) {
    $this->conditions[$property] = $value;
  }

  /**
   *
   */
  public function limitTo(int $number_of_items) {
    $this->limit = $number_of_items;
  }

  /**
   *
   */
  public function offsetBy(int $offset) {
    $this->offset = $offset;
  }

  /**
   *
   */
  public function sortByAscending(string $property) {
    $this->sort['ASC'][] = $property;
  }

  /**
   *
   */
  public function sortByDescending(string $property) {
    $this->sort['DESC'][] = $property;
  }

}
