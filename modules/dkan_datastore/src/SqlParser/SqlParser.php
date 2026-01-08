<?php

namespace Drupal\datastore\SqlParser;

use Maquina\StateMachine\MachineOfMachines;
use Maquina\Builder as mb;
use Maquina\Feeder;
use Maquina\StateMachine\IStateMachine;

/**
 * SQL parser.
 */
class SqlParser {

  /**
   * State machine.
   */
  private ?IStateMachine $stateMachine = NULL;

  public const ALPHANUM = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

  public const COLUMN_OR_TABLE_CHARS = self::ALPHANUM . '_-';

  /**
   * Static call for backward compatibility.
   *
   * @codeCoverageIgnore
   */
  public static function __callStatic($name, $arguments) {
    $instance = new static();

    return call_user_func_array([$instance, $name], $arguments);
  }

  /**
   * Validate an SQL string.
   *
   * @param string $sql
   *   An SQL statement.
   *
   * @return bool
   *   Whether the statement is valid.
   *
   * @static
   *   Can be called statically.
   */
  public function validate(string $sql): bool {
    $machine = $this->getMachine();
    $this->stateMachine = $machine;
    try {
      $this->feedFeeder($sql, $machine);
      return TRUE;
    }
    catch (\Exception) {
      return FALSE;
    }
  }

  /**
   * Get the state machine after validating a string.
   */
  public function getValidatingMachine(): ?IStateMachine {
    return $this->stateMachine;
  }

  /**
   * Creates the state machine.
   *
   * @return \Maquina\StateMachine\IStateMachine
   *   The machine that will validate the string.
   *
   * @static
   *   Can be called statically.
   */
  public function getMachine() {

    $machine = $this->newMachineOfMachine('begin');
    $machine->addEndState('end');

    $machine->addMachine('select', self::getSelectMachine());
    $machine->addMachine('where', self::getWhereMachine());
    $machine->addMachine('order_by', self::getOrderByMachine());
    $machine->addMachine('limit', self::getLimitMachine());

    $machine->addTransition('begin', ['['], 'select');
    $machine->addTransition('select', [']'], 'anticipation');
    $machine->addTransition('where', [']'], 'anticipation');
    $machine->addTransition('order_by', [']'], 'anticipation');
    $machine->addTransition('limit', [']'], 'anticipation');
    $machine->addTransition('anticipation', [';'], 'end');
    $machine->addTransition('anticipation', ['['], 'where');
    $machine->addTransition('anticipation', ['['], 'order_by');
    $machine->addTransition('anticipation', ['['], 'limit');

    return $machine;
  }

  /**
   * Private.
   */
  protected function getSelectMachine() {
    $machine = $this->newMachineOfMachine('select_start');
    $machine->addEndState('table_var');

    $machine->addMachine('select_start', mb::s('SELECT'));
    $machine->addMachine('select_var_all', mb::s('*'));
    $machine->addMachine('select_count_all', mb::s('COUNT(*)'));
    $machine->addMachine('select_var', mb::bh(
          self::COLUMN_OR_TABLE_CHARS,
          mb::ONE_OR_MORE
      ));
    $machine->addMachine('select_from', mb::s('FROM'));
    $machine->addMachine('table_var', mb::bh(
          self::COLUMN_OR_TABLE_CHARS,
          mb::ONE_OR_MORE
      ));

    $machine->addTransition('select_start', [' '], 'select_var');
    $machine->addTransition('select_start', [' '], 'select_var_all');
    $machine->addTransition('select_start', [' '], 'select_count_all');
    $machine->addTransition('select_var', [' '], 'select_from');
    $machine->addTransition('select_var', [','], 'select_var');
    $machine->addTransition('select_var_all', [' '], 'select_from');
    $machine->addTransition('select_count_all', [' '], 'select_from');
    $machine->addTransition('select_from', [' '], 'table_var');

    return $machine;
  }

  /**
   * Private.
   */
  protected function getWhereMachine() {
    $machine = $this->newMachineOfMachine('where_start');
    $machine->addEndState('quoted_string');

    $machine->addMachine('where_start', mb::s('WHERE'));
    $machine->addMachine('where_column', mb::bh(
          self::COLUMN_OR_TABLE_CHARS,
          mb::ONE_OR_MORE
      ));
    $machine->addMachine('equal', mb::bh('=', mb::ONE_OR_MORE));
    $machine->addMachine('quoted_string', self::getQuotedStringMachine());
    $machine->addMachine('and', mb::s('AND'));

    $machine->addTransition('where_start', [' '], 'where_column');
    $machine->addTransition('where_column', [' '], 'equal');
    $machine->addTransition('equal', [' '], 'quoted_string');
    $machine->addTransition('quoted_string', [' '], 'and');
    $machine->addTransition('and', [' '], 'where_column');

    return $machine;
  }

  /**
   * Private.
   */
  protected function getQuotedStringMachine() {
    $machine = $this->newMachineOfMachine('start');
    $machine->addEndState('end');

    $machine->addMachine('string', mb::bh(
          self::COLUMN_OR_TABLE_CHARS . " '\\,.;:?!+-*/^=(){}[]<>`@~#$%&|",
          mb::ONE_OR_MORE
      ));

    $machine->addTransition('start', ['"'], 'string');
    $machine->addTransition('string', ['"'], 'end');

    return $machine;
  }

  /**
   * Private.
   */
  protected function getOrderByMachine() {
    $machine = $this->newMachineOfMachine('order');
    $machine->addEndState('order_asc');
    $machine->addEndState('order_desc');

    $machine->addMachine('order', mb::s('ORDER BY'));
    $machine->addMachine('order_var', mb::bh(
          self::COLUMN_OR_TABLE_CHARS,
          mb::ONE_OR_MORE
      ));
    $machine->addMachine('order_asc', mb::s('ASC'));
    $machine->addMachine('order_desc', mb::s('DESC'));

    $machine->addTransition('order', [' '], 'order_var');
    $machine->addTransition('order_var', [','], 'order_var');
    $machine->addTransition('order_var', [' '], 'order_asc');
    $machine->addTransition('order_var', [' '], 'order_desc');

    return $machine;
  }

  /**
   * Private.
   */
  protected function getLimitMachine() {
    $machine = $this->newMachineOfMachine('limit');
    $machine->addEndState('numeric1');
    $machine->addEndState('numeric2');
    $machine->addMachine('limit', mb::s('LIMIT'));
    $machine->addMachine('offset', mb::s('OFFSET'));
    $machine->addMachine('numeric1', mb::bh('0123456789'));
    $machine->addMachine('numeric2', mb::bh('0123456789'));

    $machine->addTransition('limit', [' '], 'numeric1');
    $machine->addTransition('numeric1', [' '], 'offset');
    $machine->addTransition('offset', [' '], 'numeric2');

    return $machine;
  }

  /**
   * Instantiates a new MachineOfMachine.
   *
   * @param string $initialState
   *   The initial state of the machine.
   *
   * @return \Maquina\StateMachine\IStateMachine
   *   A state machine.
   */
  protected function newMachineOfMachine(string $initialState): MachineOfMachines {
    return new MachineOfMachines([$initialState]);
  }

  /**
   * Feeds the feeder.
   *
   * @param string $sql
   *   An sql statement.
   * @param \Maquina\StateMachine\IStateMachine $machine
   *   The machine to feed.
   *
   * @throws \Exception
   *
   * @codeCoverageIgnore
   */
  protected function feedFeeder(string $sql, IStateMachine $machine) {
    return Feeder::feed($sql, $machine);
  }

}
