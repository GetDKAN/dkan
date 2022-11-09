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
    //Getting the resource id to be used for getting the data dictionary (DD).
    $resource_id = $result->{"$.query.resources.0.id"};

    $response->setCallback(function () use ($result, $datastoreQuery,$resource_id) {
      // Open the stream and send the header.
      set_time_limit(0);
      $handle = fopen('php://output', 'wb');

      // Wrap in try/catch so that we can still close the output buffer.
      try {
        // Send the header row.
        $this->sendRow($handle, $this->getHeaderRow($result));

        // Get the result pointer and send each row to the stream one by one.
        $result = $this->queryService->runResultsQuery($datastoreQuery, FALSE);

        while ($row = $result->fetchAssoc()) {
          //Get the DD definition to get the original date format.
          $data_dictionary_fields = $this->returnDataDictionaryFields($resource_id);
          //Create a new array to place the updated values.
          $formated_data = [];
          foreach ($row as $key => $value) {
            //Get the field definition from the DD.
            $field_definition = $this->returnFieldDefinition($data_dictionary_fields, $key);
            //Do something if the field is a date field and isn't empty.
            if ($field_definition['type'] == 'date' && !empty($value)) {
              //Format the date.
              $newDate = str_replace('%', '', date($field_definition['format'], strtotime($value)));
              //Return the new date in the array.
              $formated_data[] = strval($newDate);
            }else{
              //It's not a date so return the original value.
              $formated_data[] = $value;
            }
          }
          //Send the updated array to the csv file.
          $this->sendRow($handle, $formated_data);
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
   * @return array
   *
   */
  private function returnDataDictionaryFields($resource_id) {
    //Get data dictionary info.
    $dict_id =  \Drupal::service('dkan.metastore.data_dictionary_discovery')->dictionaryIdFromResource($resource_id);
    $metaData = \Drupal::service('dkan.metastore.service')->get('data-dictionary', $dict_id)->{"$.data.fields"};
    return $metaData;
  }

  /**
   * Create initial streamed response object.
   *
   * @return array
   *
   */
  private function returnFieldDefinition($dataDictionaryFields, $field) {
    //Get data dictionary info.
    foreach ($dataDictionaryFields as $definition) {
      if ($field == $definition['name']) {
        return $definition;
      }
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
   * @param array $row
   *   Row of data to send as CSV.
   */
  private function sendRow($handle, array $row) {
    fputcsv($handle, $row);
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

    try {
      if (!empty($result->{'$.query.properties'})) {
        $header_row = $result->{'$.query.properties'};
      }
      else {
        $schema = $result->{'$.schema'};
        // Query has are no explicit properties; we should assume one table.
        $header_row = array_keys(reset($schema)['fields']);
      }
      if (empty($header_row) || !is_array($header_row)) {
        throw new \DomainException("Could not generate header for CSV.");
      }
    }
    catch (\Exception $e) {
      throw new \DomainException("Could not generate header for CSV.");
    }

    array_walk($header_row, function (&$header) {
      if (is_array($header)) {
        $header = $header['alias'] ?? $header['property'];
      }
    });
    return $header_row;
  }

}
