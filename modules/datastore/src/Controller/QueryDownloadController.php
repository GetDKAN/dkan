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
        return $this->processStreamedCsv($datastoreQuery, $result);

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
  protected function processStreamedCsv(DatastoreQuery $datastoreQuery, RootedJsonData $result) {
    $data = $result->{"$"} ? $result->{"$"} : [];
    $response = $this->initStreamedCsvResponse();

    $response->setCallback(function () use ($data, $datastoreQuery) {
      $count = $data['count'];
      $this->addHeaderRow($data);

      set_time_limit(0);
      $handle = fopen('php://output', 'wb');
      $this->sendRows($handle, $data);

      // If we've already sent the full result set we can end now.
      $progress = (count($data['results']) - 1);
      if ($count <= $progress) {
        fclose($handle);
        return TRUE;
      }

      // Otherwise, we iterate.
      $this->streamIterate($data, $datastoreQuery, $handle);

      fclose($handle);
    });
    return $response;
  }

  /**
   * Iterator for CSV streaming.
   *
   * @param array $data
   *   The result data from the initial query.
   * @param \Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   The unmodified datastore query object.
   * @param resource $handle
   *   The PHP output stream.
   */
  private function streamIterate(array $data, DatastoreQuery $datastoreQuery, $handle) {
    $pageCount = $lastIndex = $progress = (count($data['results']) - 1);
    $iteratorQuery = clone $datastoreQuery;

    // Disable extra queries.
    $iteratorQuery->{"$.count"} = FALSE;
    $iteratorQuery->{"$.schema"} = FALSE;

    // For this first pass, remember we have to account for header row.
    $conditionIndex = count($iteratorQuery->{"$.conditions"} ?? []);
    $pageLimit = $this->getRowsLimit();
    $lastRowId = (int) $data['results'][$lastIndex]['record_number'];

    while ($pageCount >= $pageLimit) {
      $iteratorQuery->{"$.conditions[$conditionIndex]"} = [
        'property' => 'record_number',
        'value' => $lastRowId,
        'operator' => '>',
      ];
      $result = $this->datastoreService->runQuery($iteratorQuery);
      $data = $result->{"$"};
      $this->sendRows($handle, $data);
      $pageCount = count($data['results']);
      $progress += $pageCount;

      $lastIndex = $pageCount - 1;
      $lastRowId = (int) $result->{"$.results[$lastIndex].record_number"};
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
   * Loop through rows and send csv.
   *
   * @param resource $handle
   *   The file handler.
   * @param array $data
   *   Data to send.
   */
  private function sendRows($handle, array $data) {
    foreach ($data['results'] as $row) {
      fputcsv($handle, $row);
    }
    ob_flush();
    flush();
  }

  /**
   * Add the header row, from specified properties, if any, or the schema.
   */
  private function addHeaderRow(array &$data) {

    if (!empty($data['query']['properties'])) {
      $header_row = $data['query']['properties'];
    }
    else {
      $header_row = array_keys(reset($data['schema'])['fields']);
    }

    if (is_array($header_row)) {
      array_unshift($data['results'], $header_row);
    }
  }

}
