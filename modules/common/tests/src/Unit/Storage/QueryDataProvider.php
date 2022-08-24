<?php

namespace Drupal\Tests\common\Unit\Storage;

use Drupal\common\Storage\Query;

/**
 * Data provider for testing the SelectFactory class.
 *
 * Provides a number of query objects with expected SQL equivalents or exception
 * messages. Methods are public static so that other tests can easily pull them
 * in individually.
 *
 * @see Drupal\Tests\common\Storage\SelectFactoryTest
 * @see Drupal\Tests\datastore\Service\QueryTest
 */
class QueryDataProvider {

  const QUERY_OBJECT = 1;
  const SQL = 2;
  const EXCEPTION = 3;
  const VALUES = 4;

  /**
   *
   */
  public function getAllData($return): array {
    $tests = [
      'noPropertiesQuery',
      'propertiesQuery',
      'badPropertyQuery',
      'unsafePropertyQuery',
      'expressionQuery',
      'nestedExpressionQuery',
      'badExpressionOperandQuery',
      'conditionQuery',
      'likeConditionQuery',
      'containsConditionQuery',
      'startsWithConditionQuery',
      'matchConditionQuery',
      'arrayConditionQuery',
      'nestedConditionGroupQuery',
      'sortQuery',
      'badSortQuery',
      'offsetQuery',
      'limitOffsetQuery',
      'joinsQuery',
      'joinWithPropertiesFromBothQuery',
      'countQuery',
      'groupByQuery',
    ];
    $data = [];
    foreach ($tests as $test) {
      $data[$test] = [
        self::$test(self::QUERY_OBJECT),
        self::$test(self::SQL),
        self::$test(self::EXCEPTION),
        self::$test(self::VALUES),
      ];
    }
    return $data;
  }

  /**
   *
   */
  public static function noPropertiesQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        return $query;

      case self::SQL:
        return "SELECT t.* FROM {table} t";

      case self::EXCEPTION:
        return '';

      case self::VALUES:
        return [];
    }
  }

  /**
   *
   */
  public static function propertiesQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->properties = ["field1", "field2"];
        return $query;

      case self::SQL:
        return "SELECT t.field1 AS field1, t.field2 AS field2 FROM {table} t";

      case self::EXCEPTION:
        return '';
      
      case self::VALUES:
        return [];
  
    }
  }

  /**
   *
   */
  public static function badPropertyQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->properties = [(object) ["collection" => "t"]];
        return $query;

      case self::SQL:
        return '';

      case self::EXCEPTION:
        return "Bad query property";
      
      case self::VALUES:
        return [];
    }
  }

  /**
   *
   */
  public static function unsafePropertyQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->properties = ["l.field3"];
        return $query;

      case self::SQL:
        return '';

      case self::EXCEPTION:
        return "Unsafe property name: l.field3";

      case self::VALUES:
        return [];
    }
  }

  /**
   *
   */
  public static function expressionQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->properties = [
          (object) [
            "alias" => "add_one",
            "expression" => (object) [
              "operator" => "+",
              "operands" => ["field1", 1],
            ],
          ],
          (object) [
            "alias" => "add_two",
            "expression" => (object) [
              "operator" => "+",
              "operands" => [
                (object) ["collection" => "t", "property" => "field2"],
                2,
              ],
            ],
          ],
          (object) [
            "alias" => "sum",
            "expression" => (object) [
              "operator" => "sum",
              "operands" => [
                (object) ["collection" => "t", "property" => "field2"],
              ],
            ],
          ],
        ];
        $query->sorts = [
          (object) [
            "property" => "add_one",
            "order" => "asc",
          ],
        ];
        return $query;

      case self::SQL:
        return "SELECT (t.field1 + 1) AS add_one, (t.field2 + 2) AS add_two, (SUM(t.field2)) AS sum FROM {table} t ORDER BY add_one ASC";

      case self::EXCEPTION:
        return '';

        case self::VALUES:
          return [];
    }
  }

  /**
   *
   */
  public static function nestedExpressionQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->properties = [
          (object) [
            "alias" => "nested",
            "expression" => (object) [
              "operator" => "*",
              "operands" => [
                (object) [
                  "expression" => (object) [
                    "operator" => "+",
                    "operands" => ["field1", "field2"],
                  ],
                ],
                (object) [
                  "expression" => (object) [
                    "operator" => "+",
                    "operands" => ["field3", "field4"],
                  ],
                ],
              ],
            ],
          ],
        ];
        return $query;

      case self::SQL:
        return "SELECT ((t.field1 + t.field2) * (t.field3 + t.field4)) AS nested FROM {table} t";

      case self::EXCEPTION:
        return '';

      case self::VALUES:
        return [];
    }
  }

  /**
   *
   */
  public static function badExpressionOperandQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->properties = [
          (object) [
            "alias" => "bad_expression",
            "expression" => (object) [
              "operator" => "VARIANCE",
              "operands" => ["field1", "field2"],
            ],
          ],
        ];
        return $query;

      case self::SQL:
        return '';

      case self::EXCEPTION:
        return "Only basic arithmetic expressions and basic SQL functions currently supported.";

      case self::VALUES:
        return [];
    }
  }

  /**
   *
   */
  public static function conditionQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->conditions = [
          (object) [
            "collection" => "t",
            "property" => "field1",
            "value" => "value",
          ],
        ];
        return $query;

      case self::SQL:
        return "WHERE t.field1 = :db_condition_placeholder_0";

      case self::EXCEPTION:
        return '';

      case self::VALUES:
        return ['value'];
    }
  }

  /**
   *
   */
  public static function likeConditionQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->conditions = [
          (object) [
            "collection" => "t",
            "property" => "field1",
            "value" => "%value%",
            "operator" => "like",
          ],
        ];
        return $query;

      case self::SQL:
        return "WHERE t.field1 LIKE :db_condition_placeholder_0";

      case self::EXCEPTION:
        return '';

      case self::VALUES:
        return ['%value%'];
    }

  }


  /**
   *
   */
  public static function containsConditionQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->conditions = [
          (object) [
            "collection" => "t",
            "property" => "field1",
            "value" => "value",
            "operator" => "contains",
          ],
        ];
        return $query;

      case self::SQL:
        return "WHERE t.field1 LIKE :db_condition_placeholder_0";

      case self::EXCEPTION:
        return '';

      case self::VALUES:
        return ['%value%'];
    }
  }

  public static function startsWithConditionQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->conditions = [
          (object) [
            "collection" => "t",
            "property" => "field1",
            "value" => "value",
            "operator" => "starts with",
          ],
        ];
        return $query;

      case self::SQL:
        return "WHERE t.field1 LIKE :db_condition_placeholder_0";

      case self::EXCEPTION:
        return '';

      case self::VALUES:
        return ['value%'];
    }
  }

  public static function matchConditionQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->conditions = [
          (object) [
            "collection" => "t",
            "property" => "field1",
            "value" => "value",
            "operator" => "match",
          ],
          (object) [
            "collection" => "t",
            "property" => "field2",
            "value" => "value2",
            "operator" => "match",
          ],
        ];
        return $query;

      case self::SQL:
        return "WHERE ((MATCH(t.field1) AGAINST (:words0 IN BOOLEAN MODE))) AND ((MATCH(t.field2) AGAINST (:words1 IN BOOLEAN MODE)))";

      case self::EXCEPTION:
        return '';

      case self::VALUES:
        return ['value', 'value2'];
    }
  }


  /**
   *
   */
  public static function arrayConditionQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->conditions = [
          (object) [
            "collection" => "t",
            "property" => "field1",
            "value" => [1, 5],
            "operator" => "in",
          ],
        ];
        return $query;

      case self::SQL:
        return "WHERE t.field1 IN (:db_condition_placeholder_0, :db_condition_placeholder_1)";

      case self::EXCEPTION:
        return '';

      case self::VALUES:
        return [1, 5];
    }

  }

  /**
   *
   */
  public static function nestedConditionGroupQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->conditions = [
          (object) [
            "groupOperator" => "or",
            "conditions" => [
              (object) [
                "collection" => "t",
                "property" => "field1",
                "value" => "value1",
                "operator" => "<",
              ],
              (object) [
                "groupOperator" => "and",
                "conditions" => [
                  (object) [
                    "collection" => "t",
                    "property" => "field2",
                    "value" => "value2",
                  ],
                  (object) [
                    "collection" => "t",
                    "property" => "field3",
                    "value" => "value3",
                  ],
                ],
              ],
            ],
          ],
        ];
        return $query;

      case self::SQL:
        return "WHERE (t.field1 < :db_condition_placeholder_0) OR ((t.field2 = :db_condition_placeholder_1) AND (t.field3 = :db_condition_placeholder_2))";

      case self::EXCEPTION:
        return '';

      case self::VALUES:
        return ['value1', 'value2', 'value3'];
    }
  
  }

  /**
   *
   */
  public static function sortQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->sorts = [
          (object) [
            "collection" => "t",
            "property" => "field1",
            "order" => "desc",
          ],
          (object) [
            "collection" => "t",
            "property" => "field2",
            "order" => "asc",
          ],
          (object) [
            "collection" => "t",
            "property" => "field3",
            "order" => "desc",
          ],
        ];
        return $query;

      case self::SQL:
        return "ORDER BY t.field1 DESC, t.field2 ASC, t.field3 DESC";

      case self::EXCEPTION:
        return '';

      case self::VALUES:
        return [];
    }
  }

  /**
   *
   */
  public static function badSortQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->sorts = ["foo" => ["field1"]];
        return $query;

      case self::SQL:
        return '';

      case self::EXCEPTION:
        return "Invalid sort.";

      case self::VALUES:
        return [];
    }
  }

  /**
   *
   */
  public static function offsetQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->offset = 5;
        return $query;

      case self::SQL:
        return "OFFSET 5";

      case self::EXCEPTION:
        return '';

      case self::VALUES:
        return [];
    }
  }

  /**
   *
   */
  public static function limitOffsetQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->offset = 5;
        $query->limit = 15;
        return $query;

      case self::SQL:
        return "LIMIT 15 OFFSET 5";

      case self::EXCEPTION:
        return '';

      case self::VALUES:
        return [];
    }
  }

  /**
   *
   */
  public static function joinsQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->joins = [
          (object) [
            "collection" => "table2",
            "alias" => "l",
            "condition" => (object) [
              "collection" => "t",
              "property" => "field1",
              "value" => (object) [
                "collection" => "l",
                "property" => "field1",
              ],
            ],
          ],
        ];
        return $query;

      case self::SQL:
        return "INNER JOIN {table2} l ON t.field1 = l.field1";

      case self::EXCEPTION:
        return '';

      case self::VALUES:
        return [];
    }
  }

  /**
   *
   */
  public static function joinWithPropertiesFromBothQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->properties = [
          (object) [
            "collection" => "t",
            "property" => "field2",
          ],
          (object) [
            "collection" => "l",
            "property" => "field3",
          ],
        ];
        $query->joins = [
          (object) [
            "collection" => "table2",
            "alias" => "l",
            "condition" => (object) [
              "collection" => "t",
              "property" => "field1",
              "value" => (object) ["collection" => "l", "property" => "field1"],
            ],
          ],
        ];
        return $query;

      case self::SQL:
        return "SELECT t.field2 AS field2, l.field3 AS field3 FROM {table} t INNER JOIN {table2} l";

      case self::EXCEPTION:
        return '';

      case self::VALUES:
        return [];
    }
  }

  /**
   *
   */
  public static function countQuery($return) {
    switch ($return) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->count = TRUE;
        return $query;

      case self::SQL:
        return "SELECT COUNT(*) AS expression";

      case self::EXCEPTION:
        return '';

      case self::VALUES:
        return [];
    }
  }

  /**
   * Provides an example groupby query object, SQL string, and exception string.
   *
   * @param int $returnType
   *   Expected groupby query return value type.
   *
   * @return Query|string
   */
  public static function groupByQuery(int $returnType) {
    switch ($returnType) {
      case self::QUERY_OBJECT:
        $query = new Query();
        $query->properties = [
          (object) [
            'collection' => 't',
            'property' => 'prop',
          ],
          (object) [
            'alias' => 'sum',
            'expression' => (object) [
              'operator' => 'sum',
              'operands' => [
                (object) [
                  'collection' => 't',
                  'property' => 'summable',
                ],
              ],
            ],
          ],
        ];
        $query->conditions = [
          (object) [
            'collection' => 't',
            'property' => 'filterable',
            'value' => 'value',
            'operator' => '=',
          ],
        ];
        $query->groupby = ['prop'];
        return $query;

      case self::SQL:
        return 'SELECT t.prop AS prop, (SUM(t.summable)) AS sum FROM {table} t WHERE t.filterable = :db_condition_placeholder_0 GROUP BY prop';

      case self::EXCEPTION:
        return '';

      case self::VALUES:
        return ['value'];
    }

    throw new \UnexpectedValueException('Unknown return type provided.');
  }

}
