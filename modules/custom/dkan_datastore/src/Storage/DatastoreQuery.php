<?php

namespace Drupal\dkan_api\Storage;

/*class DrupalNodeDataset implements Storage, BulkRetriever {
protected function getType() {
return 'dataset';
}

public function retrieve(string $id): ?string {

foreach ($this->getNodesByUuid($id) as $result) {
$node = Node::load($result->nid);
return $node->field_json_metadata->value;
}

throw new \Exception("No data with the identifier {$id} was found.");
}

public function retrieveAll(): array {
$connection = \Drupal::database();
$sql = "SELECT nid FROM node WHERE type = :type";
$query = $connection->query($sql, [':type' => $this->getType()]);
$results = $query->fetchAll();

$all = [];
foreach ($results as $result) {
$node = Node::load($result->nid);
$all[] = $node->field_json_metadata->value;
}
return $all;
}

public function remove(string $id) {

foreach ($this->getNodesByUuid($id) as $result) {
$node = Node::load($result->nid);
return $node->delete();
}
}

public function store(string $data, string $id = NULL): string {

$data = json_decode($data);

if (!$id && isset($data->identifier)) {
$id = $data->identifier;
}

if ($id) {
$node = \Drupal::service('entity.repository')->loadEntityByUuid('node', $id);
}

if ($node) {    // update existing node
$node->field_json_metadata = json_encode($data);
$node->save();
return $node->id();
} else {    // create new node
$title = isset($data->title) ? $data->title : $data->name;
$nodeWrapper = NODE::create([
'title' => $title,
'type' => 'dataset',
'uuid' => $id,
'field_json_metadata' => json_encode($data)
]);
$nodeWrapper->save();
return $nodeWrapper->id();
}

return NULL;
}

private function getNodesByUuid($uuid) {
$connection = \Drupal::database();
$sql = "SELECT nid FROM node WHERE uuid = :uuid AND type = :type";
$query = $connection->query($sql, [':uuid' => $uuid, ':type' => $this->getType()]);
return $query->fetchAll();
}*/

use Contracts\Sorter;
use Contracts\Conditioner;
use Contracts\Offsetter;
use Contracts\Limiter;

/**
 *
 */
class DatastoreQuery extends Memory implements Sorter, Conditioner, Offsetter, Limiter {
  private $offset = 0;
  private $limit = 0;

  private $sorts = [
    'ascend' => [],
    'descend' => [],
  ];

  private $conditions = [];

  /**
   *
   */
  public function retrieveAll(): array {
    $results = parent::retrieveAll();
    $results = $this->applyFilters($results);
    $this->resetFilters();
    return $results;
  }

  /**
   *
   */
  public function store(string $data, string $id = NULL): string {
    $this->validate($data);
    return parent::store($data, $id);
  }

  /**
   *
   */
  public function conditionByIsEqualTo(string $property, string $value) {
    $this->conditions[$property][] = $value;
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
    $this->sorts['ascend'][] = $property;
  }

  /**
   *
   */
  public function sortByDescending(string $property) {
    $this->sorts['descend'][] = $property;
  }

  /**
   *
   */
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
        usort($results, function ($a, $b) use ($property) {
          return $this->compare($a, $b, $property);
        });

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

  /**
   *
   */
  private function resetFilters() {
    $this->offset = 0;
    $this->limit = 0;

    $this->sorts = [
      'ascend' => [],
      'descend' => [],
    ];

    $this->conditions = [];
  }

  /**
   *
   */
  private function validate(string $data) {
    $decoded = json_decode($data);
    if (is_null($decoded)) {
      throw new \Exception("Only JSON strings can be stored");
    }
    if (!is_object($decoded)) {
      throw new \Exception("Only strings with JSON objects can be stored");
    }
  }

}
