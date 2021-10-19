<?php

namespace Drupal\datastore\Controller;

use Drupal\datastore\Service\DatastoreQuery;
use Symfony\Component\HttpFoundation\StreamedResponse;
use RootedData\RootedJsonData;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Datastore CSV download methods.
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
    $iteratorQuery = clone $datastoreQuery;

    // Disable extra information in response.
    $iteratorQuery->{"$.count"} = FALSE;
    $iteratorQuery->{"$.schema"} = FALSE;
    $iteratorQuery->{"$.keys"} = FALSE;

    $i = 1;
    while ($pageCount >= $pageLimit) {
      $iteratorQuery->{"$.offset"} = $pageLimit * $i;
      $result = $this->datastoreService->runQuery($iteratorQuery);
      $rows = $result->{"$.results"};
      $this->sendRows($handle, $rows);
      $pageCount = count($rows);
      $progress += $pageCount;
      $i++;
    }
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
      $tableSchema = reset($result->{'$.schema'});
      $header_row = array_keys($tableSchema['fields']);
    }

    if (empty($header_row) || !is_array($header_row)) {
      throw new \Exception("Could not generate header for CSV.");
    }
    return $header_row;
  }

}
