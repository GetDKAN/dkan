<?php

class dataJsonTest extends PHPUnit_Framework_TestCase {
  public function testDataJson()
  {
    $json = file_get_contents('json');
    $json = json_decode($json, TRUE);
    $this->assertArrayHasKey('landingPage', $json['dataset'][0]);
  }
}

