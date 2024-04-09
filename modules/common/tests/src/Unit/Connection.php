<?php

namespace Drupal\Tests\common\Unit;

use Drupal\Core\Database\Connection as CoreConnection;

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
  protected $statementClass = NULL;

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
  public function createDatabase($database) {
    return;
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
