<?php

namespace Drupal\Tests\datastore\Unit\DataDictionary;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Update;

class UpdateQueryMock extends Update {

    public function __construct(Connection $connection, $table, array $options = []) {
    }

    public function expression($field, $expression, $arguments = NULL) {
        \Drupal::state()->set('update_query', [
            'field' => $field,
            'expression' => $expression,
            'arguments' => $arguments,
        ]);

        return $this;
    }

    public function condition($field, $value = NULL, $operator = '=') {
        return $this;
    }

    public function execute() {
        return 0;
    }

}
