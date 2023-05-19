<?php

namespace Drupal\datastore\SqlEndpoint;

use Drupal\common\DataResource;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\Storage\Query;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\SqlEndpoint\Helper\GetStringsFromStateMachineExecution;
use Drupal\datastore\Storage\DatabaseTable;
use Maquina\StateMachine\Machine;
use Maquina\StateMachine\MachineOfMachines;
use SqlParser\SqlParser;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * SQL endpoint service.
 */
class DatastoreSqlEndpointService implements ContainerInjectionInterface {
  /**
   * ConfigFactory object.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  private $configFactory;

  /**
   * The datastore service object.
   *
   * @var \Drupal\datastore\DatastoreService
   */
  private $datastoreService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.datastore.service'),
      $container->get('config.factory')
    );
  }

  /**
   * Constructor, sets the datastoreService and configFactory properties.
   *
   * @param \Drupal\datastore\DatastoreService $datastoreService
   *   The datastore service object.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   An instance of Drupal's configFactory.
   */
  public function __construct(DatastoreService $datastoreService, ConfigFactory $configFactory) {

    $this->datastoreService = $datastoreService;
    $this->configFactory = $configFactory;
  }

  /**
   * Run query.
   *
   * @param string $queryString
   *   The query string passed to the endpoint.
   * @param bool $showDbColumns
   *   If true, return DB column machine names instead of human-readable
   *   descriptions, and include a "record_number" column.
   *
   * @return array
   *   Array of row/record objects.
   */
  public function runQuery(string $queryString, $showDbColumns = FALSE): array {
    $queryObject = $this->getQueryObject($queryString);

    $identifier = NULL;
    $version = NULL;
    [$identifier, $version] = $this->getResourceIdentifierAndVersion($queryString);

    $databaseTable = $this->getDatabaseTable($identifier, $version);

    $result = $databaseTable->query($queryObject);

    $schema = $databaseTable->getSchema();
    $fields = $schema['fields'];

    return array_map(function ($row) use ($fields, $showDbColumns) {
      if (!$showDbColumns) {
        unset($row->record_number);
      }

      $arrayRow = (array) $row;

      $newRow = [];

      foreach ($arrayRow as $fieldName => $value) {
        if (!$showDbColumns && isset($fields[$fieldName]['description']) && !empty($fields[$fieldName]['description'])) {
          $newRow[$fields[$fieldName]['description']] = $value;
        }
        else {
          $newRow[$fieldName] = $value;
        }
      }

      return (object) $newRow;
    }, $result);
  }

  /**
   * Get resource UUID.
   *
   * @param string $sqlString
   *   A string with an sql statement.
   *
   * @return array
   *   An array with the identifier and version.
   *
   * @throws \Exception
   */
  public function getResourceIdentifierAndVersion(string $sqlString): array {
    $stateMachine = $this->validate($sqlString);
    $someIdentifier = $this->getTableNameFromSelect($stateMachine->gsm('select'));
    return DataResource::getIdentifierAndVersion($someIdentifier);
  }

  /**
   * Private.
   */
  private function getDatabaseTable($identifier, $version = NULL): DatabaseTable {
    return $this->datastoreService->getStorage($identifier, $version);
  }

  /**
   * Private.
   */
  protected function getTableNameFromSelect(MachineOfMachines $selectMachine): string {
    $machine = $selectMachine->gsm('table_var');
    $strings = $this->getStringsFromStringMachine($machine);
    if (empty($strings)) {
      throw new \Exception("No table name");
    }
    return $strings[0];
  }

  /**
   * Get a query object from a sql string.
   *
   * @param string $sqlString
   *   A string with a sql statement.
   *
   * @return \Drupal\datastore\Storage\Query
   *   A query object.
   */
  private function getQueryObject(string $sqlString): Query {
    return $this->getQueryObjectFromStateMachine($this->validate($sqlString));
  }

  /**
   * Private.
   */
  private function validate(string $sqlString): MachineOfMachines {
    $parser = new SqlParser();
    if ($parser->validate($sqlString) === FALSE) {
      throw new \Exception("Invalid query string.");
    }

    return $parser->getValidatingMachine();
  }

  /**
   * Take an instantiated state machine build a query object.
   *
   * @param Maquina\StateMachine\MachineOfMachines $state_machine
   *   The state machine returned from the validate() function.
   *
   * @return Drupal\common\Storage\Query
   *   A Drupal query object
   */
  private function getQueryObjectFromStateMachine(MachineOfMachines $state_machine): Query {
    $object = new Query();
    $this->setQueryObjectSelect($object, $state_machine->gsm('select'));
    $this->setQueryObjectWhere($object, $state_machine->gsm('where'));
    $this->setQueryObjectOrderBy($object, $state_machine->gsm('order_by'));
    $this->setQueryObjectLimit($object, $state_machine->gsm('limit'));

    return $object;
  }

  /**
   * Set select statements on query object.
   *
   * @param \Drupal\common\Storage\Query $object
   *   A drupal query object.
   * @param \Maquina\StateMachine\MachineOfMachines $state_machine
   *   The state machine from validate().
   */
  private function setQueryObjectSelect(Query $object, MachineOfMachines $state_machine) {
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
   * Set where conditions on query object.
   *
   * @param \Drupal\common\Storage\Query $object
   *   A drupal query object.
   * @param \Maquina\StateMachine\MachineOfMachines $state_machine
   *   The state machine from validate().
   */
  private function setQueryObjectWhere(Query $object, MachineOfMachines $state_machine) {
    $properties = $this->getStringsFromStringMachine($state_machine->gsm('where_column'));
    $quoted_string = $state_machine->gsm('quoted_string');
    if (!($quoted_string instanceof MachineOfMachines)) {
      throw new \Exception("State machine error.");
    }
    $values = $this->getStringsFromStringMachine($quoted_string->gsm('string'));

    foreach ($properties as $index => $property) {
      $value = $values[$index];
      if ($value) {
        $object->conditionByIsEqualTo($property, $value);
      }
    }
  }

  /**
   * Set sorting on query object.
   *
   * @param \Drupal\common\Storage\Query $object
   *   A drupal query object.
   * @param \Maquina\StateMachine\MachineOfMachines $state_machine
   *   The state machine from validate().
   */
  private function setQueryObjectOrderBy(Query $object, MachineOfMachines $state_machine) {
    $properties = $this->getStringsFromStringMachine($state_machine->gsm('order_var'));

    $direction = $this->getStringsFromStringMachine($state_machine->gsm('order_asc'));
    $sortMethod = (!empty($direction)) ? "sortByAscending" : "sortByDescending";

    foreach ($properties as $property) {
      $object->$sortMethod($property);
    }
  }

  /**
   * Private.
   */
  private function setQueryObjectLimit(Query $object, MachineOfMachines $state_machine) {
    $limit = $this->getStringsFromStringMachine($state_machine->gsm('numeric1'));

    if (empty($limit)) {
      return;
    }

    $limit = $limit[0];

    $rows_limit = $this->configFactory->get('datastore.settings')->get('rows_limit');
    if (!$object->count && isset($limit) && $limit > $rows_limit) {
      $limit = $rows_limit;
    }

    $object->limitTo($limit);

    $offset = $this->getStringsFromStringMachine($state_machine->gsm('numeric2'));

    if (!empty($offset)) {
      $object->offsetBy($offset[0]);
    }
  }

  /**
   * Private.
   */
  private function getStringsFromStringMachine(Machine $machine): array {
    return (new GetStringsFromStateMachineExecution($machine->execution))->get();
  }

  /**
   * Get the a resource's UUID if it was given through the SQL string.
   */
  public function getResourceUuid(string $sqlString) {
    $stateMachine = $this->validate($sqlString);
    $identifier = $this->getTableNameFromSelect($stateMachine->gsm('select'));

    // Are we dealing with a distribution id?
    if (substr_count($identifier, '__') == 0) {
      return $identifier;
    }
    return NULL;
  }

}
