<?php

namespace Drupal\datastore\Controller;

use Drupal\datastore\Service\DatastoreQuery;
use Drupal\datastore\Util\QueryIterator;
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
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  protected function buildDatastoreQuery($request, $identifier = NULL) {
    $data = json_decode(static::getPayloadJson($request));
    if (isset($data->limit)) {
      throw new \Exception("Limits are, temporarily, not allowed in downloads.");
    }
    if (isset($data->sorts) && count($data->sorts) > 1) {
      throw new \Exception("Downloads are currently limited to sorting on a single column.");
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
      $rows = $result->{'$.results'};

      // Open the stream and send the header.
      set_time_limit(0);
      $handle = fopen('php://output', 'wb');
      $this->sendRows($handle, $this->getHeaderRow($result));

      // If we've already sent the full result set we can end now.
      if ($result->{'$.count'} <= count($rows)) {
        $this->sendRows($handle, $rows);
        fclose($handle);
        return TRUE;
      }

      // Otherwise, we're going to redo as an iterator from the beginning.
      $iterator = new QueryIterator($datastoreQuery, $this->getRowsLimit(), $this->datastoreService);
      while ($rows = $iterator->pageResult()) {
        $this->sendRows($handle, $rows);
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
    return [$header_row];
  }

}
