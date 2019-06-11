<?php

namespace Drupal\dkan_datastore\Controller;

use Dkan\Datastore\Manager\Manager;
use Drupal\dkan_datastore\Query;
use Drupal\dkan_datastore\Storage\Database;
use Drupal\dkan_datastore\Util;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Api class.
 */
class Api implements ContainerInjectionInterface {

  /**
   * Service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Factory to generate various dkan classes.
   *
   * @var \Drupal\dkan_common\Service\Factory
   */
  protected $dkanFactory;

  /**
   * Constructor.
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
    $this->dkanFactory = $container->get('dkan.factory');
  }

  /**
   * Method called by the router.
   */
  public function runQuery($query_string) {

    $parser = $this->container->get('dkan_datastore.sql_parser');

    if ($parser->validate($query_string) === TRUE) {
      $state_machine = $parser->getValidatingMachine();
      $query_object = $this->getQueryObject($state_machine);
      $database = $this->getDatabase();

      try {
        $result = $database->query($query_object);
      }
      catch(\Exception $e) {
        return $this->dkanFactory
        ->newJsonResponse(
          "Querying a datastore that does not exist.",
          500,
          ["Access-Control-Allow-Origin" => "*"]
        );
      }

      return $this->dkanFactory
        ->newJsonResponse(
          $result,
          200,
          ["Access-Control-Allow-Origin" => "*"]
        );
    }
    else {
      return $this->dkanFactory
        ->newJsonResponse(
          "Invalid query string.",
          500,
          ["Access-Control-Allow-Origin" => "*"]
        );
    }
  }

  /**
   * Private.
   */
  protected function getQueryObject($state_machine) {

    $uuid = $this->getUuidFromSelect($state_machine->gsm('select')->gsm('table_var'));
    try {
      $manager = $this->getDatastoreManager($uuid);
    }
    catch (\Exception $e) {
      return $this->dkanFactory
        ->newJsonResponse(
          "No datastore.",
          500,
          ["Access-Control-Allow-Origin" => "*"]
        );
    }

    $object = new Query();
    $table = $manager->getTableName();
    $object->setThingToRetrieve($table);
    $this->setQueryObjectSelect($object, $state_machine->gsm('select'));
    $this->setQueryObjectWhere($object, $state_machine->gsm('where'));
    $this->setQueryObjectOrderBy($object, $state_machine->gsm('order_by'));
    $this->setQueryObjectLimit($object, $state_machine->gsm('limit'));

    return $object;
  }

  /**
   * Private.
   */
  private function setQueryObjectSelect(Query $object, $state_machine) {
    $strings = $this->getStringsFromStringMachine($state_machine->gsm('select_count_all'));
    if (!empty($strings)) {
      $object->count();
      return;
    }

    $strings = $this->getStringsFromStringMachine($state_machine->gsm('select_var_all'));
    if (!empty($strings)) {
      return;
    }

    $strings = $this->getStringsFromStringMachine($state_machine->gsm('select_var'));
    foreach ($strings as $property) {
      $object->filterByProperty($property);
    }
  }

  /**
   * Private.
   */
  private function setQueryObjectWhere(Query $object, $state_machine) {
    $properties = $this->getStringsFromStringMachine($state_machine->gsm('where_column'));
    $values = $this->getStringsFromStringMachine($state_machine->gsm('quoted_string')->gsm('string'));

    foreach ($properties as $index => $property) {
      $value = $values[$index];
      if ($value) {
        $object->conditionByIsEqualTo($property, $value);
      }
    }
  }

  /**
   * Private.
   */
  private function setQueryObjectOrderBy(Query $object, $state_machine) {
    $properties = $this->getStringsFromStringMachine($state_machine->gsm('order_var'));

    $direction = $this->getStringsFromStringMachine($state_machine->gsm('order_asc'));
    if (!empty($direction)) {
      foreach ($properties as $property) {
        $object->sortByAscending($property);
      }
    }
    else {
      foreach ($properties as $property) {
        $object->sortByDescending($property);
      }
    }
  }

  /**
   * Private.
   */
  private function setQueryObjectLimit(Query $object, $state_machine) {
    $limit = $this->getStringsFromStringMachine($state_machine->gsm('numeric1'));
    if (!empty($limit)) {
      $object->limitTo($limit[0]);
    }

    $offset = $this->getStringsFromStringMachine($state_machine->gsm('numeric2'));
    if (!empty($offset)) {
      $object->offsetBy($offset[0]);
    }
  }

  /**
   * Private.
   */
  private function getUuidFromSelect($machine) {
    $strings = $this->getStringsFromStringMachine($machine);
    if (empty($strings)) {
      throw new \Exception("No UUID given");
    }
    return $strings[0];
  }

  /**
   * Private.
   */
  private function getStringsFromStringMachine($machine) {
    $strings = [];
    $current_string = "";
    $array = $machine->execution;

    foreach ($array as $states_or_input) {
      if (is_array($states_or_input)) {
        $states = $states_or_input;
        if (in_array(0, $states) && !empty($current_string)) {
          $strings[] = $current_string;
          $current_string = "";
        }
      }
      else {
        $input = $states_or_input;
        $current_string .= $input;
      }
    }

    if (!empty($current_string)) {
      $strings[] = $current_string;
    }

    return $strings;
  }

  /**
   * Private.
   */
  protected function getDatabase(): Database {
    return $this->container
      ->get('dkan_datastore.database');
  }

  /**
   * Private.
   */
  protected function getDatastoreManager(string $uuid): Manager {
    return Util::getDatastoreManager($uuid);
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
