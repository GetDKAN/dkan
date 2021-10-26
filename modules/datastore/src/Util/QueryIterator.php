<?php

namespace Drupal\datastore\Util;

use Drupal\datastore\Service;
use Drupal\datastore\Service\DatastoreQuery;
use RootedData\RootedJsonData;

/**
 * Help the query download paginate through a query efficiently.
 *
 * @package Drupal\datastore
 *
 * @todo Proper dependency injection.
 */
class QueryIterator {

  /**
   * The iterator query, modified throughout the process.
   *
   * @var \Drupal\datastore\Service\DatastoreQuery
   */
  private $query;

  /**
   * The datastore service.
   *
   * @var \Drupal\datastore\Service
   */
  private $datastoreService;

  /**
   * Index of the condition array to use for pagination filters.
   *
   * @var int
   */
  private $iteratorConditionIndex;

  /**
   * Constructor.
   *
   * @param Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   A DKAN datastore query object. Will not be modified.
   * @param int $rowsLimit
   *   The system limit for rows returned in a single DB query.
   * @param \Drupal\Datastore\Service $datastoreService
   *   The main datastore service.
   *
   * @todo Implement as a service with proper dependency injection.
   */
  public function __construct(DatastoreQuery $datastoreQuery, int $rowsLimit, Service $datastoreService) {
    $this->datastoreService = $datastoreService;
    $iteratorQuery = new DatastoreQuery("$datastoreQuery", $rowsLimit);

    // Disable extra information in response.
    $iteratorQuery->{"$.count"} = FALSE;
    $iteratorQuery->{"$.schema"} = FALSE;
    $iteratorQuery->{"$.keys"} = TRUE;
    $iteratorQuery->{"$.offset"} = 0;

    // Set up our properties, conditions and sorts.
    $this->addRowIdProperty($iteratorQuery);
    $this->addRowIdSort($iteratorQuery);
    $this->iteratorConditionIndex = count($iteratorQuery->{"$.conditions"} ?? []);

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
   * Uses the "keyset pagination" method.
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

    // We need groups for each sort.
    $sorts = $this->query->{"$.sorts"};
    $pageConditions = $this->intializeSortConditions($sorts, $lastRow);
    // Finish building the pagination conditions tree.
    $this->recurseSortConditions($sorts, $lastRow, $pageConditions);
    $baseOrGroup = [
      'groupOperator' => 'or',
      'conditions' => array_values($pageConditions),
    ];
    // Add the whole thing as a single "OR" group in the conditions.
    $this->query->{"$.conditions[$this->iteratorConditionIndex]"} = $baseOrGroup;
  }

  /**
   * Create initial page conditions array.
   *
   * @param array $sorts
   *   The sorts from the main query.
   * @param array $lastRow
   *   The last row from the previous iteration.
   *
   * @return array
   *   An array of comparison conditions, to be used in the main "OR" group
   *   for pagination.
   */
  private function intializeSortConditions(array $sorts, array $lastRow) {
    $pageConditions = [];

    // Set up initial pagination comparison conditions, per sort.
    foreach ($sorts as $sort) {
      $pageConditions[$sort['property']] = [
        'groupOperator' => 'and',
        'conditions' => [
          [
            'resource' => $sort['resource'] ?? $this->getPrimaryResource($this->query),
            'property' => $sort['property'],
            'operator' => $sort['order'] == 'asc' ? '>' : '<',
            'value' => $lastRow[$sort['property']],
          ],
        ],
      ];
    }
    return $pageConditions;
  }

  /**
   * We need to add "=" conditions for each sort to address possible duplicates.
   *
   * @param array $sorts
   *   An array of sorts from the query. Will be altered in recursion.
   * @param array $lastRow
   *   The last result row from the previous query.
   * @param array $pageConditions
   *   The full pagination conditions group.
   */
  private function recurseSortConditions(array &$sorts, array $lastRow, array &$pageConditions) {
    // For each recursion, we have a "main" sort we are adding conditions for.
    $mainSort = array_shift($sorts);
    // We copy the existing comparison condition, and swap in a "=" operator.
    $mainSortCondition = $pageConditions[$mainSort['property']]['conditions'][0];
    $mainSortCondition['operator'] = '=';
    // Add an "=" condition to each or the main comparison conditions.
    foreach ($sorts as $sort) {
      $pageConditions[$sort['property']]['conditions'][] = $mainSortCondition;
    }
    // If we have more than one sort left, we need to do it all again.
    if (count($sorts) > 1) {
      $this->recurseSortConditions($sorts, $lastRow, $pageConditions);
    }
  }

  /**
   * If properties are being specified, add one for safer pagination.
   *
   * @param \Drupal\datastore\Service\DatastoreQuery $iteratorQuery
   *   Datastore query.
   *
   * @return int
   *   The array index of the column to use for pagination.
   */
  private function addRowIdProperty(DatastoreQuery $iteratorQuery) {
    $properties = $iteratorQuery->{'$.properties'} ?? NULL;
    if (!empty($properties)) {
      $properties[] = [
        'property' => 'record_number',
        'resource' => $this->getPrimaryResource($iteratorQuery),
      ];
      $iteratorQuery->{'$.properties'} = $properties;
      end($properties);
      // We want to know which array index contains the added record_id
      // property. Return it (the query itself is modified by reference).
      return key($properties);
    }
    return NULL;
  }

  /**
   * We need to explicitly sort by the row ID (record_number).
   *
   * @param \Drupal\datastore\Service\DatastoreQuery $iteratorQuery
   *   Datastore query.
   */
  private function addRowIdSort(DatastoreQuery $iteratorQuery) {
    $sorts = $iteratorQuery->{'$.sorts'} ?? [];
    $sorts[] = [
      'resource' => $this->getPrimaryResource($iteratorQuery),
      'property' => 'record_number',
      'order' => 'asc',
    ];

    $iteratorQuery->{'$.sorts'} = $sorts;
  }

  /**
   * Get the alias string for the main resource in the query.
   *
   * @param \Drupal\datastore\Service\DatastoreQuery $iteratorQuery
   *   Datastore query.
   *
   * @return string
   *   The alias for the primary resource (table) in the query.
   */
  private function getPrimaryResource(DatastoreQuery $iteratorQuery) {
    return $iteratorQuery->{"$.resources[0][alias]"} ?? "t";
  }

}
