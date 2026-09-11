<?php

namespace Drupal\dkan_datastore\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\dkan_common\DatasetInfo;
use Drupal\dkan_datastore\Service\DatastoreQuery;
use Drupal\dkan_datastore\Service\Query as QueryService;
use Drupal\dkan_metastore\MetastoreApiResponse;
use League\Csv\Writer;
use RootedData\RootedJsonData;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * Controller providing functionality used to stream datastore queries.
 *
 * This generally supports CSV download of filtered datasets.
 */
class QueryDownloadController extends AbstractQueryController {

  /**
   * {@inheritDoc}
   */
  public function __construct(
    QueryService $queryService,
    DatasetInfo $datasetInfo,
    MetastoreApiResponse $metastoreApiResponse,
    ConfigFactoryInterface $configFactory,
    StateInterface $state,
  ) {
    parent::__construct($queryService, $datasetInfo, $metastoreApiResponse, $configFactory, $state);
    // We do not want to cache streaming CSV content internally in Drupal,
    // because datasets can be very large. However, we do want CDNs to be able
    // to cache the CSV stream for a reasonable amount of time.
    $config = $configFactory->get('dkan_datastore.settings');
    $this->cacheMaxAge = $config->get('response_stream_max_age');
  }

  /**
   * {@inheritdoc}
   */
  public function formatResponse(
    DatastoreQuery $datastoreQuery,
    RootedJsonData $result,
    array $dependencies = [],
    ?ParameterBag $params = NULL,
  ) {
    return match ($datastoreQuery->{"$.format"}) {
      'csv' => $this->streamCsvResponse($datastoreQuery, $result),
      'json' => $this->streamJsonResponse($datastoreQuery, $result),
      default => $this->getResponseFromException(
        new \UnexpectedValueException('Streaming not currently available for ' . $datastoreQuery->{"$.format"} . 'responses'),
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
    $this->assertDegradedModeAllowed($data);
    $this->additionalPayloadValidation($data);
    if ($identifier) {
      $resource = (object) ['id' => $identifier, 'alias' => 't'];
      $data->resources = [$resource];
    }
    $data->results = FALSE;
    return new DatastoreQuery(json_encode($data));
  }

  /**
   * {@inheritdoc}
   */
  protected function assertDegradedModeAllowed(object $data): void {
    if ($this->state->get('dkan_datastore.degraded_performance', FALSE)) {
      throw new ServiceUnavailableHttpException(
        static::DEGRADE_MODE_RETRY_AFTER,
        'Datastore downloads are temporarily limited due to high server load. All streaming responses are currently unavailable.'
      );
    }
  }

  /**
   * Set up the Streamed Response callback.
   *
   * @param \Drupal\dkan_datastore\Service\DatastoreQuery $datastoreQuery
   *   A datastore Query object.
   * @param \RootedData\RootedJsonData $result
   *   Query result.
   *
   * @return \Symfony\Component\HttpFoundation\StreamedResponse
   *   Return the StreamedResponse object.
   */
  protected function streamCsvResponse(DatastoreQuery $datastoreQuery, RootedJsonData $result) {
    $response = $this->initStreamedCsvResponse();

    $response->setCallback(
      function () use ($result, $datastoreQuery) {
        // Maximum execution time, since these downloads can be very large.
        set_time_limit(0);

        $csv = Writer::from('php://output', 'wb')
          // @see https://csv.thephpleague.com/9.0/interoperability/rfc4180-field/
          ->setEscape('');

        try {
          // Send the header row.
          $csv->insertOne($this->getHeaderRow($datastoreQuery, $result));

          // Get the result pointer and send each row to the stream one by one.
          $result = $this->queryService->runResultsQuery($datastoreQuery, FALSE, TRUE);
          while ($row = $result->fetchAssoc()) {
            $csv->insertOne($row);
          }
        }
        catch (\Exception $e) {
          // @todo Sanitize this message.
          $csv->insertOne([$e->getMessage()]);
        }
      }
    );
    return $response;
  }

  /**
   * Create initial streamed response object.
   *
   * @param string $filename
   *   (optional) File name that will be the attached file name. Defaults to
   *   data.csv.
   *
   * @return \Symfony\Component\HttpFoundation\StreamedResponse
   *   A streamed response object set up to download the file.
   */
  private function initStreamedCsvResponse($filename = "data.csv"): StreamedResponse {
    $response = new StreamedResponse();
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set(
      'Content-Disposition',
      $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        $filename
      )
    );
    // Turn off ngnix buffering.
    $response->headers->set('X-Accel-Buffering', 'no');
    // Ensure one hour max-age plus public status.
    return $this->addCacheHeaders($response);
  }

  /**
   * Set up the Streamed JSON Response.
   *
   * @param \Drupal\dkan_datastore\Service\DatastoreQuery $datastoreQuery
   *   A datastore Query object.
   * @param \RootedData\RootedJsonData $result
   *   Query result.
   *
   * @return \Symfony\Component\HttpFoundation\StreamedJsonResponse
   *   Return the StreamedResponse object.
   */
  protected function streamJsonResponse(DatastoreQuery $datastoreQuery, RootedJsonData $result) {
    $data = ['results' => $this->loadJson($datastoreQuery)];
    $metadata_names = ['count', 'schema', 'query'];
    foreach ($metadata_names as $metadata_name) {
      $data[$metadata_name] = $result->get('$.' . $metadata_name);
    }
    $data = array_filter($data);

    $response = new StreamedJsonResponse($data);
    $response->headers->set('Content-Type', 'application/json');
    $response->headers->set('Content-Disposition', "attachment; filename=\"data.json\"");
    $response->headers->set('X-Accel-Buffering', 'no');
    // Ensure one hour max-age plus public status.
    return $this->addCacheHeaders($response);
  }

  /**
   * Set up the Stream query result as json objects.
   *
   * @param \Drupal\dkan_datastore\Service\DatastoreQuery $datastoreQuery
   *   A datastore Query object.
   */
  protected function loadJson(DatastoreQuery $datastoreQuery) {
    $count = 0;

    try {
      // Get the result pointer and send each row to the stream one by one.
      $result = $this->queryService->runResultsQuery($datastoreQuery, FALSE, TRUE);
      while ($row = $result->fetchAssoc()) {
        if ($datastoreQuery->{"$.keys"} === FALSE) {
          $row = $this->queryService->stripRowKeys($row);
        }
        yield $row;

        if (0 === ++$count % 100) {
          flush();
        }
      }
    }
    catch (\Exception $e) {
      yield json_encode(['error' => $e->getMessage()]);
    }
  }

}
