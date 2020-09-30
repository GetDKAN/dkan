<?php

namespace Drupal\datastore;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class CsvResponse.
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
class CsvResponse extends Response {

  private $data;
  private $keys;
  private $filename;

  public function __construct($filename, $data = array(), $keys = array(), $status = 200, $headers = array())
  {
    parent::__construct('', $status, $headers);
    $this->filename = $filename . '_' . md5(uniqid(rand(), TRUE)) . '.csv';
    $this->build($keys, $data);
    $this->serve();
  }

  private function build(array $keys, array $data)
  {
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
  }

  private function serve()
  {
    $this->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $this->filename));
    if (!$this->headers->has('Content-Type')) {
        $this->headers->set('Content-Type', 'text/csv');
    }

    return $this->setContent($this->data);
  }

}
