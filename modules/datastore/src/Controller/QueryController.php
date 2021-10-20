<?php

namespace Drupal\datastore\Controller;

use Drupal\datastore\Service\DatastoreQuery;
use RootedData\RootedJsonData;
use Ilbee\CSVResponse\CSVResponse as CsvResponse;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Controller providing functionality used to perform paged datastore queries.
 *
 * @package Drupal\datastore
 */
class QueryController extends AbstractQueryController {

  /**
   * Return correct JSON or CSV response.
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
        $response = new CsvResponse($result->{"$.results"}, 'data', ',');
        return $this->addCacheHeaders($response);

      case 'json':
      default:
        return $this->metastoreApiResponse->cachedJsonResponse($result->{"$"}, 200, $dependencies, $params);
    }
  }

  /**
   * Retrieve the datastore query schema. Used by datastore.1.query.schema.get.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   *
   * @todo Incorporate config cache tags into the API response service.
   */
  public function querySchema() {
    $schema = (new DatastoreQuery("{}", $this->getRowsLimit()))->getSchema();
    return $this->metastoreApiResponse->cachedJsonResponse(json_decode($schema), 200);
  }

}
