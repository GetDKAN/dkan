<?php

namespace Drupal\Tests\common\Unit;

use Drupal\Core\Database\Connection as CoreConnection;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Database\Query\Select;

/**
 * A fake Connection class for testing purposes.
 */
class Connection extends CoreConnection {

  /**
   * Public property so we can test driver loading mechanism.
   *
   * @var string
   * @see driver().
   */
  public $driver = 'fake';

  /**
   * {@inheritdoc}
   */
  protected $identifierQuotes = ['', ''];

  /**
   * {@inheritdoc}
   */
  protected $statementClass;

  public function select($table, $alias = NULL, array $options = []) {
    return new Select($this, $table, $alias, $options);
  }

  public function condition($conjunction = 'AND') {
    return new Condition($conjunction, $this);
  }

  public function upsert($table, array $options = []) { }

  public function schema() { }

  /**
   * {@inheritdoc}
   */
  public function queryRange($query, $from, $count, array $args = [], array $options = []) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function queryTemporary($query, array $args = [], array $options = []) {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function driver() {
    return $this->driver;
  }

  /**
   * {@inheritdoc}
   */
  public function databaseType() {
    return 'fake';
  }

  /**
   * {@inheritdoc}
   */
  public function createDatabase($database)
  {
  }

  /**
   * {@inheritdoc}
   */
  public function mapConditionOperator($operator) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function nextId($existing_id = 0) {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public static function open(array &$connection_options = []) {
    return new \stdClass();
  }

}
