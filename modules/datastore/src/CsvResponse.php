<?php

namespace Drupal\datastore;

use Symfony\Component\HttpFoundation\Response;

/**
 * Send a CSV file using a Symfony Response.
 *
 * @package Drupal\datastore
 */
class CsvResponse extends Response {

  /**
   * Data to put in file.
   *
   * @var array
   */
  protected $data;

  /**
   * Filename.
   *
   * @var string
   */
  protected $filename = 'export.csv';

  /**
   * CsvResponse constructor.
   *
   * @param array $data
   *   Data.
   * @param int $status
   *   Status Code.
   * @param array $headers
   *   Optional headers to send.
   */
  public function __construct(array $data = [], $status = 200, array $headers = []) {
    parent::__construct('', $status, $headers);

    $this->setData($data);
  }

  /**
   * Set the data into the file.
   *
   * @param array $data
   *   Data.
   *
   * @return CsvResponse
   *   return for chaining.
   */
  public function setData(array $data) {
    $output = fopen('php://temp', 'r+');

    foreach ($data as $row) {
      fputcsv($output, $row);
    }

    rewind($output);
    $this->data = '';
    while ($line = fgets($output)) {
      $this->data .= $line;
    }

    $this->data .= fgets($output);

    return $this->update();
  }

  /**
   * Get the file name.
   *
   * @return string
   *   Filename.
   */
  public function getFilename() {
    return $this->filename;
  }

  /**
   * Set the file name.
   *
   * @param string $filename
   *   The file name.
   *
   * @return CsvResponse
   *   Return something.
   */
  public function setFilename(string $filename) {
    $this->filename = $filename;
    return $this->update();
  }

  /**
   * Update headers and content.
   *
   * @return CsvResponse
   *   Return a csv file.
   */
  protected function update() {
    $this->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $this->filename));

    if (!$this->headers->has('Content-Type')) {
      $this->headers->set('Content-Type', 'text/csv');
    }

    return $this->setContent($this->data);
  }

}
