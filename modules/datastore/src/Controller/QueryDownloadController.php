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
class QueryDownloadController extends QueryController {

  /**
   * Stream a CSV response.
   *
   * @param Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   A datastore query object.
   * @param RootedData\RootedJsonData $result
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
   * @param RootedData\RootedJsonData $result
   *   Query result.
   *
   * @return \Symfony\Component\HttpFoundation\StreamedResponse
   *   Return the StreamedResponse object.
   */
  protected function processStreamedCsv(DatastoreQuery $datastoreQuery, RootedJsonData $result) {
    $data = $result->{"$"} ? $result->{"$"} : [];

    // Disable extra queries.
    $datastoreQuery->{"$.count"} = FALSE;
    $datastoreQuery->{"$.schema"} = FALSE;

    $this->addHeaderRow($data);
    $response = $this->initStreamedCsvResponse();
    $response->setCallback(function () use (&$data, $datastoreQuery) {
      $conditionIndex = count($datastoreQuery->{"$.conditions"} ?? []);
      $pageLimit = $datastoreQuery->{"$.limit"} = $this->getRowsLimit();
      $queryLimit = $datastoreQuery->{"$.limit"};
      
      set_time_limit(0);
      $handle = fopen('php://output', 'wb');
      $this->sendRows($handle, $data);
      
      // For this first pass, remember we have to account for header row.
      $pageCount = $count = (count($data['results']) - 1);
      $lastIndex = $count;
      $lastRowId = (int) $data['results'][$lastIndex]['record_number'];
      
      if ($pageCount > $pageLimit || $count >= $queryLimit) {
        fclose($handle);
        return TRUE;
      }

      // Count can be greater as we add a header row to the first time.
      while ($pageCount >= $pageLimit && $count < $queryLimit) {
        $datastoreQuery->{"$.conditions[$conditionIndex]"} = ['property' => 'record_number', 'value' => $lastRowId, 'operator' => '>'];
        $result = $this->datastoreService->runQuery($datastoreQuery);
        $data = $result->{"$"};
        $this->sendRows($handle, $data);
        $pageCount = count($data['results']);
        $count += $pageCount;

        $lastIndex = $pageCount - 1;
        $lastRowId = (int) $result->{"$.results[$lastIndex].record_number"};
      }
      fclose($handle);
    });
    return $response;
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
