<?php

/**
 * Class dataJsonTest
 *
 * Tests for landingPage key in /data.json file datasets.
 */
class dataJsonTest extends PHPUnit_Framework_TestCase {
  public function testDataJson()
  {
    $base_url = 'http://127.0.0.1:8888/';
    $path = 'data.json';
    $file = $base_url . $path;
    $contents = file_get_contetns($file);
    $decoded = json_decode($contents, TRUE);
    // Asserts that landingPage key is in data.json file.
    $this->assertArrayHasKey('landingPage', $decoded['dataset'][0]);
  }
}
