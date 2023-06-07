<?php

namespace Drupal\Tests\metastore\Unit;

use Drupal\common\Storage\DatabaseTableInterface;
use Drupal\common\Storage\Query;

/**
 *
 */
class DatabaseTableMock implements DatabaseTableInterface {
  private $id = 0;
  private $store = [];

  public function primaryKey() {
    return 'id';
  }

  /**
   *
   */
  public function retrieveAll(): array {
  }

  /**
   *
   */
  public function storeMultiple(array $data) {
    // TODO: Implement storeMultiple() method.
  }

  /**
   *
   */
  public function count(): int {
    // TODO: Implement count() method.
  }

  /**
   *
   */
  public function destruct() {
    // TODO: Implement destruct() method.
  }
  
  /**
   *
   */
  public function query(Query $query) {
    $storeCopy = $this->store;

    foreach ($query->conditions as $condition) {
      $property = $condition->property;
      $value = $condition->value;
      $storeCopy = array_filter($storeCopy, function ($item) use ($property, $value) {
        return $item->{$property} == $value;
      });
    }

    $sortProperty = reset($query->sorts);

    if ($sortProperty) {
      usort($storeCopy, function ($a, $b) use ($sortProperty) {
        return strcmp($a->{$sortProperty}, $b->{$sortProperty});
      });
    }

    return $storeCopy;
  }

  /**
   *
   */
  public function remove(string $id) {
    // TODO: Implement remove() method.
  }

  /**
   *
   */
  public function retrieve(string $id) {
    // TODO: Implement retrieve() method.
  }

  /**
   * Setter.
   */
  public function setSchema(array $schema): void {
    // TODO: Implement setSchema() method.
  }

  /**
   * Getter.
   */
  public function getSchema(): array {
    // TODO: Implement getSchema() method.
    return [];
  }

  /**
   *
   */
  public function store($data, string $id = NULL): string {
    $this->id++;
    $this->store[$this->id] = json_decode($data);
    return $this->id;
  }

}
