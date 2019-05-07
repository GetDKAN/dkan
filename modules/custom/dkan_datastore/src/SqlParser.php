<?php

namespace Drupal\dkan_datastore;

use Maquina\StateMachine\MachineOfMachines;
use Maquina\Builder as mb;
use Maquina\Feeder;
use Maquina\StateMachine\IStateMachine;

/**
 *
 */
class SqlParser {

  /**
   * Static call for backward compatibility.
   *
   * @codeCoverageIgnore
   * @param string $name
   * @param array $arguments
   *
   * @return mixed
   */
  public static function __callStatic($name, $arguments) {
    $instance = new static();

    return call_user_func_array([$instance, $name], $arguments);
  }

  /**
   * Validate and SQL string.
   *
   * Can be called statically.
   *
   * @static
   * @param string $sql
   *
   * @return bool
   */
  public function validate(string $sql): bool {
    $machine = $this->getMachine();
    try {
      $this->feedFeeder($sql, $machine);
      return TRUE;
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }

  /**
   * Creates the state machine.
   *
   * @static
   *
   * @return \Maquina\StateMachine\IStateMachine
   */
  public function getMachine() {
    $alphanumeric = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $acceptable_for_vars = $alphanumeric . "*-_";

    $machine = $this->newMachineOfMachine('select');
    $machine->addEndState("end");

    $machine->addMachine('select', mb::s('[SELECT'));
    $machine->addMachine('select_var', mb::bh($acceptable_for_vars, mb::ONE_OR_MORE));
    $machine->addMachine('select_from', mb::s('FROM'));
    $machine->addMachine('table_var', mb::bh($acceptable_for_vars, mb::ONE_OR_MORE));
    $machine->addMachine('closing_bracket', mb::bh(' ', mb::ZERO_OR_MORE));
    $machine->addMachine("where", self::getWhereMachine());
    $machine->addMachine("order_by", self::getOrderByMachine());
    $machine->addMachine("limit", self::getLimitMachine());

    $machine->addTransition('select', [" "], "select_var");
    $machine->addTransition('select_var', [" "], "select_from");
    $machine->addTransition('select_var', [","], "select_var");
    $machine->addTransition('select_from', [" "], "table_var");
    $machine->addTransition('table_var', [",", ", ", " ,", " , "], "table_var");
    $machine->addTransition('table_var', ["]"], "closing_bracket");
    $machine->addTransition('closing_bracket', ["["], "where");
    $machine->addTransition('closing_bracket', [";"], "end");
    $machine->addTransition('where', [";"], "end");

    $machine->addTransition('where', ["["], "order_by");
    $machine->addTransition('order_by', ["["], "limit");
    $machine->addTransition('order_by', [";"], "end");

    $machine->addTransition('order_by_end', ["["], "limit");
    $machine->addTransition('limit', ["]"], "limit_end");
    $machine->addTransition('limit_end', [";"], "end");

    return $machine;
  }

  /**
   *
   * @return \Maquina\StateMachine\IStateMachine
   */
  protected function getWhereMachine() {
    $machine = $this->newMachineOfMachine('where_start');
    $machine->addEndState("where_end");

    $machine->addMachine('where_start', mb::s('WHERE'));
    $machine->addMachine('where_column', mb::bh('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_', mb::ONE_OR_MORE));
    $machine->addMachine("equal", mb::bh("LIKE= ", mb::ONE_OR_MORE));
    $machine->addMachine('where_var', mb::bh('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_"%', mb::ONE_OR_MORE));
    // $machine->addMachine("quoted_string", self::getQuotedStringMachine());
    $machine->addMachine("and", mb::s("AND"));

    $machine->addTransition('where_start', [" "], "where_column");
    $machine->addTransition('where_column', [" "], "equal");
    $machine->addTransition('equal', ['"'], "where_var");
    $machine->addTransition('where_var', [']'], "where_end");
    $machine->addTransition('where_var', [' '], "and");
    $machine->addTransition('and', [' '], "where_column");

    return $machine;
  }

  /**
   *
   * @return \Maquina\StateMachine\IStateMachine
   */
  protected function getQuotedStringMachine() {
    $machine = $this->newMachineOfMachine('1');
    $machine->addEndState("end");

    $machine->addMachine('string', mb::bh('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ %$.', mb::ONE_OR_MORE));

    $machine->addTransition('1', ["'"], "string");
    $machine->addTransition('string', ["'"], "end");

    return $machine;
  }

  /**
   *
   * @return \Maquina\StateMachine\IStateMachine
   */
  protected function getOrderByMachine() {
    $machine = $this->newMachineOfMachine('order');
    $machine->addEndState("order_end");

    $machine->addMachine('order', mb::s('ORDER BY'));
    $machine->addMachine('order_var', mb::bh('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ$_', mb::ONE_OR_MORE));
    $machine->addMachine('order_sort', mb::bh('DEASC', mb::ONE_OR_MORE));

    $machine->addTransition('order', [" "], "order_var");
    $machine->addTransition('order_var', [","], "order_var");
    $machine->addTransition('order_var', [" "], "order_sort");
    $machine->addTransition('order_var', ["]"], "order_end");
    $machine->addTransition('order_sort', ["]"], "order_end");

    return $machine;
  }

  /**
   *
   * @return \Maquina\StateMachine\IStateMachine
   */
  protected function getLimitMachine() {
    $machine = $this->newMachineOfMachine('limit');
    $machine->addEndState("numeric1");
    $machine->addEndState("numeric2");
    $machine->addMachine('limit', mb::s('LIMIT'));
    $machine->addMachine('offset', mb::s('OFFSET'));
    $machine->addMachine('numeric1', mb::bh('0123456789'));
    $machine->addMachine('numeric2', mb::bh('0123456789'));

    $machine->addTransition('limit', [" "], "numeric1");
    $machine->addTransition('numeric1', [" "], "offset");
    $machine->addTransition('offset', [" "], "numeric2");

    return $machine;

  }

  /**
   * Instantiates a new MachineOfMachine.
   *
   * Separate method for easier testing.
   *
   * @param string $initialState
   *
   * @return \Maquina\StateMachine\IStateMachine
   */
  protected function newMachineOfMachine(string $initialState) {
    return new MachineOfMachines($initialState);
  }

  /**
   * Feeds the feeder.
   *
   * @codeCoverageIgnore
   * @param string $sql
   * @param \Maquina\StateMachine\IStateMachine $machine
   *
   * @throws \Exception
   */
  protected function feedFeeder(string $sql, IStateMachine $machine) {
    return Feeder::feed($sql, $machine);
  }

}
