<?php

namespace Drupal\Tests\common\Unit\Mocks\Storage;

use Contracts\ConditionerInterface;
use Contracts\LimiterInterface;
use Contracts\OffsetterInterface;
use Contracts\SorterInterface;

class JsonObjectMemory extends Memory implements
  SorterInterface,
  ConditionerInterface,
  OffsetterInterface,
  LimiterInterface {
  private int $offset = 0;
  private int $limit = 0;

  private array $sorts = [
    'ascend' => [],
    'descend' => [],
  ];

  private array $conditions = [];

  public function retrieveAll(): array {
    $results = parent::retrieveAll();
    $results = $this->applyFilters($results);
    $this->resetFilters();
    return $results;
  }

  public function store($data, string $id = NULL): string {
    $this->validate($data);
    return parent::store($data, $id);
  }

  public function conditionByIsEqualTo(string $property, string $value): void {
    $this->conditions[$property][] = $value;
  }

  public function limitTo(int $number_of_items): void {
    $this->limit = $number_of_items;
  }

  public function offsetBy(int $offset): void {
    $this->offset = $offset;
  }

  public function sortByAscending(string $property): void {
    $this->sorts['ascend'][] = $property;
  }

  public function sortByDescending(string $property): void {
    $this->sorts['descend'][] = $property;
  }

  private function applyFilters(array $results) {

    if (!empty($this->conditions)) {
      $results2 = [];

      foreach ($this->conditions as $property => $values) {
        foreach ($values as $value) {
          foreach ($results as $key => $result) {
            $obj = json_decode($result);
            if ($obj->{$property} == $value) {
              $results2[$key] = $result;
            }
          }
        }
      }

      $results = $results2;
    }

    foreach ($this->sorts as $type => $properties) {
      foreach ($properties as $property) {
        usort($results, fn($a, $b) => $this->compare($a, $b, $property));

        if ($type == 'descend') {
          $results = array_reverse($results);
        }
      }
    }

    if ($this->limit > 0 || $this->offset > 0) {
      $results = array_slice($results, $this->offset, $this->limit);
    }

    return $results;
  }

  private function resetFilters(): void {
    $this->offset = 0;
    $this->limit = 0;

    $this->sorts = [
      'ascend' => [],
      'descend' => [],
    ];

    $this->conditions = [];
  }

  private function validate(string $data) {
    $decoded = json_decode($data);
    if (is_null($decoded)) {
      throw new \Exception('Only JSON strings can be stored');
    }
    if (!is_object($decoded)) {
      throw new \Exception('Only strings with JSON objects can be stored');
    }
  }

  private function compare($a, $b, $property): int {
    $a = json_decode($a);
    $b = json_decode($b);
    return strnatcmp($a->{$property}, $b->{$property});
  }

}
