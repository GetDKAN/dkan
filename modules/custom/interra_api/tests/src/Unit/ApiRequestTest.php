<?php

use Drupal\interra_api\ApiRequest;
use \PHPUnit\Framework\TestCase;

class ApiRequestTest extends TestCase {
  
  public $apiRequest;
  
  function __construct() {
    parent::__construct();
    $this->apiRequest = new ApiRequest();
  }
  
  public function testInstantiateClass() {
    $this->assertNotNull($this->apiRequest);
  }
  
  public function testGetUri() {
    $this->assertEquals($this->apiRequest->getURI('/api/v1/collections/dataset'), 'collections/dataset');
    $this->assertEquals($this->apiRequest->getURI('/api/v1/wtf'), 'wtf');
    $this->assertEquals($this->apiRequest->getURI('/api/collections/dataset'), '');
  }
  
}

