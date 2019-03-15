<?php

namespace Drupal\dkan_datastore;

use Maquina\StateMachine\MachineOfMachines;
use Maquina\Builder as mb;
use Maquina\Feeder;

class SqlParser
{
  public static function validate(string $sql): bool {
    $machine = self::getMachine();
    try {
      Feeder::feed($sql, $machine);
      return TRUE;
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }

  private static function getMachine() {

    $machine = new MachineOfMachines('select_start');
    $machine->addEndState("end");

    $machine->addMachine('select', mb::s('SELECT * FROM'));
    $machine->addMachine('select_var', mb::bh('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ$_-',  mb::ONE_OR_MORE));
    $machine->addMachine("where", self::getWhereMachine());
    $machine->addMachine("order_by", self::getOrderByMachine());
    $machine->addMachine("limit", self::getLimitMachine());


    $machine->addTransition('select_start', ["["], "select");
    $machine->addTransition('select', [" "], "select_var");
    $machine->addTransition('select_var', ["]"], "select_end");
    $machine->addTransition('select_end', [";"], "end");

    $machine->addTransition('select_end', ["["], "where");
    $machine->addTransition('where', ["]"], "where_end");
    $machine->addTransition('where_end', [";"], "end");

    $machine->addTransition('where_end', ["["], "order_by");
    $machine->addTransition('order_by', ["]"], "order_by_end");
    $machine->addTransition('order_by_end', [";"], "end");

    $machine->addTransition('order_by_end', ["["], "limit");
    $machine->addTransition('limit', ["]"], "limit_end");
    $machine->addTransition('limit_end', [";"], "end");

    return $machine;
  }

  private static function getWhereMachine() {
    $machine = new MachineOfMachines('where');
    $machine->addEndState("quoted_string");

    $machine->addMachine('where', mb::s('WHERE'));
    $machine->addMachine('where_var', mb::bh('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ$_',  mb::ONE_OR_MORE));
    $machine->addMachine("equal", mb::s("LIKE"));
    $machine->addMachine("quoted_string", self::getQuotedStringMachine());
    $machine->addMachine("and", mb::s("AND"));


    $machine->addTransition('where', [" "], "where_var");
    $machine->addTransition('where_var', [" "], "equal");
    $machine->addTransition('equal', [" "], "quoted_string");
    $machine->addTransition('quoted_string', [" "], "and");
    $machine->addTransition('and', [" "], "where_var");

    return $machine;
  }

  private static function getQuotedStringMachine() {
    $machine = new MachineOfMachines('1');
    $machine->addEndState("end");

    $machine->addMachine('string', mb::bh('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ %$.',  mb::ONE_OR_MORE));

    $machine->addTransition('1', ["'"], "string");
    $machine->addTransition('string', ["'"], "end");

    return $machine;
  }

  private static function getOrderByMachine() {
    $machine = new MachineOfMachines('order');
    $machine->addEndState("order_var");

    $machine->addMachine('order', mb::s('ORDER BY'));
    $machine->addMachine('order_var', mb::bh('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ$_',  mb::ONE_OR_MORE));

    $machine->addTransition('order', [" "], "order_var");
    $machine->addTransition('order_var', [","], "space");
    $machine->addTransition('space', [" "], "order_var");

    return $machine;
  }

  private static function getLimitMachine() {
    $machine = new MachineOfMachines('limit');
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

}