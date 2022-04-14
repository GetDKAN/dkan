<?php

namespace Drupal\Tests\common\Unit\Mocks;

use Drupal\Core\Database\Connection;
use Drupal\common\Storage\DatabaseConnectionFactory;

class DatabaseConnectionFactoryMock extends DatabaseConnectionFactory {

  protected Connection $connection;

  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  public function getConnection(): Connection {
    $this->doSetConnectionTimeout($this->connection);

    return $this->connection;
  }

}
