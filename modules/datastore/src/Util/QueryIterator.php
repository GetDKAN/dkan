<?php

namespace Drupal\datastore\Util;

use Drupal\datastore\Service\DatastoreQuery;
use RootedData\RootedJsonData;

/**
 * @package Drupal\datastore
 *
 * @todo Dependency injection.
 */
class QueryIterator {

  public function __construct($datastoreQuery, $rowsLimit, $datastoreService) {
    $this->datastoreService = $datastoreService;
    $iteratorQuery = new DatastoreQuery("$datastoreQuery", $rowsLimit);

    // Disable extra information in response.
    $iteratorQuery->{"$.count"} = FALSE;
    $iteratorQuery->{"$.schema"} = FALSE;
    $iteratorQuery->{"$.keys"} = TRUE;
    $iteratorQuery->{"$.offset"} = 0;

    // Set up our properties, conditions and sorts.
    $this->rowIdColumnIndex = $this->addRowIdProperty($iteratorQuery);
    $this->order = $this->addRowIdSort($iteratorQuery);
    $this->rowIdConditionIndex = count($iteratorQuery->{"$.conditions"} ?? []);

    $this->query = $iteratorQuery;
  }

  /**
   * Return a page of results and advance the query pagination.
   *
   * @return array
   *   An page of of results, ready to be appended to full result set.
   */
  public function pageResult() {
    // Run the query based on current parameters.
    $result = $this->datastoreService->runQuery($this->query);
    $rows = $this->prepareRows($result);

    // Set up pagination for next iteration.
    $this->iterateConditions($result);

    // Return the result rows for this iteration.
    return $rows;
  }

  /**
   * Prepare one page of query results to be returned.
   *
   * @param \RootedData\RootedJsonData $result
   *   Unaltered query result object.
   *
   * @return array
   *   An array of un-keyed result rows, with the iterator artifacts removed.
   */
  private function prepareRows(RootedJsonData $result) {
    $rows = $result->{"$.results"};
    if (!$this->query->{"$.rowIds"}) {
      array_walk($rows, function (&$row) {
          unset($row['record_number']);
          $row = array_values($row);
      });
    }
    return $rows;
  }

  /**
   * Advance the pagination condition(s) once to prepare to request next page.
   *
   * Returns nothing, just modifies the iterator query conditions by reference.
   *
   * @param \RootedData\RootedJsonData $result
   *   The full result object from the previous iteration.
   */
  private function iterateConditions(RootedJsonData $result) {
    // Start creating the condition for the next page.
    $resultRows = $result->{"$.results"};
    $lastRow = end($resultRows);
    if (empty($lastRow)) {
      return;
    }
    $lastRowId = $lastRow['record_number'];
    $rowIdCondition = [
      'resource' => $this->getPrimaryResource($this->query),
      'property' => 'record_number',
      'operator' => $this->order == 'asc' ? '>' : '<',
      'value' => $lastRowId,
    ];

    $sorts = $this->query->{"$.sorts"};
    array_pop($sorts);

    if (empty($sorts)) {
      $this->query->{"$.conditions[$this->rowIdConditionIndex]"} = $rowIdCondition;
      return;
    }

    // If we're still here, we've got sorts and need an OR group.
    $orGroup = [
      'groupOperator' => 'or',
      'conditions' => [$rowIdCondition],
    ];
    $andGroup = [
      'groupOperator' => 'and',
      'conditions' => [],
    ];

    // The rowId sort is the last one; remove it and see if anything's left.
    foreach ($sorts as $sort) {
      $andCondition = [
        'resource' => $sort['resource'] ?? $this->getPrimaryResource($this->query),
        'property' => $sort['property'],
        'operator' => $this->order == 'asc' ? '>=' : '<=',
        'value' => $lastRow[$sort['property']],
      ];
      $andGroup['conditions'][] = $andCondition;

      $orCondition = $andCondition;
      $orCondition['operator'] = substr($andCondition['operator'], 0, 1);
      $orGroup['conditions'][] = $orCondition;
    }
    $andGroup['conditions'][] = $orGroup;
    $this->query->{"$.conditions[$this->rowIdConditionIndex]"} = $andGroup;
  }

  /**
   * If properties are being specified, add one for pagination.
   *
   * @return int
   *   The array index of the column to use for pagination.
   */
  private function addRowIdProperty($iteratorQuery) {
    $properties = $iteratorQuery->{'$.properties'} ?? NULL;
    if (!empty($properties)) {
      $properties[] = [
        'property' => 'record_number',
        'resource' => $this->getPrimaryResource($iteratorQuery),
      ];
      $iteratorQuery->{'$.properties'} = $properties;
      end($properties);
      return key($properties);
    }
    return NULL;
  }


  /**
   * We need to explicitly sort by the row ID (record_number).
   *
   * @return string
   *   The sort order used. Will be either "asc" or "desc".
   */
  private function addRowIdSort($iteratorQuery) {
    $sorts = $iteratorQuery->{'$.sorts'} ?? [];
    $orders = [];
    foreach ($sorts as $sort) {
      $orders[] = $sort['order'] ?? 'asc';
    }
    $orders = array_unique($orders ?: ['asc']);
    if (count($orders) > 1) {
      throw new \Exception("Because of how DKAN optimizes queries for large CSV downloads, you may not add sorts with different orders to the same query.");
    }
    $order = current($orders);
    $sorts[] = [
      'resource' => 't',
      'property' => 'record_number',
      'order' => $order,
    ];

    $iteratorQuery->{'$.sorts'} = $sorts;
    return $order;
  }

  private function getPrimaryResource($iteratorQuery) {
    return $iteratorQuery->{"$.resources[0][alias]"} ?? "t";
  }

}
