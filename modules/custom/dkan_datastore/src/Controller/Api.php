<?php

namespace Drupal\dkan_datastore\Controller;

use Drupal\dkan_datastore\Query;
use Drupal\dkan_datastore\Storage\Database;
use Drupal\dkan_datastore\Util;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class Api implements ContainerInjectionInterface {

  /**
   * Service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   *
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  /**
   *
   */
  public function runQuery($query_string) {

    $parser = $this->container->get('dkan_datastore.sql_parser');

    if ($parser->validate($query_string) === TRUE) {

      $query_object = $this->getQueryObject($query_string);
      $database = $this->getDatabase();
      $result = $database->query($query_object);
      return new JsonResponse($result);
    }
    else {
      return new JsonResponse("Invalid query string.");
    }
  }

  /**
   *
   */
  protected function getQueryObject($query_string) {

    $object = new Query();

    $query_pieces = $this->explode($query_string);

    $select = array_shift($query_pieces);
    $uuid = $this->getUuidFromSelect($select);
    $properties = $this->getPropertiesFromSelect($select);

    $where = array_shift($query_pieces);
    if ($where) {
      $properties_and_values = $this->getPropertiesAndValuesFromWhere($where);
      foreach ($properties_and_values as $property => $value) {
        $object->conditionByIsEqualTo($property, $value);
      }
    }

    $sort = array_shift($query_pieces);
    if ($sort) {
      $sort_info = $this->getSortInfo($sort);
      foreach ($sort_info['ASC'] as $property) {
        $object->sortByAscending($property);
      }
      foreach ($sort_info['DESC'] as $property) {
        $object->sortByDescending($property);
      }
    }

    $range = array_shift($query_pieces);
    if ($range) {
      $range_info = $this->getRangeInfo($range);
      if (!empty($range_info['limit'])) {
        $object->limitTo($range_info['limit']);
      }
      if (!empty($range_info['offset'])) {
        $object->offsetBy($range_info['offset']);
      }
    }

    try {
      $manager = Util::getDatastoreManager($uuid);

      $table = $manager->getTableName();
      $object->setThingToRetrieve($table);
    }
    catch (\Exception $e) {
      return new JsonResponse("No datastore.");
    }

    foreach ($properties as $p) {
      $object->filterByProperty($p);
    }

    return $object;
  }

  /**
   *
   */
  protected function getDatabase(): Database {
    return $this->container
      ->get('dkan_datastore.database');
  }

  /**
   *
   */
  protected function explode(string $queryStr) {
    $pieces = explode("]", $queryStr);
    foreach ($pieces as $key => $piece) {
      $pieces[$key] = str_replace("[", "", $piece);
    }
    array_pop($pieces);
    return $pieces;
  }

  /**
   *
   */
  protected function getUuidFromSelect(string $select) {
    $pieces = explode("FROM", $select);
    return trim(end($pieces));
  }

  /**
   *
   */
  protected function getPropertiesFromSelect(string $select) {
    $properties = [];
    $pieces = explode("FROM", $select);
    $first = array_shift($pieces);
    $properties_string = str_replace("SELECT", "", $first);
    if (substr_count($properties_string, "*") > 0) {
      return $properties;
    }
    else {
      $dirty_properties = explode(",", $properties_string);
      foreach ($dirty_properties as $p) {
        $properties[] = trim($p);
      }
      return $properties;
    }
  }

  /**
   *
   */
  protected function getPropertiesAndValuesFromWhere(string $where) {
    $result = [];
    $where = str_replace("WHERE", "", $where);
    $conditions = explode("AND", $where);
    foreach ($conditions as $cond) {
      $pieces = explode("=", $cond);
      if (count($pieces) == 1) {
        $pieces = explode("LIKE", $cond);
      }
      $result[$pieces[0]] = trim(str_replace('"', "", $pieces[1]));
    }
    return $result;
  }

  /**
   *
   */
  protected function getSortInfo(string $sort) {
    $sort_info = ['ASC' => [], 'DESC' => []];

    $pieces = explode(" ", $sort);

    $sort_order = "ASC";
    if (count($pieces) == 4) {
      $sort_order = end($pieces);
    }

    foreach (explode(",", $pieces[2]) as $property) {
      $sort_info[$sort_order][] = trim($property);
    }

    return $sort_info;
  }

  /**
   *
   */
  protected function getRangeInfo(string $range) {
    $info = ['limit' => NULL, 'offset' => NULL];
    $pieces = explode(" ", $range);
    if (count($pieces) == 4) {
      $info['limit'] = $pieces[1];
      $info['offset'] = $pieces[3];
    }
    elseif (count($pieces) == 2) {
      $info['limit'] = $pieces[1];
    }
    return $info;
  }

  /**
   * @{inheritdocs}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container) {
    return new static($container);
  }

}
