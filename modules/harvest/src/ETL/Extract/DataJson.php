<?php

namespace Drupal\harvest\ETL\Extract;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Drupal\harvest\Util;

class DataJson extends Extract
{
    /**
     * Harvest Plan, decoded JSON object.
     *
     * @var object
     */
    protected $harvest_plan;

    /**
     * Inject the guzzle client.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    public function __construct(object $harvest_plan, ?ClientInterface $client = null)
    {
        $this->client = $client ?? new Client();
        $this->harvest_plan = $harvest_plan;
    }

    /**
     * Get the items to be harvested.
     *
     * @return array
     *   The items to be harvested.
     */
    public function getItems(): array
    {
        $file_location = $this->harvest_plan->extract->uri;
        if (substr_count($file_location, "file://") > 0) {
            $json = file_get_contents($file_location);
        } else {
            $json = $this->httpRequest($file_location);
        }

        $data = json_decode($json);

        if ($data === null) {
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
    private function httpRequest(string $uri): string
    {
        try {
            $res = $this->client->request('GET', $uri);
            return (string) $res->getBody();
        } catch (\Exception $exception) {
            throw new \Exception("Error reading {$uri}");
        }
    }
}
