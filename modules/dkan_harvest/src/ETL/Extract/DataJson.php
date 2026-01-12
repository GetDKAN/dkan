<?php

namespace Drupal\harvest\ETL\Extract;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Drupal\harvest\Util;

/**
 * Extract class for json data.
 */
class DataJson extends Extract {
  /**
   * Harvest Plan, decoded JSON object.
   *
   * @var object
   */
  protected $harvestPlan;

  /**
   * Inject the guzzle client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * DataJson constructor.
   *
   * @param object $harvest_plan
   *   The harvest plan.
   * @param \GuzzleHttp\ClientInterface|null $client
   *   Optional http client.
   */
  public function __construct(object $harvest_plan, ?ClientInterface $client = NULL) {
    $this->client = $client ?? new Client();
    $this->harvestPlan = $harvest_plan;
  }

  /**
   * {@inheritdoc}
   */
  public function getItems(): array {
    $file_location = $this->harvestPlan->extract->uri;
    if (substr_count($file_location, "file://") > 0) {
      $json = file_get_contents($file_location);
    }
    else {
      $json = $this->httpRequest($file_location);
    }

    $data = json_decode($json);

    if ($data === NULL) {
      throw new \Exception("Error decoding JSON.");
    }

    if (!isset($data->dataset)) {
      throw new \Exception("data.json does not have a dataste property");
    }

    $datasets = [];
    foreach ($data->dataset as $dataset) {
      $datasets[Util::getDatasetId($dataset)] = $dataset;
    }
    return $datasets;
  }

  /**
   * Make the HTTP request to get harvest data.
   *
   * @param string $uri
   *   URI for request.
   *
   * @return string
   *   The response body.
   */
  private function httpRequest(string $uri): string {
    try {
      $res = $this->client->request('GET', $uri);
      return (string) $res->getBody();
    }
    catch (\Exception $exception) {
      throw new \Exception("Error reading {$uri}");
    }
  }

}
