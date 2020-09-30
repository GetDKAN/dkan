<?php

namespace Drupal\datastore;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class CsvExport.
 *
 * @package Drupal\datastore
 *
 * @param array $data
 *   Results from a datastore query.
 * @param array $keys
 * Array of table column headers.
 * @param string $filename
 *   Filename for the exported csv.
 */
class CsvExport extends Response {

  private $data;
  private $keys;
  private $filename;

  public function __construct($filename, $data = array(), $status = 200, $headers = array())
  {
    parent::__construct('', $status, $headers);
    $this->filename = $filename . '_' . md5(uniqid(rand(), TRUE)) . '.csv';
    $this->build($data);
    $this->serve();
  }

  private function build(array $keys, array $data) {
    // Use PHP's built in file handler functions to create a temporary file.
    $handle = fopen('php://temp', 'w+');

    // Set up the header as the first line of the CSV file.
    // Blank strings are used for multi-cell values where there is a count of
    // the "keys" and a list of the keys with the count of their usage.
    $header = [];
    foreach ($keys as $key) {
      $header[] = $key;
    }
    // Add the header as the first line of the CSV.
    fputcsv($handle, $header);

    // Iterate through the data.
    foreach ($data as $row) {
      fputcsv($handle, $row);
    }
    // Reset where we are in the CSV.
    rewind($handle);

    // Retrieve the data from the file handler.
    $csv_data = stream_get_contents($handle);

    // Close the file handler since we don't need it anymore.
    // We are not storing this file anywhere in the filesystem.
    fclose($handle);

    // Once the data is built, we can return it as a response.
    $response = new Response();

    // By setting these 2 header options, the browser will see the URL
    // used by this Controller to return a CSV file called "export.csv".
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

    // This line physically adds the CSV data we created.
    $response->setContent($csv_data);

    return $response;
  }

}
