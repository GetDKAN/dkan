<?php

namespace Drupal\datastore\Controller;

use Drupal\datastore\Service\DatastoreQuery;
use Symfony\Component\HttpFoundation\StreamedResponse;
use RootedData\RootedJsonData;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Controller providing functionality used to stream datastore queries.
 *
 * @package Drupal\datastore
 */
class QueryDownloadController extends AbstractQueryController {

  /**
   * Stream a CSV response.
   *
   * @param \Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   A datastore query object.
   * @param \RootedData\RootedJsonData $result
   *   The result of the datastore query.
   * @param array $dependencies
   *   A dependency array for use by \Drupal\metastore\MetastoreApiResponse.
   * @param \Symfony\Component\HttpFoundation\ParameterBag|null $params
   *   The parameter object from the request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The json response.
   */
  public function formatResponse(
    DatastoreQuery $datastoreQuery,
    RootedJsonData $result,
    array $dependencies = [],
    ?ParameterBag $params = NULL
  ) {
    switch ($datastoreQuery->{"$.format"}) {
      case 'csv':
        return $this->streamCsvResponse($datastoreQuery, $result);

      case 'json':
      default:
        return $this->getResponseFromException(
          new \UnexpectedValueException("Streaming not currently available for JSON responses"),
          400
        );
    }
  }

  protected function buildDatastoreQuery($request, $identifier = NULL) {
    $data = json_decode(static::getPayloadJson($request));
    if (isset($data->limit)) {
      throw new \Exception("Limits are, temporarily, not allowed in downloads.");
    }
    return parent::buildDatastoreQuery($request, $identifier);
  }

  /**
   * Set up the Streamed Response callback.
   *
   * @param \Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   A datastore Query object.
   * @param \RootedData\RootedJsonData $result
   *   Query result.
   *
   * @return \Symfony\Component\HttpFoundation\StreamedResponse
   *   Return the StreamedResponse object.
   */
  protected function streamCsvResponse(DatastoreQuery $datastoreQuery, RootedJsonData $result) {
    $response = $this->initStreamedCsvResponse();

    $response->setCallback(function () use ($result, $datastoreQuery) {
      $count = $result->{'$.count'};
      $rows = $result->{'$.results'};
      array_unshift($rows, $this->getHeaderRow($result));

      set_time_limit(0);
      $handle = fopen('php://output', 'wb');
      $this->sendRows($handle, $rows);

      // If we've already sent the full result set we can end now.
      $progress = (count($rows) - 1);
      if ($count <= $progress) {
        fclose($handle);
        return TRUE;
      }

      // Otherwise, we iterate.
      $this->streamIterate($result, $datastoreQuery, $handle);

      fclose($handle);
    });
    return $response;
  }

  /**
   * Iterator for CSV streaming.
   *
   * This is fairly non-intuitive so some explanation is probably necessary. 
   *
   * @param \RootedData\RootedJsonData $result
   *   The result data from the initial query.
   * @param \Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   The unmodified datastore query object.
   * @param resource $handle
   *   The PHP output stream.
   */
  private function streamIterate(RootedJsonData $result, DatastoreQuery $datastoreQuery, $handle) {
    $pageCount = $progress = count($result->{'$.results'});
    $pageLimit = $this->getRowsLimit();
    $iteratorQuery = new DatastoreQuery("$datastoreQuery", $this->getRowsLimit());

    // Disable extra information in response.
    $iteratorQuery->{"$.count"} = FALSE;
    $iteratorQuery->{"$.schema"} = FALSE;
    $iteratorQuery->{"$.keys"} = FALSE;
    $iteratorQuery->{"$.offset"} = $pageCount;

    // Set up condition-based pagination.
    $conditionIndex = count($iteratorQuery->{"$.conditions"} ?? []);
    $lastIndex = $pageCount - 1;
    $lastRowId = (int) $result->{"$.results.$pageCount.record_number"};
    $rowIdColumnIndex = $this->addRowIdProperty($iteratorQuery);

    while ($pageCount >= $pageLimit) {
      $result = $this->datastoreService->runQuery($iteratorQuery);
      $rows = $result->{"$.results"};
      $pageCount = count($rows);
      $lastIndex = $pageCount - 1;
      $progress += $pageCount;
      $lastRowId = (int) $rows[$lastIndex][($rowIdColumnIndex !== NULL) ? $rowIdColumnIndex : 0];
      $this->removeRowIdProperty($rows, $rowIdColumnIndex);
      $this->sendRows($handle, $rows);
      $iteratorQuery->{"$.conditions[$conditionIndex]"} = [
        'property' => 'record_number',
        'value' => $lastRowId,
        'operator' => '>',
      ];
      $iteratorQuery->{"$.offset"} = 0;
    }
  }

  private function addRowIdProperty($iteratorQuery) {
    $properties = $iteratorQuery->{'$.properties'} ?? null;
    // if (!is_array($properties)) {
    //   exit;
    // }
    // if (is_array($properties[0])) {
    //   exit;
    // }
    if (!empty($properties) && !in_array('record_number', $properties)) {
      $properties[] = 'record_number';
      $iteratorQuery->{'$.properties'} = $properties;
      return array_search('record_number', $properties);
    }
    return NULL;
  }

  private function removeRowIdProperty(array &$rows, $rowIdColumnIndex) {
    if ($rowIdColumnIndex === NULL) {
      return;
    }
    array_walk($rows, function (&$row) use ($rowIdColumnIndex) {
      unset($row[$rowIdColumnIndex]);
    });
  }

  /**
   * Create initial streamed response object.
   *
   * @return Symfony\Component\HttpFoundation\StreamedResponse
   *   A streamed response object set up for data.csv file.
   */
  private function initStreamedCsvResponse($filename = "data.csv") {
    $response = new StreamedResponse();
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");
    $response->headers->set('X-Accel-Buffering', 'no');
    return $response;
  }

  /**
   * Loop through a group of rows and send as csv.
   *
   * @param resource $handle
   *   The file handler.
   * @param array $rows
   *   Rows of data to send as CSV.
   */
  private function sendRows($handle, array $rows) {
    foreach ($rows as $row) {
      fputcsv($handle, $row);
    }
    ob_flush();
    flush();
  }

  /**
   * Add the header row from specified properties or the schema.
   *
   * Alters the data array.
   *
   * @param \RootedData\RootedJsonData $result
   *   The result of a DatastoreQuery.
   */
  private function getHeaderRow(RootedJsonData &$result) {

    if (!empty($result->{'$.query.properties'})) {
      $header_row = $result->{'$.query.properties'};
    }
    else {
      $schema = $result->{'$.schema'};
      // Query has are no explicit properties; we should assume one table.
      $header_row = array_keys(reset($schema)['fields']);
    }

    if (empty($header_row) || !is_array($header_row)) {
      throw new \Exception("Could not generate header for CSV.");
    }
    array_walk($header_row, function (&$header) {
      if (is_array($header)) {
        $header = $header['alias'] ?? $header['property'];
      }
    });
    return $header_row;
  }

}
