<?php

namespace Drupal\datastore\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\common\DatasetInfo;
use Drupal\datastore\Service\DatastoreQuery;
use Drupal\datastore\Service\Query as QueryService;
use Drupal\metastore\MetastoreApiResponse;
use RootedData\RootedJsonData;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller providing functionality used to stream datastore queries.
 *
 * This generally supports CSV download of filtered datasets.
 */
class QueryDownloadController extends AbstractQueryController {

  /**
   * {@inheritDoc}
   */
  public function __construct(QueryService $queryService, DatasetInfo $datasetInfo, MetastoreApiResponse $metastoreApiResponse, ConfigFactoryInterface $configFactory) {
    parent::__construct($queryService, $datasetInfo, $metastoreApiResponse, $configFactory);
    // We do not want to cache streaming CSV content internally in Drupal,
    // because datasets can be very large. However, we do want CDNs to be able
    // to cache the CSV stream for a reasonable amount of time.
    $config = $configFactory->get('datastore.settings');
    $this->cacheMaxAge = $config->get('response_stream_max_age');
  }

  /**
   * {@inheritdoc}
   */
  public function formatResponse(
    DatastoreQuery $datastoreQuery,
    RootedJsonData $result,
    array $dependencies = [],
    ?ParameterBag $params = NULL
  ) {
    return match ($datastoreQuery->{"$.format"}) {
      'csv' => $this->streamCsvResponse($datastoreQuery, $result),
      default => $this->getResponseFromException(
        new \UnexpectedValueException('Streaming not currently available for JSON responses'),
        400
      ),
    };
  }

  /**
   * {@inheritdoc}
   */
  protected function buildDatastoreQuery($request, $identifier = NULL) {
    $json = static::getPayloadJson($request);
    $data = json_decode($json);
    $this->additionalPayloadValidation($data);
    if ($identifier) {
      $resource = (object) ["id" => $identifier, "alias" => "t"];
      $data->resources = [$resource];
    }
    $data->results = FALSE;
    return new DatastoreQuery(json_encode($data));
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
      // Open the stream and send the header.
      set_time_limit(0);
      $handle = fopen('php://output', 'wb');

      // Wrap in try/catch so that we can still close the output buffer.
      try {
        // Send the header row.
        $this->sendRow($handle, $this->getHeaderRow($datastoreQuery, $result));

        // Get the result pointer and send each row to the stream one by one.
        $result = $this->queryService->runResultsQuery($datastoreQuery, FALSE, TRUE);
        while ($row = $result->fetchAssoc()) {
          $this->sendRow($handle, array_values($row));
        }
      }
      catch (\Exception $e) {
        $this->sendRow($handle, [$e->getMessage()]);
      }

      fclose($handle);
    });
    return $response;
  }

  /**
   * Create initial streamed response object.
   *
   * @return \Symfony\Component\HttpFoundation\StreamedResponse
   *   A streamed response object set up for data.csv file.
   */
  private function initStreamedCsvResponse($filename = "data.csv") {
    $response = new StreamedResponse();
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");
    $response->headers->set('X-Accel-Buffering', 'no');
    // Ensure one hour max-age plus public status.
    return $this->addCacheHeaders($response);
  }

  /**
   * Loop through a group of rows and send as csv.
   *
   * @param resource $handle
   *   The file handler.
   * @param array $row
   *   Row of data to send as CSV.
   */
  private function sendRow($handle, array $row) {
    fputcsv($handle, $row, escape: "\\");
    ob_flush();
    flush();
  }

}
