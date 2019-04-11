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

  public static function getMachine() {
    $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ*';

    $machine = new MachineOfMachines('select');
    $machine->addEndState("end");

    $machine->addMachine('select', mb::s('[SELECT'));
    $machine->addMachine('select_var', mb::bh($letters,mb::ONE_OR_MORE));
    $machine->addMachine('select_from', mb::s('FROM'));
    $machine->addMachine('table_var', mb::bh($letters,mb::ONE_OR_MORE));
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

  private static function getWhereMachine() {
    $machine = new MachineOfMachines('where_start');
    $machine->addEndState("where_end");

    $machine->addMachine('where_start', mb::s('WHERE'));
    $machine->addMachine('where_column', mb::bh('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_',  mb::ONE_OR_MORE));
    $machine->addMachine("equal", mb::bh("LIKE= ",  mb::ONE_OR_MORE));
    $machine->addMachine('where_var', mb::bh('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_"',  mb::ONE_OR_MORE));
    //$machine->addMachine("quoted_string", self::getQuotedStringMachine());
    $machine->addMachine("and", mb::s("AND"));

    $machine->addTransition('where_start', [" "], "where_column");
    $machine->addTransition('where_column', [" "], "equal");
    $machine->addTransition('equal', ['"'], "where_var");
    $machine->addTransition('where_var', [']'], "where_end");
    $machine->addTransition('where_var', [' '], "and");
    $machine->addTransition('and', [' '], "where_column");

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
    $machine->addEndState("order_end");

    $machine->addMachine('order', mb::s('ORDER BY'));
    $machine->addMachine('order_var', mb::bh('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ$_ ',  mb::ONE_OR_MORE));

    $machine->addTransition('order', [" "], "order_var");
    $machine->addTransition('order_var', [","], "order_var");
    $machine->addTransition('order_var', ["]"], "order_end");

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
