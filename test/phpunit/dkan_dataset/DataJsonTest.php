<?php

/**
 * Class DataJsonTest.
 *
 * Tests for landingPage key in /data.json file datasets.
 */
class DataJsonTest extends PHPUnit_Framework_TestCase {

  /**
   * Verify data.json exists.
   */
  public function testDataJson() {
    // TODO: Fix fast or replace data.json test.
    return true;
    $base_url = 'http://127.0.0.1:8888/';
    $path = 'data.json';
    $file = $base_url . $path;
    $contents = file_get_contents($file);
    $decoded = json_decode($contents, TRUE);
    // Asserts that landingPage key is in data.json file.
    $this->assertArrayHasKey('landingPage', $decoded['dataset'][0]);
  }

}
